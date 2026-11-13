<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

// Handle CORS
ApiResponse::cors();

RequestHelper::requireMethod('POST');
RequestHelper::checkRateLimit('reset_password', 5);

try {
    $data = RequestHelper::getJsonBody();
    
    // Validate input
    $token = trim($data['token'] ?? '');
    $newPassword = $data['newPassword'] ?? '';
    $confirmPassword = $data['confirmPassword'] ?? '';
    
    if (empty($token) || empty($newPassword)) {
        ApiResponse::error('Token and new password are required', 400);
    }
    
    if ($newPassword !== $confirmPassword) {
        ApiResponse::error('Passwords do not match', 400);
    }
    
    // Password strength validation
    if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
        ApiResponse::error("Password must be at least " . PASSWORD_MIN_LENGTH . " characters long", 400);
    }
    
    if (PASSWORD_REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $newPassword)) {
        ApiResponse::error('Password must contain at least one uppercase letter', 400);
    }
    
    if (PASSWORD_REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $newPassword)) {
        ApiResponse::error('Password must contain at least one lowercase letter', 400);
    }
    
    if (PASSWORD_REQUIRE_NUMBERS && !preg_match('/[0-9]/', $newPassword)) {
        ApiResponse::error('Password must contain at least one number', 400);
    }
    
    if (PASSWORD_REQUIRE_SPECIAL && !preg_match('/[^A-Za-z0-9]/', $newPassword)) {
        ApiResponse::error('Password must contain at least one special character', 400);
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Find user with matching reset token
    $stmt = $db->prepare("SELECT id, email, tmpvar FROM hesk_users WHERE tmpvar IS NOT NULL AND tmpvar != ''");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $validUser = null;
    foreach ($users as $user) {
        $resetData = json_decode($user['tmpvar'], true);
        if ($resetData && isset($resetData['token']) && $resetData['token'] === $token) {
            // Check if token is expired
            if (strtotime($resetData['expires']) > time()) {
                $validUser = $user;
                break;
            }
        }
    }
    
    if (!$validUser) {
        ApiResponse::error('Invalid or expired reset token', 400);
    }
    
    // Hash new password
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password and clear reset token
    $stmt = $db->prepare("UPDATE hesk_users SET pass = ?, tmpvar = '' WHERE id = ?");
    $stmt->execute([$passwordHash, $validUser['id']]);
    
    Logger::info('Password reset completed', [
        'user_id' => $validUser['id'],
        'email' => $validUser['email']
    ]);
    
    ApiResponse::success(null, 'Password has been reset successfully');
    
} catch (PDOException $e) {
    Logger::error('Database error during password reset', ['error' => $e->getMessage()]);
    ApiResponse::serverError('Database error occurred');
} catch (Exception $e) {
    Logger::error('Unexpected error during password reset', ['error' => $e->getMessage()]);
    ApiResponse::serverError('An unexpected error occurred');
}
?>