<?php
require_once __DIR__ . '/config/bootstrap.php';

if (is_logged_in()) {
    header('Location: ' . APP_URL . '/');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Bitte Benutzername und Passwort eingeben.';
    } else {
        $db   = get_db();
        $stmt = $db->prepare('SELECT id, username, password_hash FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            login_user((int)$user['id'], $user['username']);
            header('Location: ' . APP_URL . '/');
            exit;
        } else {
            $error = 'Benutzername oder Passwort falsch.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Login – <?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/privat.css">
</head>
<body class="login-body">

<div class="login-wrap">
    <div class="login-logo">
        <span class="logo-mark">M</span>
        <span class="logo-text">monvesto <em>privat</em></span>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" class="login-form" autocomplete="off">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="username">Benutzername</label>
            <input type="text" id="username" name="username" required
                   value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   autocomplete="username">
        </div>
        <div class="form-group">
            <label for="password">Passwort</label>
            <input type="password" id="password" name="password" required
                   autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary btn-full">Anmelden</button>
    </form>
</div>

</body>
</html>