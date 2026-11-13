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
    
    // Get query parameters
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(50, max(10, (int)($_GET['limit'] ?? 20)));
    $search = trim($_GET['search'] ?? '');
    $type = $_GET['type'] ?? '';
    $priority = $_GET['priority'] ?? '';
    
    $offset = ($page - 1) * $limit;
    
    // Build WHERE clause
    $whereConditions = [];
    $params = [];
    
    // Type filter (0 = Public, 1 = Private)
    if ($type !== '') {
        $whereConditions[] = "type = ?";
        $params[] = (int)$type;
    }
    
    // Priority filter
    if ($priority !== '') {
        $whereConditions[] = "priority = ?";
        $params[] = (int)$priority;
    }
    
    // Search filter
    if ($search !== '') {
        $whereConditions[] = "(name LIKE ? OR description LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countQuery = "SELECT COUNT(*) FROM hesk_categories $whereClause";
    $stmt = $db->prepare($countQuery);
    $stmt->execute($params);
    $totalCount = (int)$stmt->fetchColumn();
    
    // Get categories
    $categoriesQuery = "
        SELECT 
            id, name, cat_order, autoassign, autoassign_config, type, priority, 
            default_due_date_amount, default_due_date_unit, description
        FROM hesk_categories 
        $whereClause
        ORDER BY cat_order ASC, name ASC 
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $db->prepare($categoriesQuery);
    $stmt->execute($params);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format categories
    foreach ($categories as &$category) {
        $category['id'] = (int)$category['id'];
        $category['cat_order'] = (int)$category['cat_order'];
        $category['autoassign'] = (bool)$category['autoassign'];
        $category['type'] = (int)$category['type'];
        $category['priority'] = (int)$category['priority'];
        $category['default_due_date_amount'] = (int)$category['default_due_date_amount'];
        
        // Add human-readable type and priority
        $category['type_text'] = getTypeText($category['type']);
        $category['priority_text'] = getPriorityText($category['priority']);
        
        // Get ticket count for this category
        $ticketCountQuery = "SELECT COUNT(*) FROM hesk_tickets WHERE category = ?";
        $ticketStmt = $db->prepare($ticketCountQuery);
        $ticketStmt->execute([$category['id']]);
        $category['ticket_count'] = (int)$ticketStmt->fetchColumn();
    }
    
    // Calculate pagination
    $totalPages = ceil($totalCount / $limit);
    $hasNext = $page < $totalPages;
    $hasPrev = $page > 1;
    
    Logger::info('Categories list accessed', [
        'user_id' => $user['sub'],
        'page' => $page,
        'limit' => $limit,
        'total_count' => $totalCount
    ]);
    
    ApiResponse::success([
        'categories' => $categories,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_count' => $totalCount,
            'per_page' => $limit,
            'has_next' => $hasNext,
            'has_prev' => $hasPrev
        ],
        'filters' => [
            'search' => $search,
            'type' => $type,
            'priority' => $priority
        ]
    ], 'Categories retrieved successfully');
    
} catch (PDOException $e) {
    Logger::error('Database error in categories list', ['error' => $e->getMessage()]);
    ApiResponse::serverError('Database error occurred');
} catch (Exception $e) {
    Logger::error('Unexpected error in categories list', ['error' => $e->getMessage()]);
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
?>
