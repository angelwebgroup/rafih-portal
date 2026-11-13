<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

// Handle CORS
ApiResponse::cors();

RequestHelper::requireMethod('POST');
RequestHelper::checkRateLimit('signup', 3);

try {
    $data = RequestHelper::getJsonBody();
    
    // Validate input
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $confirmPassword = $data['confirmPassword'] ?? '';
    $dealership = trim($data['dealership'] ?? '');
    $department = trim($data['department'] ?? '');
    $user = trim($data['user'] ?? '');
    
    // Basic validation
    if (empty($name) || empty($email) || empty($password)) {
        ApiResponse::error('Name, email, and password are required', 400);
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        ApiResponse::error('Invalid email format', 400);
    }
    
    if ($password !== $confirmPassword) {
        ApiResponse::error('Passwords do not match', 400);
    }
    
    // Password strength validation
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        ApiResponse::error("Password must be at least " . PASSWORD_MIN_LENGTH . " characters long", 400);
    }
    
    if (PASSWORD_REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
        ApiResponse::error('Password must contain at least one uppercase letter', 400);
    }
    
    if (PASSWORD_REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $password)) {
        ApiResponse::error('Password must contain at least one lowercase letter', 400);
    }
    
    if (PASSWORD_REQUIRE_NUMBERS && !preg_match('/[0-9]/', $password)) {
        ApiResponse::error('Password must contain at least one number', 400);
    }
    
    if (PASSWORD_REQUIRE_SPECIAL && !preg_match('/[^A-Za-z0-9]/', $password)) {
        ApiResponse::error('Password must contain at least one special character', 400);
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM hesk_users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        ApiResponse::error('Email already registered', 409);
    }
    
    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Generate verification token
    $verificationToken = bin2hex(random_bytes(32));
    
    // Insert new user with correct column names and structure
    $stmt = $db->prepare("
        INSERT INTO hesk_users (
            name, email, pass, activated, isadmin, signature, language, categories, 
            afterreply, autostart, notify_customer_new, notify_customer_reply, 
            show_suggested, notify_new_unassigned, notify_new_my, notify_reply_unassigned, 
            notify_reply_my, notify_assigned, notify_pm, notify_note, 
            notify_overdue_unassigned, notify_overdue_my, default_list, autoassign, 
            heskprivileges, ratingneg, ratingpos, rating, replies, 
            dealership, department, usertype, dealerships, user,
            notify_when_request_by_me, notify_category_users_when_request_created, 
            notify_status_change
        ) VALUES (
            ?, ?, ?, 0, 0, '', 'English', '', 
            1, 1, 1, 1, 
            1, 1, 1, 1, 
            1, 1, 1, 1, 
            1, 1, '', 1, 
            '', 0, 0, 0.0, 0, 
            ?, ?, 'employee', '', ?,
            1, 1, 
            1
        )
    ");
    
    $stmt->execute([
        $name,
        $email,
        $passwordHash,
        $dealership,
        $department,
        $user
    ]);
    
    $userId = $db->lastInsertId();
    
    Logger::info('New user registered', ['user_id' => $userId, 'email' => $email, 'username' => $user]);
    
    // Account created but requires admin activation
    ApiResponse::success([
        'user_id' => $userId,
        'username' => $user,
        'activation_required' => true,
        'message' => 'Registration successful! Your account has been created but requires activation by an administrator. Please wait for approval before attempting to login.'
    ], 'Registration successful - Awaiting activation');
    
} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Duplicate entry
        ApiResponse::error('Email already registered', 409);
    }
    Logger::error('Database error during signup', ['error' => $e->getMessage()]);
    ApiResponse::serverError('Database error occurred');
} catch (Exception $e) {
    Logger::error('Unexpected error during signup', ['error' => $e->getMessage()]);
    ApiResponse::serverError('An unexpected error occurred');
}
?>