<?php
require_once __DIR__ . '/config/bootstrap.php';
header('Content-Type: application/json');
if (!is_admin()) { echo json_encode(['error' => 'no access']); exit; }
$db = get_db();
$rows = $db->query("SELECT id, entry_date, challenge_account_balance, main_account_balance, ea_account_balance FROM trading_daily_updates ORDER BY entry_date DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows, JSON_PRETTY_PRINT);