<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

// Handle CORS
ApiResponse::cors();

RequestHelper::requireMethod('GET');

try {
    // Require authentication
    $user = RequestHelper::requireAuth();
    
    $categoryId = $_GET['id'] ?? '';
    
    if (empty($categoryId)) {
        ApiResponse::error('Category ID is required', 400);
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Get category details
    $categoryQuery = "
        SELECT 
            id, name, cat_order, autoassign, autoassign_config, type, priority, 
            default_due_date_amount, default_due_date_unit, description
        FROM hesk_categories 
        WHERE id = ?
        LIMIT 1
    ";
    
    $stmt = $db->prepare($categoryQuery);
    $stmt->execute([(int)$categoryId]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$category) {
        ApiResponse::notFound('Category not found');
    }
    
    // Format category data
    $category['id'] = (int)$category['id'];
    $category['cat_order'] = (int)$category['cat_order'];
    $category['autoassign'] = (bool)$category['autoassign'];
    $category['type'] = (int)$category['type'];
    $category['priority'] = (int)$category['priority'];
    $category['default_due_date_amount'] = (int)$category['default_due_date_amount'];
    
    // Add human-readable type and priority
    $category['type_text'] = getTypeText($category['type']);
    $category['priority_text'] = getPriorityText($category['priority']);
    
    // Get ticket statistics for this category
    $statsQuery = "
        SELECT 
            COUNT(*) as total_tickets,
            SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as new_tickets,
            SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as waiting_reply,
            SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as replied,
            SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as resolved,
            SUM(CASE WHEN status = 4 THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 5 THEN 1 ELSE 0 END) as on_hold
        FROM hesk_tickets 
        WHERE category = ?
    ";
    
    $stmt = $db->prepare($statsQuery);
    $stmt->execute([$category['id']]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Convert stats to integers
    foreach ($stats as $key => $value) {
        $stats[$key] = (int)$value;
    }
    
    $category['statistics'] = $stats;
    
    // Get recent tickets in this category (last 10)
    $recentTicketsQuery = "
        SELECT 
            id, trackid, name, email, subject, status, priority, dt, lastchange
        FROM hesk_tickets 
        WHERE category = ?
        ORDER BY dt DESC 
        LIMIT 10
    ";
    
    $stmt = $db->prepare($recentTicketsQuery);
    $stmt->execute([$category['id']]);
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
    
    $category['recent_tickets'] = $recentTickets;
    
    Logger::info('Category details accessed', [
        'user_id' => $user['sub'],
        'category_id' => $category['id']
    ]);
    
    ApiResponse::success([
        'category' => $category
    ], 'Category details retrieved successfully');
    
} catch (PDOException $e) {
    Logger::error('Database error in category details', ['error' => $e->getMessage()]);
    ApiResponse::serverError('Database error occurred');
} catch (Exception $e) {
    Logger::error('Unexpected error in category details', ['error' => $e->getMessage()]);
    ApiResponse::serverError('An unexpected error occurred');
}

function getTypeText($type) {
    $typeMap = [
        0 => 'Public',
        1 => 'Private'
    ];
    return $typeMap[$type] ?? 'Unknown';
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
?>