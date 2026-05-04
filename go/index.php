<?php

$partners = require $_SERVER['DOCUMENT_ROOT'] . '/includes/affiliate-links.php';

$partner = $_GET['partner'] ?? '';
$partner = strtolower(trim($partner, "/ \t\n\r\0\x0B"));

if ($partner === '' || !isset($partners[$partner])) {
    http_response_code(404);
    header('X-Robots-Tag: noindex, nofollow', true);
    echo 'Partner nicht gefunden.';
    exit;
}

$targetUrl = $partners[$partner]['url'] ?? '';

if (!filter_var($targetUrl, FILTER_VALIDATE_URL)) {
    http_response_code(500);
    header('X-Robots-Tag: noindex, nofollow', true);
    echo 'Affiliate-Link ist nicht korrekt konfiguriert.';
    exit;
}

header('X-Robots-Tag: noindex, nofollow', true);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Location: ' . $targetUrl, true, 302);
exit;