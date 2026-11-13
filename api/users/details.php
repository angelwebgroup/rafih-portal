<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

// Handle CORS
ApiResponse::cors();

RequestHelper::requireMethod('GET');

try {
    // Require authentication
    $user = RequestHelper::requireAuth();
    
    // Only admin users can view user details
    if (!$user['isadmin']) {
        ApiResponse::forbidden('Admin access required');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Get user ID from URL
    $userId = $_GET['id'] ?? null;
    if (!$userId || !is_numeric($userId)) {
        ApiResponse::badRequest('Valid user ID is required');
    }
    
    // Get user details
    $stmt = $db->prepare("
        SELECT id, name, email, user, dealership, department, usertype, 
               isadmin, activated, dt_reg as created_at
        FROM hesk_users 
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $userDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userDetails) {
        ApiResponse::notFound('User not found');
    }
    
    // Get user categories if not admin
    $categories = [];
    if (!$userDetails['isadmin']) {
        $stmt = $db->prepare("
            SELECT c.id, c.name 
            FROM hesk_categories c
            INNER JOIN hesk_users_categories uc ON c.id = uc.category_id
            WHERE uc.user_id = ?
        ");
        $stmt->execute([$userId]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get user statistics
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_tickets,
            SUM(CASE WHEN status IN (0, 1, 2, 4, 5) THEN 1 ELSE 0 END) as open_tickets,
            SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as closed_tickets
        FROM hesk_tickets 
        WHERE owner = ?
    ");
    $stmt->execute([$userId]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get recent activity
    $stmt = $db->prepare("
        SELECT 
            'ticket' as type,
            t.id,
            t.subject,
            t.dt as date,
            'Created ticket' as action
        FROM hesk_tickets t
        WHERE t.owner = ?
        
        UNION ALL
        
        SELECT 
            'reply' as type,
            r.replyto as id,
            t.subject,
            r.dt as date,
            'Replied to ticket' as action
        FROM hesk_replies r
        INNER JOIN hesk_tickets t ON r.replyto = t.id
        WHERE r.staffid = ?
        
        ORDER BY date DESC
        LIMIT 10
    ");
    $stmt->execute([$userId, $userId]);
    $recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    Logger::info('User details accessed', [
        'user_id' => $user['id'],
        'viewed_user_id' => $userId
    ]);
    
    ApiResponse::success([
        'user' => $userDetails,
        'categories' => $categories,
        'statistics' => $stats,
        'recent_activity' => $recentActivity
    ]);
    
} catch (PDOException $e) {
    Logger::error('Database error getting user details', ['error' => $e->getMessage()]);
    ApiResponse::error('Database error occurred');
} catch (Exception $e) {
    Logger::error('Unexpected error getting user details', ['error' => $e->getMessage()]);
    ApiResponse::error('An unexpected error occurred');
}
?>