<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

// Handle CORS
ApiResponse::cors();

RequestHelper::requireMethod('GET');

try {
    // Require authentication
    $user = RequestHelper::requireAuth();
    
    $db = Database::getInstance()->getConnection();
    
    // Get ticket statistics
    $stats = [];
    
    // Total tickets
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM hesk_tickets");
    $stmt->execute();
    $stats['total_tickets'] = (int)$stmt->fetchColumn();
    
    // Open tickets
    $stmt = $db->prepare("SELECT COUNT(*) as open FROM hesk_tickets WHERE status IN (0, 1, 2)");
    $stmt->execute();
    $stats['open_tickets'] = (int)$stmt->fetchColumn();
    
    // Closed tickets
    $stmt = $db->prepare("SELECT COUNT(*) as closed FROM hesk_tickets WHERE status = 3");
    $stmt->execute();
    $stats['closed_tickets'] = (int)$stmt->fetchColumn();
    
    // User's tickets (if not admin)
    if (!$user['isadmin']) {
        $stmt = $db->prepare("SELECT COUNT(*) as user_tickets FROM hesk_tickets WHERE email = ?");
        $stmt->execute([$user['email']]);
        $stats['user_tickets'] = (int)$stmt->fetchColumn();
    }
    
    // Recent tickets (last 10)
    $recentQuery = "
        SELECT id, trackid, name, email, subject, status, priority, dt, lastchange
        FROM hesk_tickets 
        ORDER BY dt DESC 
        LIMIT 10
    ";
    
    if (!$user['isadmin']) {
        $recentQuery = "
            SELECT id, trackid, name, email, subject, status, priority, dt, lastchange
            FROM hesk_tickets 
            WHERE email = ?
            ORDER BY dt DESC 
            LIMIT 10
        ";
    }
    
    $stmt = $db->prepare($recentQuery);
    if (!$user['isadmin']) {
        $stmt->execute([$user['email']]);
    } else {
        $stmt->execute();
    }
    $recentTickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format recent tickets
    foreach ($recentTickets as &$ticket) {
        $ticket['id'] = (int)$ticket['id'];
        $ticket['status'] = (int)$ticket['status'];
        $ticket['priority'] = (int)$ticket['priority'];
        $ticket['status_text'] = getStatusText($ticket['status']);
        $ticket['priority_text'] = getPriorityText($ticket['priority']);
        $ticket['created_at'] = $ticket['dt'];
        $ticket['updated_at'] = $ticket['lastchange'];
    }
    
    // Get categories (if admin)
    $categories = [];
    if ($user['isadmin']) {
        $stmt = $db->prepare("SELECT id, name FROM hesk_categories ORDER BY cat_order ASC");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get user info
    $userInfo = [
        'id' => $user['sub'],
        'name' => $user['name'],
        'email' => $user['email'],
        'isadmin' => $user['isadmin']
    ];
    
    Logger::info('Dashboard summary accessed', ['user_id' => $user['sub']]);
    
    ApiResponse::success([
        'user' => $userInfo,
        'statistics' => $stats,
        'recent_tickets' => $recentTickets,
        'categories' => $categories
    ], 'Dashboard data retrieved successfully');
    
} catch (PDOException $e) {
    Logger::error('Database error in dashboard summary', ['error' => $e->getMessage()]);
    ApiResponse::serverError('Database error occurred');
} catch (Exception $e) {
    Logger::error('Unexpected error in dashboard summary', ['error' => $e->getMessage()]);
    ApiResponse::serverError('An unexpected error occurred');
}

function getStatusText($status) {
    $statusMap = [
        0 => 'New',
        1 => 'Waiting Reply',
        2 => 'Replied',
        3 => 'Resolved',
        4 => 'In Progress',
        5 => 'On Hold'
    ];
    return $statusMap[$status] ?? 'Unknown';
}

function getPriorityText($priority) {
    $priorityMap = [
        0 => 'Critical',
        1 => 'High',
        2 => 'Medium',
        3 => 'Low'
    ];
    return $priorityMap[$priority] ?? 'Unknown';
}
?>