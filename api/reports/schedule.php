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
    $requiredFields = ['reportType', 'title', 'frequency', 'time', 'recipients', 'format'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            ApiResponse::error("Field '$field' is required", 400);
        }
    }
    
    $reportType = $input['reportType'];
    $title = $input['title'];
    $frequency = $input['frequency'];
    $time = $input['time'];
    $recipients = $input['recipients'];
    $format = $input['format'];
    $description = $input['description'] ?? '';
    $startDate = $input['startDate'] ?? date('Y-m-d');
    $endDate = $input['endDate'] ?? null;
    $active = $input['active'] ?? true;
    
    // Validate report type
    $validTypes = ['ticket_volume', 'team_performance', 'category_analysis', 'sla_compliance', 'custom'];
    if (!in_array($reportType, $validTypes)) {
        ApiResponse::error('Invalid report type', 400);
    }
    
    // Validate frequency
    $validFrequencies = ['daily', 'weekly', 'monthly', 'quarterly'];
    if (!in_array($frequency, $validFrequencies)) {
        ApiResponse::error('Invalid frequency', 400);
    }
    
    // Validate format
    $validFormats = ['pdf', 'excel', 'csv'];
    if (!in_array($format, $validFormats)) {
        ApiResponse::error('Invalid format', 400);
    }
    
    // Validate time format (HH:MM)
    if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
        ApiResponse::error('Invalid time format. Use HH:MM format', 400);
    }
    
    // Validate recipients (array of email addresses)
    if (!is_array($recipients) || empty($recipients)) {
        ApiResponse::error('Recipients must be a non-empty array', 400);
    }
    
    foreach ($recipients as $email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            ApiResponse::error("Invalid email address: $email", 400);
        }
    }
    
    // Validate dates
    if (!strtotime($startDate)) {
        ApiResponse::error('Invalid start date format', 400);
    }
    
    if ($endDate && !strtotime($endDate)) {
        ApiResponse::error('Invalid end date format', 400);
    }
    
    if ($endDate && strtotime($startDate) > strtotime($endDate)) {
        ApiResponse::error('Start date must be before end date', 400);
    }
    
    // Calculate next run date based on frequency
    $nextRunDate = calculateNextRunDate($frequency, $time, $startDate);
    
    // Generate schedule ID
    $scheduleId = uniqid('schedule_', true);
    
    // In a real implementation, you would:
    // 1. Save the schedule to a database table (e.g., hesk_report_schedules)
    // 2. Set up a cron job or task scheduler to run the reports
    // 3. Store recipient information securely
    
    $schedule = [
        'id' => $scheduleId,
        'reportType' => $reportType,
        'title' => $title,
        'description' => $description,
        'frequency' => $frequency,
        'time' => $time,
        'recipients' => $recipients,
        'format' => $format,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'nextRunDate' => $nextRunDate,
        'active' => $active,
        'createdBy' => $user['name'],
        'createdAt' => date('Y-m-d H:i:s'),
        'lastRun' => null,
        'runCount' => 0,
        'status' => 'scheduled'
    ];
    
    ApiResponse::success([
        'message' => 'Report schedule created successfully',
        'schedule' => $schedule
    ]);
    
} catch (Exception $e) {
    error_log("Report scheduling error: " . $e->getMessage());
    ApiResponse::error('Failed to create report schedule: ' . $e->getMessage(), 500);
}

/**
 * Calculate the next run date based on frequency and time
 */
function calculateNextRunDate($frequency, $time, $startDate) {
    $start = new DateTime($startDate);
    $now = new DateTime();
    
    // If start date is in the future, use it
    if ($start > $now) {
        $nextRun = $start;
    } else {
        $nextRun = new DateTime();
    }
    
    // Set the time
    list($hour, $minute) = explode(':', $time);
    $nextRun->setTime((int)$hour, (int)$minute, 0);
    
    // If the time has already passed today, move to next occurrence
    if ($nextRun <= $now) {
        switch ($frequency) {
            case 'daily':
                $nextRun->add(new DateInterval('P1D'));
                break;
            case 'weekly':
                $nextRun->add(new DateInterval('P1W'));
                break;
            case 'monthly':
                $nextRun->add(new DateInterval('P1M'));
                break;
            case 'quarterly':
                $nextRun->add(new DateInterval('P3M'));
                break;
        }
    }
    
    return $nextRun->format('Y-m-d H:i:s');
}
?>