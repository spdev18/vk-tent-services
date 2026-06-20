<?php
// VK Tent Services — Central Configuration
// Samastam Technologies Private Limited

// ── Load .env file ────────────────────────────────────────────────────────────
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
} else {
    die('Missing .env file. Copy .env.example to .env and configure it.');
}

function env(string $key, mixed $default = null): mixed
{
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// ── App ───────────────────────────────────────────────────────────────────────
define('APP_NAME',    env('APP_NAME', 'VK Tent Services'));
define('APP_VERSION', env('APP_VERSION', '1.0.0'));
define('APP_ENV',     env('APP_ENV', 'development'));
define('BASE_URL',    env('BASE_URL', 'http://localhost/SamastamTechnologies/vk-tent-services'));

// ── Database ──────────────────────────────────────────────────────────────────
define('DB_HOST',    env('DB_HOST', 'localhost'));
define('DB_PORT',    env('DB_PORT', '3306'));
define('DB_NAME',    env('DB_NAME', 'vk_tent_services'));
define('DB_USER',    env('DB_USER', 'root'));
define('DB_PASS',    env('DB_PASS', ''));
define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));

// ── Paths ─────────────────────────────────────────────────────────────────────
define('BASE_PATH',    dirname(__DIR__));
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('GALLERY_PATH', UPLOADS_PATH . '/gallery');
define('THUMB_PATH',   UPLOADS_PATH . '/thumbnails');
define('PROFILE_PATH', UPLOADS_PATH . '/profile');

// ── URLs ──────────────────────────────────────────────────────────────────────
define('UPLOADS_URL', BASE_URL . '/uploads');
define('GALLERY_URL', UPLOADS_URL . '/gallery');
define('THUMB_URL',   UPLOADS_URL . '/thumbnails');
define('PROFILE_URL', UPLOADS_URL . '/profile');

// ── Upload limits ─────────────────────────────────────────────────────────────
define('MAX_IMAGE_SIZE', (int) env('MAX_IMAGE_SIZE', 8)  * 1024 * 1024);
define('MAX_VIDEO_SIZE', (int) env('MAX_VIDEO_SIZE', 100) * 1024 * 1024);
define('THUMB_WIDTH',    (int) env('THUMB_WIDTH', 400));
define('THUMB_HEIGHT',   (int) env('THUMB_HEIGHT', 300));

define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/webm', 'video/ogg']);

// ── Session ───────────────────────────────────────────────────────────────────
define('SESSION_TIMEOUT',    (int) env('SESSION_TIMEOUT', 3600));
define('MAX_LOGIN_ATTEMPTS', (int) env('MAX_LOGIN_ATTEMPTS', 5));
define('LOCKOUT_DURATION',   (int) env('LOCKOUT_DURATION', 900));

// ── Security ──────────────────────────────────────────────────────────────────
define('CSRF_TOKEN_NAME', '_vk_csrf');

// ── Error reporting ───────────────────────────────────────────────────────────
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
