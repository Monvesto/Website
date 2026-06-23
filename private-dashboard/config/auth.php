<?php
// ════════════════════════════════════════════════
// config/auth.php – Authentifizierung & Session-Verwaltung
//
// KONZEPT:
//   - Jeder User hat eine eigene user_id
//   - Alle Datentabellen sind per user_id getrennt
//   - Ein User kann bis zu MAX_PROFILES Profile anlegen
//     (= Personen im Person-Switcher, z.B. "Marcel", "Kim")
//   - role = 'admin' → Zugriff auf Admin-Panel
//   - role = 'user'  → normaler Nutzer
//
// SESSION-VARIABLEN:
//   $_SESSION['user_id']       → INT, eingeloggte User-ID
//   $_SESSION['username']      → STRING, Anzeigename
//   $_SESSION['role']          → STRING, 'admin' oder 'user'
//   $_SESSION['profiles']      → ARRAY, alle Profile des Users
//   $_SESSION['active_profile']→ STRING, aktiver Profilname (Person-Filter)
// ════════════════════════════════════════════════

// Maximale Anzahl zusätzlicher Profile pro User (änderbar)
define('MAX_PROFILES', 2);

function start_secure_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
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

/**
 * Prüft ob User eingeloggt ist, sonst Redirect zu login.php.
 * Lädt auch Profile in Session falls noch nicht vorhanden.
 */
function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    // Profile in Session laden falls noch nicht da
    if (!isset($_SESSION['profiles'])) {
        $db = get_db();
        load_user_profiles($db, (int)$_SESSION['user_id']);
    }
}

/**
 * User einloggen: Session befüllen.
 */
function login_user(array $user, PDO $db): void {
    session_regenerate_id(true);
    $_SESSION['user_id']        = (int)$user['id'];
    $_SESSION['username']       = $user['display_name'] ?: $user['username'];
    $_SESSION['role']           = $user['role'];
    load_user_profiles($db, (int)$user['id']);
}

/**
 * Profile des Users in Session laden.
 * Setzt auch active_profile auf Default-Profil falls nicht gesetzt.
 */
function load_user_profiles(PDO $db, int $user_id): void {
    $s = $db->prepare("SELECT * FROM user_profiles WHERE user_id=? ORDER BY is_default DESC, sort_order ASC");
    $s->execute([$user_id]);
    $profiles = $s->fetchAll(PDO::FETCH_ASSOC);
    $_SESSION['profiles'] = $profiles;

    // Default-Profil setzen falls noch kein active_profile
    if (empty($_SESSION['active_profile']) && !empty($profiles)) {
        $default = array_filter($profiles, function($p) { return $p['is_default']; });
        $_SESSION['active_profile'] = !empty($default)
            ? reset($default)['profile_name']
            : $profiles[0]['profile_name'];
    }
}

/**
 * Ist der eingeloggte User ein Admin?
 */
function is_admin(): bool {
    return ($_SESSION['role'] ?? '') === 'admin';
}

/**
 * Gibt die user_id des eingeloggten Users zurück.
 */
function current_user_id(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}

/**
 * Gibt alle Profilnamen des Users als Array zurück.
 * z.B. ['Marcel', 'Kim'] oder ['Marcel', 'Kim', 'Lena']
 */
function get_profile_names(): array {
    return array_column($_SESSION['profiles'] ?? [], 'profile_name');
}

/**
 * Gibt den aktuell aktiven Profilnamen zurück.
 */
function get_active_profile(): string {
    return $_SESSION['active_profile'] ?? '';
}

/**
 * Person-Filter Optionen: Alle Profile + "Alle" (entspricht "Beide" bei 2)
 * Bei 1 Profil: nur dieses Profil
 * Bei 2 Profilen: Profil1 / Profil2 / Beide
 * Bei 3+ Profilen: Profil1 / Profil2 / ... / Alle
 */
function get_person_options(): array {
    $names = get_profile_names();
    if (count($names) <= 1) return $names;
    $alle_label = count($names) === 2 ? 'Beide' : 'Alle';
    return array_merge($names, [$alle_label]);
}

/**
 * Ist die gewählte Person "Alle/Beide"?
 */
function person_is_all(string $person): bool {
    return in_array($person, ['Beide', 'Alle'], true);
}

/**
 * Logout: Session zerstören.
 */
function logout(): void {
    session_destroy();
    header('Location: login.php');
    exit;
}