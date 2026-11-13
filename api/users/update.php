<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

// Handle CORS
ApiResponse::cors();

RequestHelper::requireMethod('PUT');

try {
    // Require authentication
    $user = RequestHelper::requireAuth();
    
    // Only admin users can update users
    if (!$user['isadmin']) {
        ApiResponse::forbidden('Admin access required');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Get user ID from URL
    $userId = $_GET['id'] ?? null;
    if (!$userId || !is_numeric($userId)) {
        ApiResponse::badRequest('Valid user ID is required');
    }
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id, name, email, user FROM hesk_users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existingUser) {
        ApiResponse::notFound('User not found');
    }
    
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $requiredFields = ['name', 'email', 'user'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            ApiResponse::badRequest("Field '$field' is required");
        }
    }
    
    // Validate email format
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        ApiResponse::badRequest('Invalid email format');
    }
    
    // Check if username or email already exists (excluding current user)
    $stmt = $db->prepare("SELECT id FROM hesk_users WHERE (user = ? OR email = ?) AND id != ? LIMIT 1");
    $stmt->execute([$input['user'], $input['email'], $userId]);
    if ($stmt->fetch()) {
        ApiResponse::badRequest('Username or email already exists');
    }
    
    // Prepare user data
    $userData = [
        'name' => trim($input['name']),
        'email' => trim($input['email']),
        'user' => trim($input['user']),
        'dealership' => trim($input['dealership'] ?? ''),
        'department' => trim($input['department'] ?? ''),
        'usertype' => trim($input['usertype'] ?? ''),
        'isadmin' => isset($input['isadmin']) ? (int)$input['isadmin'] : 0,
        'activated' => isset($input['activated']) ? (int)$input['activated'] : 1,
        'categories' => $input['categories'] ?? [],
        'dealerships' => $input['dealerships'] ?? []
    ];
    
    // Update user
    $sql = "UPDATE hesk_users SET 
        name = ?, email = ?, user = ?, dealership = ?, department = ?, 
        usertype = ?, isadmin = ?, activated = ?
        WHERE id = ?";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        $userData['name'],
        $userData['email'],
        $userData['user'],
        $userData['dealership'],
        $userData['department'],
        $userData['usertype'],
        $userData['isadmin'],
        $userData['activated'],
        $userId
    ]);
    
    // Update password if provided
    if (!empty($input['password'])) {
        $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE hesk_users SET pass = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $userId]);
    }
    
    // Handle categories if user is not admin
    if (!$userData['isadmin'] && isset($userData['categories'])) {
        // Delete existing categories
        $stmt = $db->prepare("DELETE FROM hesk_users_categories WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Insert new categories
        foreach ($userData['categories'] as $categoryId) {
            $stmt = $db->prepare("INSERT INTO hesk_users_categories (user_id, category_id) VALUES (?, ?)");
            $stmt->execute([$userId, (int)$categoryId]);
        }
    }
    
    // Get the updated user data
    $stmt = $db->prepare("
        SELECT id, name, email, user, dealership, department, usertype, 
               isadmin, activated, dt_reg as created_at
        FROM hesk_users 
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    Logger::info('User updated', [
        'user_id' => $user['id'],
        'updated_user_id' => $userId,
        'updated_user_email' => $updatedUser['email']
    ]);
    
    ApiResponse::success([
        'user' => $updatedUser
    ], 'User updated successfully');
    
} catch (PDOException $e) {
    Logger::error('Database error updating user', ['error' => $e->getMessage()]);
    ApiResponse::error('Database error occurred');
} catch (Exception $e) {
    Logger::error('Unexpected error updating user', ['error' => $e->getMessage()]);
    ApiResponse::error('An unexpected error occurred');
}
?>