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
    $type = $_GET['type'] ?? '';
    $status = $_GET['status'] ?? '';
    
    $offset = ($page - 1) * $limit;
    
    // Sample reports data (in production, this would come from a reports table)
    $reports = [
        [
            'id' => '1',
            'title' => 'Ticket Volume Report',
            'description' => 'Overview of ticket creation and resolution trends',
            'type' => 'chart',
            'lastGenerated' => date('Y-m-d', strtotime('-1 day')),
            'status' => 'ready',
            'createdBy' => $user['name'],
            'createdAt' => date('Y-m-d H:i:s', strtotime('-7 days')),
            'fileSize' => '2.3 MB',
            'downloadCount' => 15
        ],
        [
            'id' => '2',
            'title' => 'Team Performance Report',
            'description' => 'Individual and team productivity metrics',
            'type' => 'table',
            'lastGenerated' => date('Y-m-d', strtotime('-2 days')),
            'status' => 'ready',
            'createdBy' => $user['name'],
            'createdAt' => date('Y-m-d H:i:s', strtotime('-10 days')),
            'fileSize' => '1.8 MB',
            'downloadCount' => 8
        ],
        [
            'id' => '3',
            'title' => 'Category Analysis',
            'description' => 'Breakdown of tickets by category and priority',
            'type' => 'chart',
            'lastGenerated' => date('Y-m-d', strtotime('-3 days')),
            'status' => 'generating',
            'createdBy' => $user['name'],
            'createdAt' => date('Y-m-d H:i:s', strtotime('-5 days')),
            'fileSize' => '0 MB',
            'downloadCount' => 0
        ],
        [
            'id' => '4',
            'title' => 'SLA Compliance Report',
            'description' => 'Service level agreement adherence tracking',
            'type' => 'summary',
            'lastGenerated' => date('Y-m-d', strtotime('-4 days')),
            'status' => 'ready',
            'createdBy' => $user['name'],
            'createdAt' => date('Y-m-d H:i:s', strtotime('-12 days')),
            'fileSize' => '950 KB',
            'downloadCount' => 22
        ]
    ];
    
    // Filter reports if needed
    if ($type) {
        $reports = array_filter($reports, function($report) use ($type) {
            return $report['type'] === $type;
        });
    }
    
    if ($status) {
        $reports = array_filter($reports, function($report) use ($status) {
            return $report['status'] === $status;
        });
    }
    
    // Apply pagination
    $totalReports = count($reports);
    $reports = array_slice($reports, $offset, $limit);
    
    // Calculate pagination info
    $totalPages = ceil($totalReports / $limit);
    $hasNext = $page < $totalPages;
    $hasPrev = $page > 1;
    
    ApiResponse::success([
        'reports' => array_values($reports),
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => $totalReports,
            'total_pages' => $totalPages,
            'has_more' => $hasNext,
            'has_prev' => $hasPrev
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Reports list error: " . $e->getMessage());
    ApiResponse::error('Failed to fetch reports: ' . $e->getMessage(), 500);
}
?>