<?php
/**
 * trading/check_date.php – Datum prüfen
 * GET ?date=Y-m-d
 */

require_once __DIR__ . '/../../config/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Zugriff verweigert.']);
    exit;
}

define('TRADING_START_DATE', '2026-06-24');

$db   = get_db();
$date = trim($_GET['date'] ?? '');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['success' => false, 'message' => 'Ungültiges Datum.']);
    exit;
}

$start      = new DateTime(TRADING_START_DATE);
$target     = new DateTime($date);
$diff       = (int) $start->diff($target)->format('%r%a');
$tradingDay = max(1, $diff + 1);

$stmt = $db->prepare("SELECT id FROM trading_daily_updates WHERE entry_date = ?");
$stmt->execute([$date]);
$id = $stmt->fetchColumn();

echo json_encode([
    'success'     => true,
    'exists'      => (bool) $id,
    'id'          => $id ? (int) $id : null,
    'trading_day' => $tradingDay,
]);