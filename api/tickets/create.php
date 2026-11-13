<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

// Handle CORS
ApiResponse::cors();

RequestHelper::requireMethod('POST');
RequestHelper::checkRateLimit('create_ticket', 10);

try {
    // Require authentication
    $user = RequestHelper::requireAuth();
    
    $data = RequestHelper::getJsonBody();
    
    // Validate input
    $subject = trim($data['subject'] ?? '');
    $message = trim($data['message'] ?? '');
    $priority = (int)($data['priority'] ?? 3); // Default to low priority
    $category = (int)($data['category'] ?? 1); // Default category
    $name = trim($data['name'] ?? $user['name']);
    $email = trim($data['email'] ?? $user['email']);
    $customFields = $data['customFields'] ?? [];
    
    if (empty($subject)) {
        ApiResponse::error('Subject is required', 400);
    }
    
    if (empty($message)) {
        ApiResponse::error('Message is required', 400);
    }
    
    if (strlen($subject) > 255) {
        ApiResponse::error('Subject is too long (max 255 characters)', 400);
    }
    
    if (strlen($message) > 10000) {
        ApiResponse::error('Message is too long (max 10000 characters)', 400);
    }
    
    // Validate priority
    if (!in_array($priority, [0, 1, 2, 3])) {
        $priority = 3; // Default to low
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Validate category exists
    $stmt = $db->prepare("SELECT id FROM hesk_categories WHERE id = ? LIMIT 1");
    $stmt->execute([$category]);
    if (!$stmt->fetch()) {
        // Use default category if specified doesn't exist
        $stmt = $db->prepare("SELECT id FROM hesk_categories ORDER BY cat_order ASC LIMIT 1");
        $stmt->execute();
        $defaultCategory = $stmt->fetch();
        $category = $defaultCategory ? $defaultCategory['id'] : 1;
    }
    
    // Generate tracking ID
    $trackid = generateTrackingId();
    
    // Ensure tracking ID is unique
    $attempts = 0;
    while ($attempts < 10) {
        $stmt = $db->prepare("SELECT id FROM hesk_tickets WHERE trackid = ? LIMIT 1");
        $stmt->execute([$trackid]);
        if (!$stmt->fetch()) {
            break;
        }
        $trackid = generateTrackingId();
        $attempts++;
    }
    
    if ($attempts >= 10) {
        ApiResponse::serverError('Unable to generate unique tracking ID');
    }
    
    // Get client IP
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    if (strpos($ip, ',') !== false) {
        $ip = trim(explode(',', $ip)[0]);
    }
    
    // Create ticket
    $stmt = $db->prepare("
        INSERT INTO hesk_tickets (
            trackid, name, email, category, priority, subject, message, 
            dt, lastchange, ip, language, status, owner, time_worked, 
            due_date, archive, locked, articles, replies, staffreplies
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, 
            NOW(), NOW(), ?, 'English', 0, 0, '00:00:00', 
            '0000-00-00 00:00:00', 0, 0, '', 0, 0
        )
    ");
    
    $stmt->execute([
        $trackid,
        $name,
        $email,
        $category,
        $priority,
        $subject,
        $message,
        $ip
    ]);
    
    $ticketId = $db->lastInsertId();
    
    // Save custom fields if provided
    if (!empty($customFields)) {
        foreach ($customFields as $fieldKey => $fieldValue) {
            // Extract field ID from key (e.g., "custom1" -> 1)
            if (preg_match('/^custom(\d+)$/', $fieldKey, $matches)) {
                $fieldId = (int)$matches[1];
                
                // Convert array values to comma-separated string for checkbox fields
                if (is_array($fieldValue)) {
                    $fieldValue = implode(',', $fieldValue);
                }
                
                // Insert custom field value
                $stmt = $db->prepare("
                    INSERT INTO hesk_tickets (id, custom{$fieldId}) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE custom{$fieldId} = VALUES(custom{$fieldId})
                ");
                
                // For safety, only allow custom field IDs 1-50 (HESK standard)
                if ($fieldId >= 1 && $fieldId <= 50) {
                    try {
                        // Update the ticket with custom field value
                        $updateStmt = $db->prepare("UPDATE hesk_tickets SET custom{$fieldId} = ? WHERE id = ?");
                        $updateStmt->execute([$fieldValue, $ticketId]);
                    } catch (PDOException $e) {
                        // Log error but don't fail the ticket creation
                        Logger::warning('Failed to save custom field', [
                            'ticket_id' => $ticketId,
                            'field_id' => $fieldId,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }
    }
    
    // Get the created ticket
    $stmt = $db->prepare("
        SELECT id, trackid, name, email, subject, message, status, priority, 
               category, dt, lastchange
        FROM hesk_tickets 
        WHERE id = ?
    ");
    $stmt->execute([$ticketId]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($ticket) {
        $ticket['id'] = (int)$ticket['id'];
        $ticket['status'] = (int)$ticket['status'];
        $ticket['priority'] = (int)$ticket['priority'];
        $ticket['category'] = (int)$ticket['category'];
        $ticket['status_text'] = getStatusText($ticket['status']);
        $ticket['priority_text'] = getPriorityText($ticket['priority']);
        $ticket['created_at'] = $ticket['dt'];
        $ticket['updated_at'] = $ticket['lastchange'];
    }
    
    Logger::info('Ticket created', [
        'ticket_id' => $ticketId,
        'trackid' => $trackid,
        'user_id' => $user['sub'],
        'email' => $email
    ]);
    
    ApiResponse::success([
        'ticket' => $ticket,
        'tracking_id' => $trackid
    ], 'Ticket created successfully');
    
} catch (PDOException $e) {
    Logger::error('Database error creating ticket', ['error' => $e->getMessage()]);
    ApiResponse::serverError('Database error occurred');
} catch (Exception $e) {
    Logger::error('Unexpected error creating ticket', ['error' => $e->getMessage()]);
    ApiResponse::serverError('An unexpected error occurred');
}

function generateTrackingId() {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $trackid = '';
    for ($i = 0; $i < 10; $i++) {
        $trackid .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $trackid;
}

function getStatusText($status) {
    $statusMap = [
        0 => 'New',
        1 => 'Waiting Reply',
        2 => 'Replied',
        3 => 'Resolved',
        4 => 'In Progress',
        5 => 'On Hold'
    ];
    return $statusMap[$status] ?? 'Unknown';
}

function getPriorityText($priority) {
    $priorityMap = [
        0 => 'Critical',
        1 => 'High',
        2 => 'Medium',
        3 => 'Low'
    ];
    return $priorityMap[$priority] ?? 'Unknown';
}
?>