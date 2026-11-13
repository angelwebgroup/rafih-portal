<?php
// Enhanced configuration for secure database and API operations
// Adjust these values to match your local MySQL setup

//define('DB_HOST', 'localhost');
//define('DB_NAME', 'hesk_new');
//define('DB_USER', 'root');
//define('DB_PASS', '');

// Production database (commented out for local development)
define('DB_HOST', 'a2nlmysql61plsk.secureserver.net:3306');
define('DB_NAME', 'hesk');
define('DB_USER', 'hesk');
define('DB_PASS', 'Help-Desk@2023#');

// CORS Configuration
define('CORS_ALLOW_ORIGIN', '*'); // Allow all origins for development
define('CORS_ALLOW_METHODS', 'GET,POST,PUT,DELETE,OPTIONS');
define('CORS_ALLOW_HEADERS', 'Content-Type, Authorization, X-Requested-With, Origin, Accept, X-CSRF-Token');

// Security Configuration
define('JWT_SECRET', 'your_jwt_secret_key_change_in_production');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRY', 3600); // 1 hour

// Rate limiting (requests per minute)
define('RATE_LIMIT_LOGIN', 5);
define('RATE_LIMIT_SIGNUP', 3);
define('RATE_LIMIT_GENERAL', 60);

// Password requirements
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBERS', true);
define('PASSWORD_REQUIRE_SPECIAL', true);

// File upload limits
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Error logging
define('LOG_ERRORS', true);
define('LOG_FILE', __DIR__ . '/logs/api.log');

// Environment
define('ENVIRONMENT', 'development'); // change to 'production' for live
?>