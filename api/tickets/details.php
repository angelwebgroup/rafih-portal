<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

// Handle CORS
ApiResponse::cors();

RequestHelper::requireMethod('GET');

try {
    // Require authentication
    $user = RequestHelper::requireAuth();
    
    $ticketId = $_GET['id'] ?? '';
    $trackingId = $_GET['trackid'] ?? '';
    
    if (empty($ticketId) && empty($trackingId)) {
        ApiResponse::error('Ticket ID or tracking ID is required', 400);
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Build query based on provided identifier
    if (!empty($ticketId)) {
        $whereClause = "id = ?";
        $param = (int)$ticketId;
    } else {
        $whereClause = "trackid = ?";
        $param = $trackingId;
    }
    
    // Get ticket details
    $ticketQuery = "
        SELECT 
            id, trackid, name, email, subject, message, status, priority, 
            category, dt, lastchange, articles, ip, language, owner, 
            time_worked, due_date, archive, locked, replies, staffreplies
        FROM hesk_tickets 
        WHERE $whereClause
        LIMIT 1
    ";
    
    $stmt = $db->prepare($ticketQuery);
    $stmt->execute([$param]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ticket) {
        ApiResponse::notFound('Ticket not found');
    }
    
    // Check access permissions
    if (!$user['isadmin'] && $ticket['email'] !== $user['email']) {
        ApiResponse::forbidden('You do not have permission to view this ticket');
    }
    
    // Format ticket data
    $ticket['id'] = (int)$ticket['id'];
    $ticket['status'] = (int)$ticket['status'];
    $ticket['priority'] = (int)$ticket['priority'];
    $ticket['category'] = (int)$ticket['category'];
    $ticket['replies'] = (int)$ticket['replies'];
    $ticket['staffreplies'] = (int)$ticket['staffreplies'];
    $ticket['locked'] = (bool)$ticket['locked'];
    $ticket['archive'] = (bool)$ticket['archive'];
    $ticket['status_text'] = getStatusText($ticket['status']);
    $ticket['priority_text'] = getPriorityText($ticket['priority']);
    $ticket['created_at'] = $ticket['dt'];
    $ticket['updated_at'] = $ticket['lastchange'];
    
    // Get category name
    $stmt = $db->prepare("SELECT name FROM hesk_categories WHERE id = ? LIMIT 1");
    $stmt->execute([$ticket['category']]);
    $categoryName = $stmt->fetchColumn();
    $ticket['category_name'] = $categoryName ?: 'Unknown';
    
    // Get ticket replies
    $repliesQuery = "
        SELECT 
            id, replyto, name, message, dt, attachments, staffid, rating
        FROM hesk_replies 
        WHERE replyto = ?
        ORDER BY dt ASC
    ";
    
    $stmt = $db->prepare($repliesQuery);
    $stmt->execute([$ticket['id']]);
    $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format replies
    foreach ($replies as &$reply) {
        $reply['id'] = (int)$reply['id'];
        $reply['replyto'] = (int)$reply['replyto'];
        $reply['staffid'] = (int)$reply['staffid'];
        $reply['rating'] = (int)$reply['rating'];
        $reply['is_staff_reply'] = $reply['staffid'] > 0;
        $reply['created_at'] = $reply['dt'];
        
        // Get staff name if it's a staff reply
        if ($reply['staffid'] > 0) {
            $stmt = $db->prepare("SELECT name FROM hesk_users WHERE id = ? LIMIT 1");
            $stmt->execute([$reply['staffid']]);
            $staffName = $stmt->fetchColumn();
            $reply['staff_name'] = $staffName ?: 'Staff';
        }
    }
    
    // Get attachments if any
    $attachments = [];
    if (!empty($ticket['articles'])) {
        $stmt = $db->prepare("
            SELECT id, ticket_id, saved_name, real_name, size 
            FROM hesk_attachments 
            WHERE ticket_id = ?
        ");
        $stmt->execute([$ticket['id']]);
        $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($attachments as &$attachment) {
            $attachment['id'] = (int)$attachment['id'];
            $attachment['ticket_id'] = (int)$attachment['ticket_id'];
            $attachment['size'] = (int)$attachment['size'];
            $attachment['size_formatted'] = formatFileSize($attachment['size']);
        }
    }
    
    Logger::info('Ticket details accessed', [
        'ticket_id' => $ticket['id'],
        'user_id' => $user['sub']
    ]);
    
    ApiResponse::success([
        'ticket' => $ticket,
        'replies' => $replies,
        'attachments' => $attachments
    ], 'Ticket details retrieved successfully');
    
} catch (PDOException $e) {
    Logger::error('Database error in ticket details', ['error' => $e->getMessage()]);
    ApiResponse::serverError('Database error occurred');
} catch (Exception $e) {
    Logger::error('Unexpected error in ticket details', ['error' => $e->getMessage()]);
    ApiResponse::serverError('An unexpected error occurred');
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

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>