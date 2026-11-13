<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

// Handle CORS
ApiResponse::cors();

RequestHelper::requireMethod('GET');

try {
    // Require authentication
    $user = RequestHelper::requireAuth();
    
    // Only admin users can access user list
    if (!$user['isadmin']) {
        ApiResponse::forbidden('Admin access required');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Get query parameters
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    $search = trim($_GET['search'] ?? '');
    $dealership = trim($_GET['dealership'] ?? '');
    $department = trim($_GET['department'] ?? '');
    $usertype = trim($_GET['usertype'] ?? '');
    $activated = $_GET['activated'] ?? '';
    
    // Build WHERE conditions
    $whereConditions = [];
    $params = [];
    
    if (!empty($search)) {
        $whereConditions[] = "(name LIKE ? OR email LIKE ? OR user LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if (!empty($dealership)) {
        $whereConditions[] = "dealership = ?";
        $params[] = $dealership;
    }
    
    if (!empty($department)) {
        $whereConditions[] = "department = ?";
        $params[] = $department;
    }
    
    if (!empty($usertype)) {
        $whereConditions[] = "usertype = ?";
        $params[] = $usertype;
    }
    
    if ($activated !== '') {
        $whereConditions[] = "activated = ?";
        $params[] = (int)$activated;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countQuery = "SELECT COUNT(*) FROM hesk_users $whereClause";
    $stmt = $db->prepare($countQuery);
    $stmt->execute($params);
    $totalCount = (int)$stmt->fetchColumn();
    
    // Get users
    $usersQuery = "
        SELECT 
            id, name, email, user, dealership, department, usertype, 
            isadmin, activated, ratingpos, ratingneg, rating, replies,
            notify_customer_new, notify_customer_reply, autostart, 
            afterreply, show_suggested, language, signature
        FROM hesk_users 
        $whereClause
        ORDER BY name ASC 
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $db->prepare($usersQuery);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format users data
    foreach ($users as &$userData) {
        $userData['id'] = (int)$userData['id'];
        $userData['isadmin'] = (bool)$userData['isadmin'];
        $userData['activated'] = (bool)$userData['activated'];
        $userData['ratingpos'] = (int)$userData['ratingpos'];
        $userData['ratingneg'] = (int)$userData['ratingneg'];
        $userData['rating'] = (float)$userData['rating'];
        $userData['replies'] = (int)$userData['replies'];
        $userData['notify_customer_new'] = (bool)$userData['notify_customer_new'];
        $userData['notify_customer_reply'] = (bool)$userData['notify_customer_reply'];
        $userData['autostart'] = (bool)$userData['autostart'];
        $userData['afterreply'] = (bool)$userData['afterreply'];
        $userData['show_suggested'] = (bool)$userData['show_suggested'];
        
        // Calculate overall rating
        $totalRatings = $userData['ratingpos'] + $userData['ratingneg'];
        if ($totalRatings > 0) {
            $userData['rating_percentage'] = ($userData['ratingpos'] / $totalRatings) * 100;
        } else {
            $userData['rating_percentage'] = 0;
        }
        
        // Determine role based on isadmin and usertype
        if ($userData['isadmin']) {
            $userData['role'] = 'Administrator';
        } else {
            $userData['role'] = ucfirst($userData['usertype'] ?? 'Employee');
        }
        
        // Status text
        $userData['status_text'] = $userData['activated'] ? 'Active' : 'Inactive';
        
        // MFA status (placeholder - HESK doesn't have built-in MFA)
        $userData['mfa_enabled'] = false;
    }
    
    // Get unique values for filters
    $dealerships = [];
    $departments = [];
    $usertypes = [];
    
    $stmt = $db->prepare("SELECT DISTINCT dealership FROM hesk_users WHERE dealership IS NOT NULL AND dealership != '' ORDER BY dealership");
    $stmt->execute();
    $dealerships = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $stmt = $db->prepare("SELECT DISTINCT department FROM hesk_users WHERE department IS NOT NULL AND department != '' ORDER BY department");
    $stmt->execute();
    $departments = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $stmt = $db->prepare("SELECT DISTINCT usertype FROM hesk_users WHERE usertype IS NOT NULL AND usertype != '' ORDER BY usertype");
    $stmt->execute();
    $usertypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    Logger::info('Users list accessed', [
        'user_id' => $user['sub'],
        'page' => $page,
        'limit' => $limit,
        'total_count' => $totalCount
    ]);
    
    ApiResponse::success([
        'users' => $users,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => $totalCount,
            'total_pages' => ceil($totalCount / $limit),
            'has_more' => ($page * $limit) < $totalCount
        ],
        'filters' => [
            'dealerships' => $dealerships,
            'departments' => $departments,
            'usertypes' => $usertypes
        ]
    ], 'Users retrieved successfully');
    
} catch (PDOException $e) {
    Logger::error('Database error retrieving users', ['error' => $e->getMessage()]);
    ApiResponse::serverError('Database error occurred');
} catch (Exception $e) {
    Logger::error('Unexpected error retrieving users', ['error' => $e->getMessage()]);
    ApiResponse::serverError('An unexpected error occurred');
}
?>