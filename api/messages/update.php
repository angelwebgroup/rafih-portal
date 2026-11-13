<?php
require_once '../config.php';
require_once '../db.php';
require_once '../security.php';
require_once '../helpers.php';

header('Content-Type: application/json');

// Only allow PUT and PATCH requests
if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'PATCH'])) {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check authentication
$user = authenticate();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }
    
    // Get message IDs and action
    $messageIds = $input['messageIds'] ?? [];
    $action = $input['action'] ?? '';
    
    if (empty($messageIds) || !is_array($messageIds)) {
        http_response_code(400);
        echo json_encode(['error' => 'Message IDs are required and must be an array']);
        exit;
    }
    
    if (!in_array($action, ['mark_read', 'mark_unread'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action. Must be "mark_read" or "mark_unread"']);
        exit;
    }
    
    // Sanitize message IDs
    $messageIds = array_map('intval', $messageIds);
    $messageIds = array_filter($messageIds, function($id) { return $id > 0; });
    
    if (empty($messageIds)) {
        http_response_code(400);
        echo json_encode(['error' => 'No valid message IDs provided']);
        exit;
    }
    
    // Create placeholders for IN clause
    $placeholders = str_repeat('?,', count($messageIds) - 1) . '?';
    
    // Check if user has access to these messages (non-admins can only access public messages or private messages sent to them)
    $accessQuery = "SELECT id FROM messages WHERE id IN ($placeholders)";
    $accessParams = $messageIds;
    
    if ($user['role'] !== 'admin') {
        $accessQuery .= " AND (type = 'public' OR (type = 'private' AND FIND_IN_SET(?, recipients)))";
        $accessParams[] = $user['id'];
    }
    
    $accessStmt = $pdo->prepare($accessQuery);
    $accessStmt->execute($accessParams);
    $accessibleMessages = $accessStmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($accessibleMessages)) {
        http_response_code(403);
        echo json_encode(['error' => 'No accessible messages found']);
        exit;
    }
    
    // Update the messages
    $isRead = ($action === 'mark_read') ? 1 : 0;
    $updateQuery = "UPDATE hesk_messages SET is_read = ?, updated_at = NOW() WHERE id IN ($placeholders)";
    $updateParams = array_merge([$isRead], $accessibleMessages);
    
    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->execute($updateParams);
    
    $affectedRows = $updateStmt->rowCount();
    
    // Get updated messages
    $getQuery = "SELECT 
                   id,
                   subject,
                   content,
                   type,
                   sender_name,
                   recipients,
                   is_read,
                   has_image,
                   image_url,
                   created_at,
                   updated_at
                 FROM hesk_messages 
                 WHERE id IN ($placeholders)";
    
    $getStmt = $pdo->prepare($getQuery);
    $getStmt->execute($accessibleMessages);
    $updatedMessages = $getStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the messages
    $formattedMessages = array_map(function($message) {
        return [
            'id' => $message['id'],
            'subject' => $message['subject'],
            'content' => $message['content'],
            'type' => $message['type'],
            'from' => $message['sender_name'],
            'recipients' => $message['recipients'] ? explode(',', $message['recipients']) : [],
            'isRead' => (bool)$message['is_read'],
            'hasImage' => (bool)$message['has_image'],
            'imageUrl' => $message['image_url'],
            'date' => $message['created_at'],
            'updatedAt' => $message['updated_at']
        ];
    }, $updatedMessages);
    
    // Get new unread count
    $unreadQuery = "SELECT COUNT(*) as unread_count FROM hesk_messages 
                    WHERE is_read = 0 AND 
                    (type = 'public' OR (type = 'private' AND FIND_IN_SET(?, recipients)))";
    
    if ($user['role'] === 'admin') {
        $unreadQuery = "SELECT COUNT(*) as unread_count FROM hesk_messages WHERE is_read = 0";
    }
    
    $unreadStmt = $pdo->prepare($unreadQuery);
    if ($user['role'] !== 'admin') {
        $unreadStmt->execute([$user['id']]);
    } else {
        $unreadStmt->execute();
    }
    $unreadCount = $unreadStmt->fetch(PDO::FETCH_ASSOC)['unread_count'];
    
    $response = [
        'success' => true,
        'message' => "Successfully updated $affectedRows message(s)",
        'data' => [
            'updatedMessages' => $formattedMessages,
            'affectedCount' => $affectedRows,
            'unreadCount' => (int)$unreadCount
        ]
    ];
    
    echo json_encode($response);
    
    // Log the action
    $actionText = $action === 'mark_read' ? 'marked as read' : 'marked as unread';
    logAction($user['id'], 'messages_update', "Messages $actionText: " . implode(',', $accessibleMessages));
    
} catch (Exception $e) {
    error_log("Error in messages/update.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>