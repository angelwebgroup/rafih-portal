<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

// Handle CORS
ApiResponse::cors();

RequestHelper::requireMethod('PUT');
RequestHelper::checkRateLimit('update_category', 30);

try {
    // Require authentication and admin privileges
    $user = RequestHelper::requireAuth();
    
    if (!$user['isadmin']) {
        ApiResponse::forbidden('Only administrators can update categories');
    }
    
    $data = RequestHelper::getJsonBody();
    
    // Validate category ID
    $categoryId = (int)($data['id'] ?? 0);
    if ($categoryId <= 0) {
        ApiResponse::error('Valid category ID is required', 400);
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Check if category exists
    $stmt = $db->prepare("SELECT * FROM hesk_categories WHERE id = ? LIMIT 1");
    $stmt->execute([$categoryId]);
    $existingCategory = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existingCategory) {
        ApiResponse::notFound('Category not found');
    }
    
    // Build update query dynamically based on provided fields
    $updateFields = [];
    $params = [];
    
    // Name
    if (isset($data['name'])) {
        $name = trim($data['name']);
        if (empty($name)) {
            ApiResponse::error('Category name cannot be empty', 400);
        }
        if (strlen($name) > 255) {
            ApiResponse::error('Category name is too long (max 255 characters)', 400);
        }
        
        // Check if name already exists (excluding current category)
        $stmt = $db->prepare("SELECT id FROM hesk_categories WHERE name = ? AND id != ? LIMIT 1");
        $stmt->execute([$name, $categoryId]);
        if ($stmt->fetch()) {
            ApiResponse::error('A category with this name already exists', 409);
        }
        
        $updateFields[] = "name = ?";
        $params[] = $name;
    }
    
    // Description
    if (isset($data['description'])) {
        $description = trim($data['description']);
        if (strlen($description) > 255) {
            ApiResponse::error('Description is too long (max 255 characters)', 400);
        }
        $updateFields[] = "description = ?";
        $params[] = $description;
    }
    
    // Type
    if (isset($data['type'])) {
        $type = (int)$data['type'];
        if (!in_array($type, [0, 1])) {
            ApiResponse::error('Invalid type. Must be 0 (Public) or 1 (Private)', 400);
        }
        $updateFields[] = "type = ?";
        $params[] = $type;
    }
    
    // Priority
    if (isset($data['priority'])) {
        $priority = (int)$data['priority'];
        if (!in_array($priority, [0, 1, 2, 3])) {
            ApiResponse::error('Invalid priority. Must be 0-3', 400);
        }
        $updateFields[] = "priority = ?";
        $params[] = $priority;
    }
    
    // Category order
    if (isset($data['cat_order'])) {
        $cat_order = (int)$data['cat_order'];
        if ($cat_order < 1) {
            ApiResponse::error('Category order must be at least 1', 400);
        }
        $updateFields[] = "cat_order = ?";
        $params[] = $cat_order;
    }
    
    // Auto-assign
    if (isset($data['autoassign'])) {
        $autoassign = (bool)$data['autoassign'];
        $updateFields[] = "autoassign = ?";
        $params[] = $autoassign ? 1 : 0;
    }
    
    // Auto-assign config
    if (isset($data['autoassign_config'])) {
        $autoassign_config = trim($data['autoassign_config']);
        $updateFields[] = "autoassign_config = ?";
        $params[] = $autoassign_config;
    }
    
    // Default due date amount
    if (isset($data['default_due_date_amount'])) {
        $default_due_date_amount = (int)$data['default_due_date_amount'];
        if ($default_due_date_amount < 0) {
            ApiResponse::error('Default due date amount cannot be negative', 400);
        }
        $updateFields[] = "default_due_date_amount = ?";
        $params[] = $default_due_date_amount;
    }
    
    // Default due date unit
    if (isset($data['default_due_date_unit'])) {
        $default_due_date_unit = trim($data['default_due_date_unit']);
        $validUnits = ['minutes', 'hours', 'days', 'weeks', 'months'];
        if (!in_array($default_due_date_unit, $validUnits)) {
            ApiResponse::error('Invalid due date unit. Must be one of: ' . implode(', ', $validUnits), 400);
        }
        $updateFields[] = "default_due_date_unit = ?";
        $params[] = $default_due_date_unit;
    }
    
    // If no fields to update
    if (empty($updateFields)) {
        ApiResponse::error('No valid fields provided for update', 400);
    }
    
    // Add category ID to params for WHERE clause
    $params[] = $categoryId;
    
    // Execute update
    $updateQuery = "UPDATE hesk_categories SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $db->prepare($updateQuery);
    $stmt->execute($params);
    
    // Get updated category
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
    
    // Get ticket count
    $stmt = $db->prepare("SELECT COUNT(*) FROM hesk_tickets WHERE category = ?");
    $stmt->execute([$categoryId]);
    $category['ticket_count'] = (int)$stmt->fetchColumn();
    
    Logger::info('Category updated', [
        'user_id' => $user['sub'],
        'category_id' => $categoryId,
        'updated_fields' => array_keys($data)
    ]);
    
    ApiResponse::success([
        'category' => $category
    ], 'Category updated successfully');
    
} catch (PDOException $e) {
    Logger::error('Database error in category update', ['error' => $e->getMessage()]);
    ApiResponse::serverError('Database error occurred');
} catch (Exception $e) {
    Logger::error('Unexpected error in category update', ['error' => $e->getMessage()]);
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