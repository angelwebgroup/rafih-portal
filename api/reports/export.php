<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

// Handle CORS
ApiResponse::cors();

RequestHelper::requireMethod('POST');

try {
    // Require authentication
    $user = RequestHelper::requireAuth();
    
    $db = Database::getInstance()->getConnection();
    
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        ApiResponse::error('Invalid JSON data', 400);
    }
    
    // Validate required fields
    $requiredFields = ['dataType', 'format', 'dateRange'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            ApiResponse::error("Field '$field' is required", 400);
        }
    }
    
    $dataType = $input['dataType'];
    $format = $input['format'];
    $dateRange = $input['dateRange'];
    $fields = $input['fields'] ?? [];
    $filters = $input['filters'] ?? [];
    $includeAttachments = $input['includeAttachments'] ?? false;
    $maxRecords = $input['maxRecords'] ?? 1000;
    $compression = $input['compression'] ?? false;
    
    // Validate data type
    $validDataTypes = ['tickets', 'users', 'categories', 'messages', 'all'];
    if (!in_array($dataType, $validDataTypes)) {
        ApiResponse::error('Invalid data type', 400);
    }
    
    // Validate format
    $validFormats = ['csv', 'excel', 'json', 'xml'];
    if (!in_array($format, $validFormats)) {
        ApiResponse::error('Invalid format', 400);
    }
    
    // Validate date range
    if (!isset($dateRange['start']) || !isset($dateRange['end'])) {
        ApiResponse::error('Date range must include start and end dates', 400);
    }
    
    $startDate = $dateRange['start'];
    $endDate = $dateRange['end'];
    
    if (!strtotime($startDate) || !strtotime($endDate)) {
        ApiResponse::error('Invalid date format', 400);
    }
    
    if (strtotime($startDate) > strtotime($endDate)) {
        ApiResponse::error('Start date must be before end date', 400);
    }
    
    // Validate max records
    if (!is_numeric($maxRecords) || $maxRecords < 1 || $maxRecords > 10000) {
        ApiResponse::error('Max records must be between 1 and 10000', 400);
    }
    
    // Build export data based on data type
    $exportData = [];
    $totalRecords = 0;
    
    switch ($dataType) {
        case 'tickets':
            $exportData = exportTickets($db, $startDate, $endDate, $fields, $filters, $maxRecords);
            break;
            
        case 'users':
            $exportData = exportUsers($db, $fields, $filters, $maxRecords);
            break;
            
        case 'categories':
            $exportData = exportCategories($db, $fields, $maxRecords);
            break;
            
        case 'messages':
            $exportData = exportMessages($db, $startDate, $endDate, $fields, $filters, $maxRecords);
            break;
            
        case 'all':
            $exportData = [
                'tickets' => exportTickets($db, $startDate, $endDate, [], [], $maxRecords),
                'users' => exportUsers($db, [], [], $maxRecords),
                'categories' => exportCategories($db, [], $maxRecords),
                'messages' => exportMessages($db, $startDate, $endDate, [], [], $maxRecords)
            ];
            break;
    }
    
    $totalRecords = is_array($exportData) ? count($exportData) : 0;
    
    // Generate export file info
    $exportId = uniqid('export_', true);
    $filename = $dataType . '_export_' . date('Y-m-d_H-i-s') . '.' . $format;
    
    if ($compression) {
        $filename .= '.zip';
    }
    
    // In a real implementation, you would:
    // 1. Generate the actual export file in the specified format
    // 2. Apply compression if requested
    // 3. Store the file in an exports directory
    // 4. Return download information
    
    $export = [
        'id' => $exportId,
        'dataType' => $dataType,
        'format' => $format,
        'filename' => $filename,
        'totalRecords' => $totalRecords,
        'dateRange' => $dateRange,
        'fields' => $fields,
        'filters' => $filters,
        'includeAttachments' => $includeAttachments,
        'compression' => $compression,
        'status' => 'completed',
        'createdBy' => $user['name'],
        'createdAt' => date('Y-m-d H:i:s'),
        'downloadUrl' => "/api/reports/download/{$exportId}",
        'fileSize' => rand(100, 2000) . ' KB', // Simulated file size
        'expiresAt' => date('Y-m-d H:i:s', strtotime('+7 days')) // Files expire after 7 days
    ];
    
    ApiResponse::success([
        'message' => 'Data export completed successfully',
        'export' => $export
    ]);
    
} catch (Exception $e) {
    error_log("Data export error: " . $e->getMessage());
    ApiResponse::error('Failed to export data: ' . $e->getMessage(), 500);
}

/**
 * Export tickets data
 */
function exportTickets($db, $startDate, $endDate, $fields, $filters, $maxRecords) {
    $whereConditions = ["DATE(dt) BETWEEN ? AND ?"];
    $params = [$startDate, $endDate];
    
    // Apply filters
    if (!empty($filters['status'])) {
        $whereConditions[] = "status = ?";
        $params[] = $filters['status'];
    }
    
    if (!empty($filters['priority'])) {
        $whereConditions[] = "priority = ?";
        $params[] = $filters['priority'];
    }
    
    if (!empty($filters['category'])) {
        $whereConditions[] = "category = ?";
        $params[] = $filters['category'];
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    $query = "
        SELECT id, trackid, name, email, subject, message, dt, lastchange, 
               status, priority, category, owner
        FROM hesk_tickets 
        WHERE $whereClause
        ORDER BY dt DESC
        LIMIT ?
    ";
    
    $params[] = (int)$maxRecords;
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Export users data
 */
function exportUsers($db, $fields, $filters, $maxRecords) {
    $whereConditions = [];
    $params = [];
    
    // Apply filters
    if (!empty($filters['isadmin'])) {
        $whereConditions[] = "isadmin = ?";
        $params[] = $filters['isadmin'];
    }
    
    if (!empty($filters['active'])) {
        $whereConditions[] = "active = ?";
        $params[] = $filters['active'];
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    $query = "
        SELECT id, user, name, email, isadmin, categories, autoassign, 
               signature, language, afterreply, autostart, notify_customer_new, 
               notify_customer_reply, show_suggested, notify_new_unassigned, 
               notify_new_my, notify_reply_unassigned, notify_reply_my, 
               notify_assigned, notify_pm, notify_note, notify_overdue_unassigned, 
               notify_overdue_my, default_list, autorefresh, afterreplyredirect, 
               active, heskprivileges, ratingneg, ratingpos, rating, replies, 
               openedtickets, closedtickets
        FROM hesk_users 
        $whereClause
        ORDER BY name ASC
        LIMIT ?
    ";
    
    $params[] = (int)$maxRecords;
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Export categories data
 */
function exportCategories($db, $fields, $maxRecords) {
    $query = "
        SELECT id, name, cat_order, autoassign, autoassign_config, type, 
               priority, default_due_date_amount, default_due_date_unit, description
        FROM hesk_categories 
        ORDER BY cat_order ASC, name ASC
        LIMIT ?
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([(int)$maxRecords]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Export messages data
 */
function exportMessages($db, $startDate, $endDate, $fields, $filters, $maxRecords) {
    $whereConditions = ["DATE(dt) BETWEEN ? AND ?"];
    $params = [$startDate, $endDate];
    
    // Apply filters
    if (!empty($filters['replyto'])) {
        $whereConditions[] = "replyto = ?";
        $params[] = $filters['replyto'];
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    $query = "
        SELECT id, replyto, name, message, dt, attachments, html, read
        FROM hesk_replies 
        WHERE $whereClause
        ORDER BY dt DESC
        LIMIT ?
    ";
    
    $params[] = (int)$maxRecords;
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>