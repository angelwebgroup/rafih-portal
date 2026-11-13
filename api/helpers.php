<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';

class ApiResponse {
    public static function cors(): void {
       // header('Access-Control-Allow-Origin: ' . CORS_ALLOW_ORIGIN);
        //header('Access-Control-Allow-Methods: ' . CORS_ALLOW_METHODS);
        //header('Access-Control-Allow-Headers: ' . CORS_ALLOW_HEADERS);
        //header('Access-Control-Allow-Credentials: true');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    public static function json($data, int $code = 200): void {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function success($data = null, string $message = 'Success'): void {
        $response = ['success' => true, 'message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }
        self::json($response);
    }

    public static function error(string $message, int $code = 400, $details = null): void {
        $response = ['success' => false, 'error' => $message];
        if ($details !== null) {
            $response['details'] = $details;
        }
        self::json($response, $code);
    }

    public static function unauthorized(string $message = 'Unauthorized'): void {
        self::error($message, 401);
    }

    public static function forbidden(string $message = 'Forbidden'): void {
        self::error($message, 403);
    }

    public static function notFound(string $message = 'Not Found'): void {
        self::error($message, 404);
    }

    public static function rateLimited(string $message = 'Rate limit exceeded'): void {
        self::error($message, 429);
    }

    public static function serverError(string $message = 'Internal Server Error'): void {
        self::error($message, 500);
    }
}

class RequestHelper {
    public static function getJsonBody(): array {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    public static function requireMethod(string $method): void {
        if ($_SERVER['REQUEST_METHOD'] !== $method) {
            ApiResponse::error('Method not allowed', 405);
        }
    }

    public static function requireAuth(): array {
        // Attempt to read Authorization header robustly across environments
        $authHeader = '';

        // 1) getallheaders()
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (is_array($headers)) {
                $authHeader = $headers['Authorization']
                    ?? $headers['authorization']
                    ?? '';
            }
        }

        // 2) Fallback to server variables commonly set by proxies/Apache
        if (!$authHeader) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION']
                ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
                ?? '';
        }

        // 3) As a last resort, scan all server headers for an Authorization-like entry
        if (!$authHeader) {
            foreach ($_SERVER as $key => $value) {
                if (stripos($key, 'AUTHORIZATION') !== false && !empty($value)) {
                    $authHeader = $value;
                    break;
                }
            }
        }

        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            ApiResponse::unauthorized('Authorization token required');
        }

        $token = $matches[1];
        $payload = Security::verifyJWT($token);

        if (!$payload) {
            ApiResponse::unauthorized('Invalid or expired token');
        }

        return $payload;
    }

    public static function checkRateLimit(string $endpoint, int $limit): void {
        if (!Security::checkRateLimit($endpoint, $limit)) {
            ApiResponse::rateLimited();
        }
    }

    public static function validateCSRF(): void {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Security::verifyCSRFToken($token)) {
            ApiResponse::forbidden('CSRF token validation failed');
        }
    }
}

class Logger {
    public static function log(string $level, string $message, array $context = []): void {
        if (!LOG_ERRORS) return;
        
        $logDir = dirname(LOG_FILE);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logMessage = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
        
        error_log($logMessage, 3, LOG_FILE);
    }

    public static function info(string $message, array $context = []): void {
        self::log('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void {
        self::log('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void {
        self::log('ERROR', $message, $context);
    }
}

// Legacy functions for backward compatibility
function cors(): void {
    ApiResponse::cors();
}

function send_json($data, int $code = 200): void {
    ApiResponse::json($data, $code);
}

function read_json_body(): array {
    return RequestHelper::getJsonBody();
}

function generate_token(string $subject): string {
    return Security::generateJWT(['sub' => $subject]);
}

function verifyJWT(): array {
    return RequestHelper::requireAuth();
}
?>