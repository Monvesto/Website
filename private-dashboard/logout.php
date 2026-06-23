<?php
require_once __DIR__ . '/config/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
}
session_destroy();
header('Location: login.php?logged_out=1');
exit;