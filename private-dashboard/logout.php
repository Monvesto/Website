<?php
require_once __DIR__ . '/config/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
}

logout_user();
header('Location: ' . APP_URL . '/login.php?logged_out=1');
exit;