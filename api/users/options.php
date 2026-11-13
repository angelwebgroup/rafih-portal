<?php
require_once '../config.php';
require_once '../db.php';
require_once '../security.php';
require_once '../helpers.php';

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    ApiResponse::error('Method not allowed', 405);
}

try {
    // Authenticate user
    $user = Security::authenticateUser();
    
    // Only administrators can access user options
    if (!$user['isadmin']) {
        ApiResponse::error('Access denied. Administrator privileges required.', 403);
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Get unique dealerships
    $dealerships = [];
    $stmt = $db->prepare("SELECT DISTINCT dealership FROM hesk_users WHERE dealership IS NOT NULL AND dealership != '' ORDER BY dealership");
    $stmt->execute();
    $dealerships = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get unique departments
    $departments = [];
    $stmt = $db->prepare("SELECT DISTINCT department FROM hesk_users WHERE department IS NOT NULL AND department != '' ORDER BY department");
    $stmt->execute();
    $departments = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get unique user types
    $usertypes = [];
    $stmt = $db->prepare("SELECT DISTINCT usertype FROM hesk_users WHERE usertype IS NOT NULL AND usertype != '' ORDER BY usertype");
    $stmt->execute();
    $usertypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get categories for permissions
    $categories = [];
    $stmt = $db->prepare("SELECT id, name FROM hesk_categories ORDER BY cat_order, name");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    Logger::info('User options accessed', [
        'user_id' => $user['sub'],
        'dealerships_count' => count($dealerships),
        'departments_count' => count($departments),
        'usertypes_count' => count($usertypes),
        'categories_count' => count($categories)
    ]);
    
    ApiResponse::success([
        'dealerships' => $dealerships,
        'departments' => $departments,
        'usertypes' => $usertypes,
        'categories' => $categories
    ], 'User options retrieved successfully');
    
} catch (PDOException $e) {
    Logger::error('Database error in user options', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    ApiResponse::error('Database error occurred', 500);
} catch (Exception $e) {
    Logger::error('Error in user options', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    ApiResponse::error($e->getMessage(), 500);
}
?>