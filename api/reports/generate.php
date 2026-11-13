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
    $requiredFields = ['reportType', 'title', 'dateRange', 'format'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            ApiResponse::error("Field '$field' is required", 400);
        }
    }
    
    $reportType = $input['reportType'];
    $title = $input['title'];
    $dateRange = $input['dateRange'];
    $format = $input['format'];
    $categories = $input['categories'] ?? [];
    $includeCharts = $input['includeCharts'] ?? false;
    $includeTables = $input['includeTables'] ?? true;
    
    // Validate report type
    $validTypes = ['ticket_volume', 'team_performance', 'category_analysis', 'sla_compliance', 'custom'];
    if (!in_array($reportType, $validTypes)) {
        ApiResponse::error('Invalid report type', 400);
    }
    
    // Validate format
    $validFormats = ['pdf', 'excel', 'csv'];
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
    
    // Generate report data based on type
    $reportData = [];
    
    switch ($reportType) {
        case 'ticket_volume':
            // Get ticket volume data
            $query = "
                SELECT 
                    DATE(dt) as date,
                    COUNT(*) as tickets_created,
                    SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as tickets_resolved
                FROM hesk_tickets 
                WHERE DATE(dt) BETWEEN ? AND ?
                GROUP BY DATE(dt)
                ORDER BY date ASC
            ";
            $stmt = $db->prepare($query);
            $stmt->execute([$startDate, $endDate]);
            $reportData['volume_data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'team_performance':
            // Get team performance data
            $query = "
                SELECT 
                    owner,
                    COUNT(*) as tickets_handled,
                    AVG(CASE WHEN status = 3 THEN TIMESTAMPDIFF(HOUR, dt, lastchange) ELSE NULL END) as avg_resolution_time
                FROM hesk_tickets 
                WHERE DATE(dt) BETWEEN ? AND ? AND owner IS NOT NULL AND owner != ''
                GROUP BY owner
                ORDER BY tickets_handled DESC
            ";
            $stmt = $db->prepare($query);
            $stmt->execute([$startDate, $endDate]);
            $reportData['performance_data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'category_analysis':
            // Get category analysis data
            $query = "
                SELECT 
                    c.name as category_name,
                    COUNT(t.id) as ticket_count,
                    AVG(t.priority) as avg_priority,
                    SUM(CASE WHEN t.status = 3 THEN 1 ELSE 0 END) as resolved_count
                FROM hesk_tickets t
                LEFT JOIN hesk_categories c ON t.category = c.id
                WHERE DATE(t.dt) BETWEEN ? AND ?
                GROUP BY t.category, c.name
                ORDER BY ticket_count DESC
            ";
            $stmt = $db->prepare($query);
            $stmt->execute([$startDate, $endDate]);
            $reportData['category_data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'sla_compliance':
            // Get SLA compliance data
            $query = "
                SELECT 
                    priority,
                    COUNT(*) as total_tickets,
                    SUM(CASE WHEN status = 3 AND TIMESTAMPDIFF(HOUR, dt, lastchange) <= 24 THEN 1 ELSE 0 END) as within_sla,
                    AVG(CASE WHEN status = 3 THEN TIMESTAMPDIFF(HOUR, dt, lastchange) ELSE NULL END) as avg_resolution_hours
                FROM hesk_tickets 
                WHERE DATE(dt) BETWEEN ? AND ?
                GROUP BY priority
                ORDER BY priority ASC
            ";
            $stmt = $db->prepare($query);
            $stmt->execute([$startDate, $endDate]);
            $reportData['sla_data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
    }
    
    // Simulate report generation process
    $reportId = uniqid('report_', true);
    $filename = $title . '_' . date('Y-m-d_H-i-s') . '.' . $format;
    
    // In a real implementation, you would:
    // 1. Generate the actual report file (PDF, Excel, CSV)
    // 2. Store it in a reports directory
    // 3. Save report metadata to database
    // 4. Return the report details
    
    $report = [
        'id' => $reportId,
        'title' => $title,
        'type' => $reportType,
        'format' => $format,
        'filename' => $filename,
        'status' => 'completed',
        'createdBy' => $user['name'],
        'createdAt' => date('Y-m-d H:i:s'),
        'dateRange' => $dateRange,
        'categories' => $categories,
        'includeCharts' => $includeCharts,
        'includeTables' => $includeTables,
        'data' => $reportData,
        'downloadUrl' => "/api/reports/download/{$reportId}",
        'fileSize' => rand(500, 5000) . ' KB' // Simulated file size
    ];
    
    ApiResponse::success([
        'message' => 'Report generated successfully',
        'report' => $report
    ]);
    
} catch (Exception $e) {
    error_log("Report generation error: " . $e->getMessage());
    ApiResponse::error('Failed to generate report: ' . $e->getMessage(), 500);
}
?>