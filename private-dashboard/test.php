<?php
require_once __DIR__ . '/config/bootstrap.php';
header('Content-Type: application/json');

$db = get_db();
$cols = $db->query("SHOW COLUMNS FROM trading_account_settings")->fetchAll(PDO::FETCH_COLUMN);
$row = $db->query("SELECT * FROM trading_account_settings WHERE account_key='main'")->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'all_columns'    => $cols,
    'has_rf_server'  => in_array('rf_server', $cols),
    'has_rf_leverage'=> in_array('rf_leverage', $cols),
    'main_row'       => $row,
], JSON_PRETTY_PRINT);