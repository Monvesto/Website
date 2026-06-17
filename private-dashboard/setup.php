<?php
require_once __DIR__ . '/config/bootstrap.php';

$db    = get_db();
$count = $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ((int)$count > 0) {
    die('<b>Setup bereits abgeschlossen.</b> Diese Datei kann gelöscht werden.');
}

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';

    if (strlen($username) < 3) {
        $message = 'Benutzername muss mindestens 3 Zeichen haben.';
    } elseif (strlen($password) < 8) {
        $message = 'Passwort muss mindestens 8 Zeichen haben.';
    } elseif ($password !== $confirm) {
        $message = 'Passwörter stimmen nicht überein.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $db->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
        $stmt->execute([$username, $hash]);
        $success = true;
        $message = 'Benutzer <strong>' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8')
                 . '</strong> wurde angelegt. <a href="login.php">Zum Login</a>.<br>'
                 . '<br><strong>Wichtig: Bitte diese Datei (setup.php) jetzt löschen!</strong>';
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Setup – <?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/privat.css">
</head>
<body class="login-body">
<div class="login-wrap">
    <div class="login-logo">
        <span class="logo-mark">M</span>
        <span class="logo-text">monvesto <em>setup</em></span>
    </div>

    <?php if ($message): ?>
        <div class="alert <?= $success ? 'alert-success' : 'alert-error' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="POST" action="setup.php">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="username">Benutzername</label>
            <input type="text" id="username" name="username" required minlength="3">
        </div>
        <div class="form-group">
            <label for="password">Passwort (min. 8 Zeichen)</label>
            <input type="password" id="password" name="password" required minlength="8">
        </div>
        <div class="form-group">
            <label for="confirm">Passwort bestätigen</label>
            <input type="password" id="confirm" name="confirm" required minlength="8">
        </div>
        <button type="submit" class="btn btn-primary btn-full">Benutzer anlegen</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>