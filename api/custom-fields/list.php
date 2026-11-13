<?php
require_once '../config.php';
require_once '../db.php';
require_once '../security.php';

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    $db = getDbConnection();
    
    // Get category filter if provided
    $category = isset($_GET['category']) ? (int)$_GET['category'] : null;
    
    // Base query to get custom fields
    $sql = "SELECT 
                id,
                name,
                type,
                category,
                req,
                place,
                value,
                order_column
            FROM hesk_custom_fields 
            WHERE use_field = '1'";
    
    $params = [];
    
    // Add category filter if specified
    if ($category !== null) {
        $sql .= " AND (category = ? OR category = 0)";
        $params[] = $category;
    }
    
    // Order by order_column
    $sql .= " ORDER BY order_column ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $customFields = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process custom fields to make them frontend-friendly
    $processedFields = [];
    foreach ($customFields as $field) {
        $processedField = [
            'id' => (int)$field['id'],
            'name' => $field['name'],
            'type' => $field['type'],
            'category' => (int)$field['category'],
            'required' => (bool)$field['req'],
            'placeholder' => $field['place'],
            'order' => (int)$field['order_column']
        ];
        
        // Process field value based on type
        switch ($field['type']) {
            case 'select':
            case 'radio':
            case 'checkbox':
                // Parse options from value field (format: option1#option2#option3)
                $options = [];
                if (!empty($field['value'])) {
                    $optionParts = explode('#', $field['value']);
                    foreach ($optionParts as $option) {
                        if (!empty(trim($option))) {
                            $options[] = trim($option);
                        }
                    }
                }
                $processedField['options'] = $options;
                break;
                
            case 'text':
            case 'textarea':
                // For text fields, value might contain max length
                $processedField['maxLength'] = is_numeric($field['value']) ? (int)$field['value'] : null;
                break;
                
            case 'date':
                // Date fields might have default value or format
                $processedField['defaultValue'] = $field['value'];
                break;
                
            default:
                $processedField['value'] = $field['value'];
        }
        
        $processedFields[] = $processedField;
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $processedFields,
        'count' => count($processedFields)
    ]);
    
} catch (Exception $e) {
    error_log("Custom fields API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch custom fields'
    ]);
}
?>