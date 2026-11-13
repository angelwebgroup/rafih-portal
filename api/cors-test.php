<?php
// Explicit CORS headers for testing
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Origin, Accept, X-CSRF-Token');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Credentials: false');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set content type
header('Content-Type: application/json');

// Simple test response
echo json_encode([
    'success' => true,
    'message' => 'CORS test successful',
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers' => getallheaders(),
    'timestamp' => date('Y-m-d H:i:s')
]);
?>