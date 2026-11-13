<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

// Handle CORS
ApiResponse::cors();

RequestHelper::requireMethod('POST');
RequestHelper::checkRateLimit('forgot_password', 3);

try {
    $data = RequestHelper::getJsonBody();
    
    // Validate input
    $email = trim($data['email'] ?? '');
    
    if (empty($email)) {
        ApiResponse::error('Email is required', 400);
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        ApiResponse::error('Invalid email format', 400);
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id, name, email FROM hesk_users WHERE email = ? AND active = 1 LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Always return success to prevent email enumeration attacks
    if (!$user) {
        Logger::warning('Password reset requested for non-existent email', ['email' => $email]);
        ApiResponse::success(null, 'If the email exists, a password reset link has been sent');
    }
    
    // Generate secure reset token
    $resetToken = bin2hex(random_bytes(32));
    $resetExpiry = date('Y-m-d H:i:s', time() + (RESET_TOKEN_EXPIRY * 3600));
    
    // Store reset token (we'll use tmpvar field for this purpose)
    $resetData = json_encode([
        'token' => $resetToken,
        'expires' => $resetExpiry,
        'created' => date('Y-m-d H:i:s')
    ]);
    
    $stmt = $db->prepare("UPDATE hesk_users SET tmpvar = ? WHERE id = ?");
    $stmt->execute([$resetData, $user['id']]);
    
    // In a real application, you would send an email here
    // For demonstration, we'll log the reset token
    Logger::info('Password reset requested', [
        'user_id' => $user['id'],
        'email' => $email,
        'reset_token' => $resetToken
    ]);
    
    // Simulate email sending (in production, use a proper email service)
    $resetLink = "http://localhost:8100/reset-password?token=" . $resetToken;
    
    ApiResponse::success([
        'message' => 'Password reset instructions have been sent to your email',
        'debug_info' => [
            'reset_link' => $resetLink,
            'expires_in' => RESET_TOKEN_EXPIRY . ' hours'
        ]
    ], 'Password reset email sent');
    
} catch (PDOException $e) {
    Logger::error('Database error during password reset', ['error' => $e->getMessage()]);
    ApiResponse::serverError('Database error occurred');
} catch (Exception $e) {
    Logger::error('Unexpected error during password reset', ['error' => $e->getMessage()]);
    ApiResponse::serverError('An unexpected error occurred');
}
?>