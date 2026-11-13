<?php
require_once __DIR__ . '/config.php';

class Security {
    private static $rateLimitStore = [];

    public static function generateJWT(array $payload): string {
        $header = json_encode(['typ' => 'JWT', 'alg' => JWT_ALGORITHM]);
        $payload['exp'] = time() + JWT_EXPIRY;
        $payload['iat'] = time();
        $payload = json_encode($payload);
        
        $headerEncoded = self::base64UrlEncode($header);
        $payloadEncoded = self::base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, JWT_SECRET, true);
        $signatureEncoded = self::base64UrlEncode($signature);
        
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    public static function verifyJWT(string $token): ?array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;
        
        $signature = self::base64UrlDecode($signatureEncoded);
        $expectedSignature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, JWT_SECRET, true);
        
        if (!hash_equals($signature, $expectedSignature)) {
            return null;
        }

        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
        
        if (!$payload || $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    public static function checkRateLimit(string $endpoint, int $limit): bool {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = $ip . ':' . $endpoint;
        $now = time();
        
        if (!isset(self::$rateLimitStore[$key])) {
            self::$rateLimitStore[$key] = ['count' => 1, 'reset' => $now + 60];
            return true;
        }
        
        $data = &self::$rateLimitStore[$key];
        
        if ($now > $data['reset']) {
            $data['count'] = 1;
            $data['reset'] = $now + 60;
            return true;
        }
        
        if ($data['count'] >= $limit) {
            return false;
        }
        
        $data['count']++;
        return true;
    }

    public static function validatePassword(string $password): array {
        $errors = [];
        
        if (strlen($password) < MIN_PASSWORD_LENGTH) {
            $errors[] = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters long';
        }
        
        if (REQUIRE_SPECIAL_CHARS) {
            if (!preg_match('/[A-Z]/', $password)) {
                $errors[] = 'Password must contain at least one uppercase letter';
            }
            if (!preg_match('/[a-z]/', $password)) {
                $errors[] = 'Password must contain at least one lowercase letter';
            }
            if (!preg_match('/[0-9]/', $password)) {
                $errors[] = 'Password must contain at least one number';
            }
            if (!preg_match('/[^A-Za-z0-9]/', $password)) {
                $errors[] = 'Password must contain at least one special character';
            }
        }
        
        return $errors;
    }

    public static function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function sanitizeInput(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function generateCSRFToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    public static function verifyCSRFToken(string $token): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function generateResetToken(): string {
        return bin2hex(random_bytes(32));
    }

    private static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}

class InputValidator {
    public static function validateTicketData(array $data): array {
        $errors = [];
        
        if (empty($data['name']) || strlen(trim($data['name'])) < 2) {
            $errors[] = 'Name must be at least 2 characters long';
        }
        
        if (empty($data['email']) || !Security::validateEmail($data['email'])) {
            $errors[] = 'Valid email address is required';
        }
        
        if (empty($data['subject']) || strlen(trim($data['subject'])) < 5) {
            $errors[] = 'Subject must be at least 5 characters long';
        }
        
        if (empty($data['message']) || strlen(trim($data['message'])) < 10) {
            $errors[] = 'Message must be at least 10 characters long';
        }
        
        return $errors;
    }

    public static function validateUserData(array $data): array {
        $errors = [];
        
        if (empty($data['name']) || strlen(trim($data['name'])) < 2) {
            $errors[] = 'Name must be at least 2 characters long';
        }
        
        if (empty($data['email']) || !Security::validateEmail($data['email'])) {
            $errors[] = 'Valid email address is required';
        }
        
        if (!empty($data['password'])) {
            $passwordErrors = Security::validatePassword($data['password']);
            $errors = array_merge($errors, $passwordErrors);
        }
        
        return $errors;
    }
}
?>