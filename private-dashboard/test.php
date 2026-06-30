<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/trading/api/myfxbook.php';

header('Content-Type: application/json');

if (!is_admin()) { echo json_encode(['error' => 'no access']); exit; }

$db = get_db();
$api = new MyfxbookApi(MYFXBOOK_EMAIL, MYFXBOOK_PASSWORD);
$login = $api->login();

if (!$login['success']) {
    echo json_encode(['error' => 'Login failed: ' . $login['message']]);
    exit;
}

$today     = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

// Alle drei Account-IDs aus DB laden
$stmt = $db->query("SELECT account_key, myfxbook_id FROM trading_account_settings WHERE myfxbook_id IS NOT NULL");
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];
foreach ($accounts as $acc) {
    $id = (int)$acc['myfxbook_id'];
    $result[$acc['account_key']] = [
        'today'     => $api->getDailyGain($id, $today, $today),
        'yesterday' => $api->getDailyGain($id, $yesterday, $yesterday),
        'last7'     => $api->getDailyGain($id, date('Y-m-d', strtotime('-7 days')), $today),
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT);