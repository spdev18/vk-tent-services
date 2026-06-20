<?php
// VK Tent Services — Authentication & Session Helpers
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

class Auth {

    public static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            $basePath = rtrim(parse_url(BASE_URL, PHP_URL_PATH), '/') . '/';
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => $basePath,
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_name('vk_sess');
            session_start();
        }
    }

    public static function check(): bool {
        self::startSession();
        if (empty($_SESSION['admin_id'])) {
            return false;
        }
        // Session timeout
        if (!empty($_SESSION['last_active']) && (time() - $_SESSION['last_active']) > SESSION_TIMEOUT) {
            self::logout();
            return false;
        }
        $_SESSION['last_active'] = time();
        return true;
    }

    public static function requireLogin(): void {
        if (!self::check()) {
            header('Location: ' . BASE_URL . '/admin/index.php?timeout=1');
            exit;
        }
    }

    public static function login(string $email, string $password): array {
        $email = strtolower(trim($email));
        $user  = DB::fetchOne('SELECT * FROM admin_users WHERE email = ?', [$email]);

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        // Check lockout
        if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
            $mins = ceil((strtotime($user['locked_until']) - time()) / 60);
            return ['success' => false, 'message' => "Account locked. Try again in {$mins} minute(s)."];
        }

        if (!password_verify($password, $user['password_hash'])) {
            $attempts = (int)$user['login_attempts'] + 1;
            if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                $lockedUntil = date('Y-m-d H:i:s', time() + LOCKOUT_DURATION);
                DB::execute('UPDATE admin_users SET login_attempts=?, locked_until=? WHERE id=?',
                    [$attempts, $lockedUntil, $user['id']]);
                return ['success' => false, 'message' => 'Too many failed attempts. Account locked for 15 minutes.'];
            }
            DB::execute('UPDATE admin_users SET login_attempts=? WHERE id=?', [$attempts, $user['id']]);
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        // Success — reset attempts, start session
        DB::execute('UPDATE admin_users SET login_attempts=0, locked_until=NULL, last_login=NOW() WHERE id=?',
            [$user['id']]);

        self::startSession();
        session_regenerate_id(true);
        $_SESSION['admin_id']    = $user['id'];
        $_SESSION['admin_name']  = $user['name'];
        $_SESSION['admin_email'] = $user['email'];
        $_SESSION['last_active'] = time();

        return ['success' => true];
    }

    public static function logout(): void {
        self::startSession();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function currentUser(): array {
        self::startSession();
        return [
            'id'    => $_SESSION['admin_id']    ?? null,
            'name'  => $_SESSION['admin_name']  ?? '',
            'email' => $_SESSION['admin_email'] ?? '',
        ];
    }

    // CSRF protection
    public static function generateCsrf(): string {
        self::startSession();
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    public static function verifyCsrf(string $token): bool {
        self::startSession();
        return isset($_SESSION[CSRF_TOKEN_NAME]) &&
               hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }

    public static function csrfField(): string {
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . self::generateCsrf() . '">';
    }
}
