<?php
// ════════════════════════════════════════════════
// config/auth.php – Authentifizierung & Session-Verwaltung
// ════════════════════════════════════════════════

define('MAX_PROFILES', 2);

function start_secure_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    if (!isset($_SESSION['profiles'])) {
        $db = get_db();
        load_user_profiles($db, (int)$_SESSION['user_id']);
    }
}

function login_user(array $user, PDO $db): void {
    session_regenerate_id(true);
    $_SESSION['user_id']  = (int)$user['id'];
    $_SESSION['username'] = $user['display_name'] ?: $user['username'];
    $_SESSION['role']     = $user['role'];
    load_user_profiles($db, (int)$user['id']);
}

function load_user_profiles(PDO $db, int $user_id): void {
    $s = $db->prepare("SELECT * FROM user_profiles WHERE user_id=? ORDER BY is_default DESC, sort_order ASC");
    $s->execute([$user_id]);
    $profiles = $s->fetchAll(PDO::FETCH_ASSOC);
    $_SESSION['profiles'] = $profiles;

    if (empty($_SESSION['active_profile']) && !empty($profiles)) {
        $default = array_filter($profiles, function($p) { return $p['is_default']; });
        $_SESSION['active_profile'] = !empty($default)
            ? reset($default)['profile_name']
            : $profiles[0]['profile_name'];
    }
}

function is_admin(): bool {
    return ($_SESSION['role'] ?? '') === 'admin';
}

/**
 * Ist der eingeloggte User ein Partner oder Admin?
 */
function is_partner(): bool {
    return in_array($_SESSION['role'] ?? '', ['admin', 'partner']);
}

/**
 * Gibt die Rolle des eingeloggten Users zurück.
 */
function get_current_role(): string {
    return $_SESSION['role'] ?? 'user';
}

function current_user_id(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}

function get_profile_names(): array {
    return array_column($_SESSION['profiles'] ?? [], 'profile_name');
}

function get_active_profile(): string {
    return $_SESSION['active_profile'] ?? '';
}

function get_person_options(): array {
    $names = get_profile_names();
    if (count($names) <= 1) return $names;
    $alle_label = count($names) === 2 ? 'Beide' : 'Alle';
    return array_merge($names, [$alle_label]);
}

function person_is_all(string $person): bool {
    return in_array($person, ['Beide', 'Alle'], true);
}

function logout(): void {
    session_destroy();
    header('Location: login.php');
    exit;
}