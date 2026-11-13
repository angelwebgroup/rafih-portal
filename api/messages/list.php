<?php
require_once '../config.php';
require_once '../db.php';
require_once '../security.php';
require_once '../helpers.php';

header('Content-Type: application/json');

// Check authentication
$user = authenticate();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get query parameters
    $type = $_GET['type'] ?? 'all'; // all, public, private
    $status = $_GET['status'] ?? 'all'; // all, read, unread
    $search = $_GET['search'] ?? '';
    $limit = min((int)($_GET['limit'] ?? 50), 100); // Max 100 messages
    $offset = (int)($_GET['offset'] ?? 0);
    
    // Build the query
    $whereConditions = [];
    $params = [];
    
    // Filter by type
    if ($type !== 'all') {
        $whereConditions[] = "type = :type";
        $params[':type'] = $type;
    }
    
    // Filter by read status
    if ($status === 'read') {
        $whereConditions[] = "is_read = 1";
    } elseif ($status === 'unread') {
        $whereConditions[] = "is_read = 0";
    }
    
    // Search functionality
    if (!empty($search)) {
        $whereConditions[] = "(subject LIKE :search OR content LIKE :search OR sender_name LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    // Filter by user access (users can only see public messages or private messages sent to them)
    if ($user['role'] !== 'admin') {
        $whereConditions[] = "(type = 'public' OR (type = 'private' AND FIND_IN_SET(:user_id, recipients)))";
        $params[':user_id'] = $user['id'];
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM hesk_messages $whereClause";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get messages
    $query = "SELECT 
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
              $whereClause 
              ORDER BY created_at DESC 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($query);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
    }, $messages);
    
    // Get unread count for the user
    $unreadQuery = "SELECT COUNT(*) as unread_count FROM hesk_messages 
                    WHERE is_read = 0 AND 
                    (type = 'public' OR (type = 'private' AND FIND_IN_SET(:user_id, recipients)))";
    
    if ($user['role'] === 'admin') {
        $unreadQuery = "SELECT COUNT(*) as unread_count FROM hesk_messages WHERE is_read = 0";
    }
    
    $unreadStmt = $pdo->prepare($unreadQuery);
    if ($user['role'] !== 'admin') {
        $unreadStmt->bindValue(':user_id', $user['id']);
    }
    $unreadStmt->execute();
    $unreadCount = $unreadStmt->fetch(PDO::FETCH_ASSOC)['unread_count'];
    
    $response = [
        'success' => true,
        'data' => [
            'messages' => $formattedMessages,
            'pagination' => [
                'total' => (int)$totalCount,
                'limit' => $limit,
                'offset' => $offset,
                'hasMore' => ($offset + $limit) < $totalCount
            ],
            'unreadCount' => (int)$unreadCount
        ]
    ];
    
    echo json_encode($response);
    
    // Log the action
    logAction($user['id'], 'messages_list', "Listed messages - Type: $type, Status: $status");
    
} catch (Exception $e) {
    error_log("Error in messages/list.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>