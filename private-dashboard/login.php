<?php
// ════════════════════════════════════════════════
// login.php – Login & Registrierung
//
// Zwei Modi:
//   1. Login:         Bestehendem User anmelden
//   2. Registrierung: Neuen Account erstellen
//
// Bei Registrierung wird automatisch:
//   - Ein User-Account angelegt
//   - Ein Default-Profil mit dem Display-Namen angelegt
//   - Der erste registrierte User wird Admin
// ════════════════════════════════════════════════

require_once __DIR__ . '/config/bootstrap.php';

// Bereits eingeloggt → Weiterleitung
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$db     = get_db();
$error  = '';
$mode   = $_GET['mode'] ?? 'login'; // 'login' oder 'register'
$success = '';

// ════════════════════════════════════════════════
// POST-Handler
// ════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';

    // ── Login ──
    if ($act === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Bitte alle Felder ausfüllen.';
        } else {
            $user = $db->prepare("SELECT * FROM users WHERE (username=? OR email=?) AND aktiv=1");
            $user->execute([$username, $username]);
            $user = $user->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                login_user($user, $db);
                header('Location: index.php');
                exit;
            } else {
                $error = 'Benutzername oder Passwort falsch.';
            }
        }
    }

    // ── Registrierung ──
    if ($act === 'register') {
        $username     = trim($_POST['username'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $display_name = trim($_POST['display_name'] ?? '');
        $password     = $_POST['password'] ?? '';
        $password2    = $_POST['password2'] ?? '';

        // Validierung
        if ($username === '' || $email === '' || $display_name === '' || $password === '') {
            $error = 'Bitte alle Felder ausfüllen.';
        } elseif (strlen($username) < 3) {
            $error = 'Benutzername muss mindestens 3 Zeichen haben.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Ungültige E-Mail-Adresse.';
        } elseif (strlen($password) < 8) {
            $error = 'Passwort muss mindestens 8 Zeichen haben.';
        } elseif ($password !== $password2) {
            $error = 'Passwörter stimmen nicht überein.';
        } else {
            // Prüfen ob Username/Email bereits vergeben
            $exists = $db->prepare("SELECT id FROM users WHERE username=? OR email=?");
            $exists->execute([$username, $email]);
            if ($exists->fetch()) {
                $error = 'Benutzername oder E-Mail bereits vergeben.';
            } else {
                // Erster User wird automatisch Admin
                $user_count = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
                $role = $user_count === 0 ? 'admin' : 'user';

                // User anlegen
                $db->prepare("INSERT INTO users (username, email, password, display_name, role) VALUES (?,?,?,?,?)")
                   ->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), $display_name, $role]);

                $user_id = (int)$db->lastInsertId();

                // Default-Profil mit Display-Name anlegen
                $db->prepare("INSERT INTO user_profiles (user_id, profile_name, is_default, sort_order) VALUES (?,?,1,0)")
                   ->execute([$user_id, $display_name]);

                // Direkt einloggen
                $user = $db->prepare("SELECT * FROM users WHERE id=?");
                $user->execute([$user_id]);
                login_user($user->fetch(PDO::FETCH_ASSOC), $db);

                header('Location: index.php?msg=welcome');
                exit;
            }
        }
        $mode = 'register';
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $mode === 'register' ? 'Registrieren' : 'Anmelden' ?> – Monvesto Privat</title>
    <link rel="stylesheet" href="assets/privat.css">
</head>
<body class="login-body">
<div class="login-wrap">
    <div class="login-logo">
        <span class="logo-mark">M</span>
        <span class="logo-text">monvesto <em>privat</em></span>
    </div>

    <?php if ($mode === 'register'): ?>
    <!-- ════ REGISTRIERUNG ════ -->
    <div class="login-form">
        <h2 style="font-size:18px;font-weight:700;margin-bottom:4px">Account erstellen</h2>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:8px">
            Dein Display-Name wird als erstes Profil angelegt.
        </p>

        <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php?mode=register">
            <?= csrf_field() ?>
            <input type="hidden" name="act" value="register">

            <div class="form-group">
                <label>Display-Name (dein Profilname)</label>
                <input type="text" name="display_name" placeholder="z.B. Marcel"
                       value="<?= htmlspecialchars($_POST['display_name'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group">
                <label>Benutzername</label>
                <input type="text" name="username" placeholder="Benutzername (min. 3 Zeichen)"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>E-Mail</label>
                <input type="email" name="email" placeholder="email@beispiel.de"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Passwort (min. 8 Zeichen)</label>
                <input type="password" name="password" placeholder="Passwort" required>
            </div>
            <div class="form-group">
                <label>Passwort wiederholen</label>
                <input type="password" name="password2" placeholder="Passwort bestätigen" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px">
                Account erstellen
            </button>
        </form>
        <p style="text-align:center;font-size:13px;margin-top:16px;color:var(--text-muted)">
            Bereits registriert?
            <a href="login.php" class="text-green">Anmelden →</a>
        </p>
    </div>

    <?php else: ?>
    <!-- ════ LOGIN ════ -->
    <div class="login-form">
        <h2 style="font-size:18px;font-weight:700;margin-bottom:4px">Anmelden</h2>

        <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <?= csrf_field() ?>
            <input type="hidden" name="act" value="login">
            <div class="form-group">
                <label>Benutzername oder E-Mail</label>
                <input type="text" name="username" placeholder="Benutzername oder E-Mail"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group">
                <label>Passwort</label>
                <input type="password" name="password" placeholder="Passwort" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px">
                Anmelden
            </button>
        </form>
        <p style="text-align:center;font-size:13px;margin-top:16px;color:var(--text-muted)">
            Noch kein Account?
            <a href="login.php?mode=register" class="text-green">Registrieren →</a>
        </p>
    </div>
    <?php endif; ?>

</div>
</body>
</html>