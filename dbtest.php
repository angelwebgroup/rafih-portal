<?php
$host = 'a2nlmysql33plsk.secureserver.net:3306';
$db_name = 'hesk';
$db_user = 'hesk';
$db_pass = 'hesk@admin2023';
$db_prefix = 'hesk_';


try {
    $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, $db_user, $db_pass, $options);

    // Connection successful
    echo "Connected to the database successfully!";
} catch (PDOException $e) {
    // Connection failed
    echo "Connection failed: " . $e->getMessage();
}
?>
