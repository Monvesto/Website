<?php
function start_secure_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/private-dashboard/',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

function login_user(int $user_id, string $username): void {
    session_regenerate_id(true);
    $_SESSION['user_id']  = $user_id;
    $_SESSION['username'] = $username;
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']
        );
    }
    session_destroy();
}