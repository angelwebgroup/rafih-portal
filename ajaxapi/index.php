<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../');

// Database configuration
$db_host = 'a2nlmysql61plsk.secureserver.net:3306';
$db_name = 'hesk';
$db_user = 'hesk';
$db_pass = 'Help-Desk@2023#';
$db_pfix = 'hesk_';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        'success' => 0,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

// Validate required fields
$required = ['user',  'name', 'email', 'dealership', 'department'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        die(json_encode([
            'success' => 0,
            'message' => "Error: All fields are required. Missing $field"
        ]));
    }
}

// Prepare data
$user = $conn->real_escape_string($_POST['user']);
$name = $conn->real_escape_string($_POST['name']);
$email = $conn->real_escape_string($_POST['email']);
$dealership = $conn->real_escape_string($_POST['dealership']);
$department = $conn->real_escape_string($_POST['department']);
$password = md5($_POST['user'].'@2025');

// Insert user
$sql = "INSERT INTO {$db_pfix}users (
    `user`,
    `pass`,
    `name`,
    `email`,
    `dealership`,
    `department`,
    `activated`
) VALUES (
    '$user',
    '$password',
    '$name',
    '$email',
    '$dealership',
    '$department',
    '1'
)";

if ($conn->query($sql)) {
    $conn->close();
    
    // Send email notification to admin (after connection closed)
    require(HESK_PATH . 'hesk_settings.inc.php');
    require(HESK_PATH . 'inc/common.inc.php');
    require(HESK_PATH . 'inc/email_functions.inc.php');
    hesk_load_database_functions();
    hesk_dbConnect();
    
    // Send notification
    hesk_notifyUserRegistered($_POST['user']);
    
    // Success response
    echo 'You are registered with Rafih Portal. You will get an email when your account is activated!';
} else {
    // Error response
    echo 'Registration failed: ' . $conn->error;
    $conn->close();
}

?>
