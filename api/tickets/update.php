<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

// Handle CORS
ApiResponse::cors();

RequestHelper::requireMethod('PUT');

require_once '../config/database.php';
require_once '../middleware/auth.php';

// Verify JWT token
$user = verifyJWT();
if (!$user) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    // Validate required fields
    if (!isset($input['ticket_id']) || empty($input['ticket_id'])) {
        throw new Exception('Ticket ID is required');
    }

    $ticketId = (int)$input['ticket_id'];
    
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();

    // Check if ticket exists and user has permission
    $checkQuery = "SELECT id, user_id, status FROM tickets WHERE id = :ticket_id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':ticket_id', $ticketId, PDO::PARAM_INT);
    $checkStmt->execute();
    
    $ticket = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ticket) {
        throw new Exception('Ticket not found');
    }

    // Check permissions - only ticket owner or admin can update
    if ($ticket['user_id'] != $user['id'] && $user['role'] !== 'admin') {
        throw new Exception('You do not have permission to update this ticket');
    }

    // Build update query dynamically based on provided fields
    $updateFields = [];
    $params = [':ticket_id' => $ticketId];

    // Subject
    if (isset($input['subject']) && !empty(trim($input['subject']))) {
        $subject = trim($input['subject']);
        if (strlen($subject) < 5) {
            throw new Exception('Subject must be at least 5 characters long');
        }
        if (strlen($subject) > 200) {
            throw new Exception('Subject must not exceed 200 characters');
        }
        $updateFields[] = "subject = :subject";
        $params[':subject'] = $subject;
    }

    // Message
    if (isset($input['message']) && !empty(trim($input['message']))) {
        $message = trim($input['message']);
        if (strlen($message) < 10) {
            throw new Exception('Message must be at least 10 characters long');
        }
        if (strlen($message) > 2000) {
            throw new Exception('Message must not exceed 2000 characters');
        }
        $updateFields[] = "message = :message";
        $params[':message'] = $message;
    }

    // Priority
    if (isset($input['priority']) && is_numeric($input['priority'])) {
        $priority = (int)$input['priority'];
        if ($priority < 0 || $priority > 3) {
            throw new Exception('Invalid priority value');
        }
        $updateFields[] = "priority = :priority";
        $params[':priority'] = $priority;
    }

    // Status (only admin can change status)
    if (isset($input['status']) && is_numeric($input['status']) && $user['role'] === 'admin') {
        $status = (int)$input['status'];
        if ($status < 0 || $status > 5) {
            throw new Exception('Invalid status value');
        }
        $updateFields[] = "status = :status";
        $params[':status'] = $status;
    }

    // Category
    if (isset($input['category_id']) && is_numeric($input['category_id'])) {
        $categoryId = (int)$input['category_id'];
        
        // Verify category exists
        $categoryQuery = "SELECT id FROM categories WHERE id = :category_id";
        $categoryStmt = $db->prepare($categoryQuery);
        $categoryStmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $categoryStmt->execute();
        
        if ($categoryStmt->fetch()) {
            $updateFields[] = "category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }
    }

    // If no fields to update
    if (empty($updateFields)) {
        throw new Exception('No valid fields provided for update');
    }

    // Add updated_at field
    $updateFields[] = "updated_at = NOW()";

    // Build and execute update query
    $updateQuery = "UPDATE tickets SET " . implode(', ', $updateFields) . " WHERE id = :ticket_id";
    $updateStmt = $db->prepare($updateQuery);
    
    foreach ($params as $key => $value) {
        $updateStmt->bindValue($key, $value);
    }
    
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to update ticket');
    }

    // Get updated ticket data
    $selectQuery = "
        SELECT 
            t.*,
            u.name,
            u.email,
            c.name as category_name,
            CASE 
                WHEN t.status = 0 THEN 'Open'
                WHEN t.status = 1 THEN 'Waiting for staff reply'
                WHEN t.status = 2 THEN 'Waiting for customer reply'
                WHEN t.status = 3 THEN 'Resolved'
                WHEN t.status = 4 THEN 'In progress'
                WHEN t.status = 5 THEN 'On hold'
                ELSE 'Unknown'
            END as status_text,
            CASE 
                WHEN t.priority = 0 THEN 'Low'
                WHEN t.priority = 1 THEN 'Medium'
                WHEN t.priority = 2 THEN 'High'
                WHEN t.priority = 3 THEN 'Critical'
                ELSE 'Unknown'
            END as priority_text
        FROM tickets t
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.id = :ticket_id
    ";
    
    $selectStmt = $db->prepare($selectQuery);
    $selectStmt->bindParam(':ticket_id', $ticketId, PDO::PARAM_INT);
    $selectStmt->execute();
    
    $updatedTicket = $selectStmt->fetch(PDO::FETCH_ASSOC);

    // Log the update
    error_log("Ticket updated: ID {$ticketId} by user {$user['id']}");

    echo json_encode([
        'success' => true,
        'message' => 'Ticket updated successfully',
        'data' => $updatedTicket
    ]);

} catch (Exception $e) {
    error_log("Update ticket error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    error_log("Database error in update ticket: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>