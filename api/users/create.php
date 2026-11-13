<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

// Handle CORS
ApiResponse::cors();

RequestHelper::requireMethod('POST');

try {
    // Require authentication
    $user = RequestHelper::requireAuth();
    
    // Only admin users can create users
    if (!$user['isadmin']) {
        ApiResponse::forbidden('Admin access required');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $requiredFields = ['name', 'email', 'user', 'password'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            ApiResponse::badRequest("Field '$field' is required");
        }
    }
    
    // Validate email format
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        ApiResponse::badRequest('Invalid email format');
    }
    
    // Check if username or email already exists
    $stmt = $db->prepare("SELECT id FROM hesk_users WHERE user = ? OR email = ? LIMIT 1");
    $stmt->execute([$input['user'], $input['email']]);
    if ($stmt->fetch()) {
        ApiResponse::badRequest('Username or email already exists');
    }
    
    // Hash password
    $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
    
    // Prepare user data
    $userData = [
        'name' => trim($input['name']),
        'email' => trim($input['email']),
        'user' => trim($input['user']),
        'pass' => $hashedPassword,
        'dealership' => trim($input['dealership'] ?? ''),
        'department' => trim($input['department'] ?? ''),
        'usertype' => trim($input['usertype'] ?? ''),
        'isadmin' => isset($input['isadmin']) ? (int)$input['isadmin'] : 0,
        'activated' => isset($input['activated']) ? (int)$input['activated'] : 1,
        'categories' => $input['categories'] ?? [],
        'dealerships' => $input['dealerships'] ?? []
    ];
    
    // Insert user
    $sql = "INSERT INTO hesk_users (
        name, email, user, pass, dealership, department, usertype, 
        isadmin, activated, dt_reg
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        $userData['name'],
        $userData['email'],
        $userData['user'],
        $userData['pass'],
        $userData['dealership'],
        $userData['department'],
        $userData['usertype'],
        $userData['isadmin'],
        $userData['activated']
    ]);
    
    $userId = $db->lastInsertId();
    
    // Handle categories if user is not admin
    if (!$userData['isadmin'] && !empty($userData['categories'])) {
        $stmt = $db->prepare("DELETE FROM hesk_users_categories WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        foreach ($userData['categories'] as $categoryId) {
            $stmt = $db->prepare("INSERT INTO hesk_users_categories (user_id, category_id) VALUES (?, ?)");
            $stmt->execute([$userId, (int)$categoryId]);
        }
    }
    
    // Get the created user data
    $stmt = $db->prepare("
        SELECT id, name, email, user, dealership, department, usertype, 
               isadmin, activated, dt_reg as created_at
        FROM hesk_users 
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $newUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    Logger::info('User created', [
        'user_id' => $user['id'],
        'created_user_id' => $userId,
        'created_user_email' => $newUser['email']
    ]);
    
    ApiResponse::success([
        'user' => $newUser
    ], 'User created successfully');
    
} catch (PDOException $e) {
    Logger::error('Database error creating user', ['error' => $e->getMessage()]);
    ApiResponse::error('Database error occurred');
} catch (Exception $e) {
    Logger::error('Unexpected error creating user', ['error' => $e->getMessage()]);
    ApiResponse::error('An unexpected error occurred');
}
?>