<?php
require_once '../config.php';
require_once '../db.php';
require_once '../security.php';
require_once '../helpers.php';

header('Content-Type: application/json');

// Only allow DELETE requests
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
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
    
    // Get message IDs
    $messageIds = $input['messageIds'] ?? [];
    
    if (empty($messageIds) || !is_array($messageIds)) {
        http_response_code(400);
        echo json_encode(['error' => 'Message IDs are required and must be an array']);
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
    
    // Check if user has access to these messages
    // Admins can delete any message, regular users can only delete messages they have access to
    $accessQuery = "SELECT id, image_url FROM hesk_messages WHERE id IN ($placeholders)";
    $accessParams = $messageIds;
    
    if ($user['role'] !== 'admin') {
        $accessQuery .= " AND (type = 'public' OR (type = 'private' AND FIND_IN_SET(?, recipients)))";
        $accessParams[] = $user['id'];
    }
    
    $accessStmt = $pdo->prepare($accessQuery);
    $accessStmt->execute($accessParams);
    $accessibleMessages = $accessStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($accessibleMessages)) {
        http_response_code(403);
        echo json_encode(['error' => 'No accessible messages found']);
        exit;
    }
    
    $accessibleIds = array_column($accessibleMessages, 'id');
    $imageUrls = array_filter(array_column($accessibleMessages, 'image_url'));
    
    // Delete messages from database
    $deleteQuery = "DELETE FROM hesk_messages WHERE id IN ($placeholders)";
    $deleteStmt = $pdo->prepare($deleteQuery);
    $result = $deleteStmt->execute($accessibleIds);

    if (!$result) {
        throw new Exception('Failed to delete messages from database');
    }

    $deletedCount = $deleteStmt->rowCount();
    
    // Delete associated image files
    $deletedImages = [];
    foreach ($imageUrls as $imageUrl) {
        if (!empty($imageUrl)) {
            // Convert URL to file path
            $filePath = '../uploads/messages/' . basename($imageUrl);
            if (file_exists($filePath)) {
                if (unlink($filePath)) {
                    $deletedImages[] = $imageUrl;
                } else {
                    error_log("Failed to delete image file: $filePath");
                }
            }
        }
    }
    
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
        'message' => "Successfully deleted $deletedCount message(s)",
        'data' => [
            'deletedCount' => $deletedCount,
            'deletedIds' => $accessibleIds,
            'deletedImages' => $deletedImages,
            'unreadCount' => (int)$unreadCount
        ]
    ];
    
    echo json_encode($response);
    
    // Log the action
    logAction($user['id'], 'messages_delete', "Deleted messages: " . implode(',', $accessibleIds));
    
} catch (Exception $e) {
    error_log("Error in messages/delete.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>