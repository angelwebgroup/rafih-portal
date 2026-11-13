<?php
// --- CORS headers (inserted by assistant) - place before any output ---
if (!headers_sent()) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Expose-Headers: Authorization');
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
// --- end CORS headers ---


require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

// Handle CORS
ApiResponse::cors();

RequestHelper::requireMethod('POST');
RequestHelper::checkRateLimit('login', 5);

try {
    $data = RequestHelper::getJsonBody();
    
    // Validate input
    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        ApiResponse::error('Username and password are required', 400);
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Check if user exists and get password hash
    $stmt = $db->prepare("SELECT id, name, email, user, pass, isadmin, activated FROM hesk_users WHERE user = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        Logger::warning('Login attempt with non-existent username', ['username' => $username]);
        ApiResponse::error('Invalid credentials', 401);
    }
    
    // Check if account is activated (using activated column instead of active)
    if ($user['activated'] == 1) {
        Logger::warning('Login attempt with inactive account', ['username' => $username]);
        ApiResponse::error('Your account is pending activation by an administrator. Please wait for approval before attempting to login.', 403);
    }
    
    // Verify password (HESK uses MD5 by default, but we'll check for bcrypt first)
    $passwordValid = false;
    if (password_verify($password, $user['pass'])) {
        $passwordValid = true;
    } elseif (md5($password) === $user['pass']) {
        $passwordValid = true;
        // Upgrade to bcrypt for better security
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $db->prepare("UPDATE hesk_users SET pass = ? WHERE id = ?");
        $updateStmt->execute([$newHash, $user['id']]);
    }
    
    if (!$passwordValid) {
        Logger::warning('Login attempt with invalid password', ['username' => $username]);
        ApiResponse::error('Invalid credentials', 401);
    }
    
    // Generate JWT token
    $payload = [
        'sub' => $user['id'],
        'email' => $user['email'],
        'name' => $user['name'],
        'isadmin' => (bool)$user['isadmin'],
        'iat' => time(),
        'exp' => time() + (JWT_EXPIRY * 3600)
    ];
    
    $token = Security::generateJWT($payload);
    
    Logger::info('Successful login', ['user_id' => $user['id'], 'username' => $username]);
    
    ApiResponse::success([
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'username' => $user['user'],
            'isadmin' => (bool)$user['isadmin']
        ]
    ], 'Login successful');
    
} catch (PDOException $e) {
    Logger::error('Database error during login', ['error' => $e->getMessage()]);
    ApiResponse::serverError('Database error occurred');
} catch (Exception $e) {
    Logger::error('Unexpected error during login', ['error' => $e->getMessage()]);
    ApiResponse::serverError('An unexpected error occurred');
}
?>