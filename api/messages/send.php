<?php
require_once '../config.php';
require_once '../db.php';
require_once '../security.php';
require_once '../helpers.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

// Only admins can send messages
if ($user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden - Only admins can send messages']);
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
    
    // Validate required fields
    $requiredFields = ['subject', 'content', 'type'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: $field"]);
            exit;
        }
    }
    
    $subject = trim($input['subject']);
    $content = trim($input['content']);
    $type = strtolower(trim($input['type']));
    $recipients = $input['recipients'] ?? [];
    $hasImage = !empty($input['image']);
    $imageUrl = null;
    
    // Validate type
    if (!in_array($type, ['public', 'private'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid message type. Must be "public" or "private"']);
        exit;
    }
    
    // Validate recipients for private messages
    if ($type === 'private' && empty($recipients)) {
        http_response_code(400);
        echo json_encode(['error' => 'Recipients are required for private messages']);
        exit;
    }
    
    // Handle image upload if provided
    if ($hasImage && !empty($input['image'])) {
        $imageData = $input['image'];
        
        // Basic validation for base64 image
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
            $imageType = $matches[1];
            $allowedTypes = ['jpeg', 'jpg', 'png', 'gif'];
            
            if (in_array(strtolower($imageType), $allowedTypes)) {
                // Generate unique filename
                $filename = 'message_' . uniqid() . '.' . $imageType;
                $uploadDir = '../uploads/messages/';
                
                // Create directory if it doesn't exist
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Remove data URL prefix and decode
                $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
                $decodedImage = base64_decode($imageData);
                
                if ($decodedImage !== false) {
                    $filePath = $uploadDir . $filename;
                    if (file_put_contents($filePath, $decodedImage)) {
                        $imageUrl = '/api/uploads/messages/' . $filename;
                    } else {
                        http_response_code(500);
                        echo json_encode(['error' => 'Failed to save image']);
                        exit;
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid image data']);
                    exit;
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid image type. Allowed: JPEG, PNG, GIF']);
                exit;
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid image format']);
            exit;
        }
    }
    
    // Prepare recipients string for database
    $recipientsStr = '';
    if ($type === 'private' && !empty($recipients)) {
        // Validate that recipients exist (if they are user IDs)
        if (is_array($recipients)) {
            $recipientsStr = implode(',', array_map('trim', $recipients));
        } else {
            $recipientsStr = trim($recipients);
        }
    }
    
    // Insert message into database
    $query = "INSERT INTO hesk_messages (
                subject, 
                content, 
                type, 
                sender_id, 
                sender_name, 
                recipients, 
                has_image, 
                image_url, 
                is_read,
                created_at, 
                updated_at
              ) VALUES (
                :subject, 
                :content, 
                :type, 
                :sender_id, 
                :sender_name, 
                :recipients, 
                :has_image, 
                :image_url, 
                0,
                NOW(), 
                NOW()
              )";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':subject' => $subject,
        ':content' => $content,
        ':type' => $type,
        ':sender_id' => $user['id'],
        ':sender_name' => $user['username'],
        ':recipients' => $recipientsStr,
        ':has_image' => $hasImage ? 1 : 0,
        ':image_url' => $imageUrl
    ]);
    
    $messageId = $pdo->lastInsertId();
    
    // Get the created message
    $getMessageQuery = "SELECT 
                          id,
                          subject,
                          content,
                          type,
                          sender_name,
                          recipients,
                          has_image,
                          image_url,
                          created_at
                        FROM messages 
                        WHERE id = :id";
    
    $getStmt = $pdo->prepare($getMessageQuery);
    $getStmt->execute([':id' => $messageId]);
    $message = $getStmt->fetch(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'message' => 'Message sent successfully',
        'data' => [
            'id' => $message['id'],
            'subject' => $message['subject'],
            'content' => $message['content'],
            'type' => $message['type'],
            'from' => $message['sender_name'],
            'recipients' => $message['recipients'] ? explode(',', $message['recipients']) : [],
            'hasImage' => (bool)$message['has_image'],
            'imageUrl' => $message['image_url'],
            'date' => $message['created_at']
        ]
    ];
    
    http_response_code(201);
    echo json_encode($response);
    
    // Log the action
    logAction($user['id'], 'message_sent', "Sent message: $subject (Type: $type)");
    
} catch (Exception $e) {
    error_log("Error in messages/send.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>