<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

// Handle CORS
ApiResponse::cors();

RequestHelper::requireMethod('POST');
RequestHelper::checkRateLimit('create_category', 20);

try {
    // Require authentication and admin privileges
    $user = RequestHelper::requireAuth();
    
    if (!$user['isadmin']) {
        ApiResponse::forbidden('Only administrators can create categories');
    }
    
    $data = RequestHelper::getJsonBody();
    
    // Validate input
    $name = trim($data['name'] ?? '');
    $description = trim($data['description'] ?? '');
    $type = (int)($data['type'] ?? 0); // Default to public
    $priority = (int)($data['priority'] ?? 3); // Default to low priority
    $autoassign = (bool)($data['autoassign'] ?? false);
    $autoassign_config = trim($data['autoassign_config'] ?? '');
    $default_due_date_amount = (int)($data['default_due_date_amount'] ?? 0);
    $default_due_date_unit = trim($data['default_due_date_unit'] ?? 'days');
    
    if (empty($name)) {
        ApiResponse::error('Category name is required', 400);
    }
    
    if (strlen($name) > 255) {
        ApiResponse::error('Category name is too long (max 255 characters)', 400);
    }
    
    if (strlen($description) > 255) {
        ApiResponse::error('Description is too long (max 255 characters)', 400);
    }
    
    // Validate type (0 = Public, 1 = Private)
    if (!in_array($type, [0, 1])) {
        $type = 0; // Default to public
    }
    
    // Validate priority
    if (!in_array($priority, [0, 1, 2, 3])) {
        $priority = 3; // Default to low
    }
    
    // Validate due date unit
    $validUnits = ['minutes', 'hours', 'days', 'weeks', 'months'];
    if (!in_array($default_due_date_unit, $validUnits)) {
        $default_due_date_unit = 'days';
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Check if category name already exists
    $stmt = $db->prepare("SELECT id FROM hesk_categories WHERE name = ? LIMIT 1");
    $stmt->execute([$name]);
    if ($stmt->fetch()) {
        ApiResponse::error('A category with this name already exists', 409);
    }
    
    // Get the next category order
    $stmt = $db->prepare("SELECT MAX(cat_order) as max_order FROM hesk_categories");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $cat_order = ($result['max_order'] ?? 0) + 1;
    
    // Insert new category
    $insertQuery = "
        INSERT INTO hesk_categories (
            name, cat_order, autoassign, autoassign_config, type, priority, 
            default_due_date_amount, default_due_date_unit, description
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    
    $stmt = $db->prepare($insertQuery);
    $stmt->execute([
        $name,
        $cat_order,
        $autoassign ? 1 : 0,
        $autoassign_config,
        $type,
        $priority,
        $default_due_date_amount,
        $default_due_date_unit,
        $description
    ]);
    
    $categoryId = $db->lastInsertId();
    
    // Get the created category
    $stmt = $db->prepare("
        SELECT 
            id, name, cat_order, autoassign, autoassign_config, type, priority, 
            default_due_date_amount, default_due_date_unit, description
        FROM hesk_categories 
        WHERE id = ?
    ");
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Format category data
    $category['id'] = (int)$category['id'];
    $category['cat_order'] = (int)$category['cat_order'];
    $category['autoassign'] = (bool)$category['autoassign'];
    $category['type'] = (int)$category['type'];
    $category['priority'] = (int)$category['priority'];
    $category['default_due_date_amount'] = (int)$category['default_due_date_amount'];
    $category['type_text'] = getTypeText($category['type']);
    $category['priority_text'] = getPriorityText($category['priority']);
    $category['ticket_count'] = 0; // New category has no tickets
    
    Logger::info('Category created', [
        'user_id' => $user['sub'],
        'category_id' => $categoryId,
        'category_name' => $name
    ]);
    
    ApiResponse::success([
        'category' => $category
    ], 'Category created successfully', 201);
    
} catch (PDOException $e) {
    Logger::error('Database error in category creation', ['error' => $e->getMessage()]);
    ApiResponse::serverError('Database error occurred');
} catch (Exception $e) {
    Logger::error('Unexpected error in category creation', ['error' => $e->getMessage()]);
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