<?php
/**
 * trading/get_entries.php – Einträge + Statistiken
 * ==================================================
 * GET ?limit=10&stats=1&from=Y-m-d&to=Y-m-d
 */

require_once __DIR__ . '/../../config/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Zugriff verweigert.']);
    exit;
}

define('TRADING_START_DATE', '2026-06-24');

$db         = get_db();
$limit      = min(100, max(1, (int) ($_GET['limit'] ?? 10)));
$withStats  = ($_GET['stats'] ?? '0') === '1';
$fromDate   = $_GET['from'] ?? null;
$toDate     = $_GET['to']   ?? null;

$where  = [];
$params = [];
if ($fromDate) { $where[] = 'entry_date >= ?'; $params[] = $fromDate; }
if ($toDate)   { $where[] = 'entry_date <= ?'; $params[] = $toDate;   }
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $db->prepare("
    SELECT id, entry_date, trading_day,
           main_account_return, ea_account_return, challenge_account_return,
           main_account_profit, ea_account_profit, challenge_account_profit,
           main_account_balance, ea_account_balance, challenge_account_balance,
           created_at, updated_at
    FROM trading_daily_updates
    {$whereSQL}
    ORDER BY entry_date DESC
    LIMIT {$limit}
");
$stmt->execute($params);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

$entries = array_map(function($row) {
    foreach (['main_account_return','ea_account_return','challenge_account_return',
              'main_account_profit','ea_account_profit','challenge_account_profit',
              'main_account_balance','ea_account_balance','challenge_account_balance'] as $col) {
        $row[$col] = $row[$col] !== null ? (float) $row[$col] : null;
    }
    $row['trading_day'] = (int) $row['trading_day'];
    return $row;
}, $entries);

$response = ['success' => true, 'entries' => $entries];

if ($withStats) {
    $stmtAll = $db->prepare("
        SELECT entry_date, main_account_return, ea_account_return, challenge_account_return
        FROM trading_daily_updates ORDER BY entry_date ASC
    ");
    $stmtAll->execute();
    $all = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

    $today      = date('Y-m-d');
    $lastMonday = date('Y-m-d', strtotime('monday this week'));
    if ($lastMonday > $today) $lastMonday = date('Y-m-d', strtotime('monday last week'));

    $buckets = [
        'main'      => ['all' => [], 'week' => []],
        'ea'        => ['all' => [], 'week' => []],
        'challenge' => ['all' => [], 'week' => []],
    ];
    foreach ($all as $row) {
        $d = $row['entry_date'];
        $map = ['main' => $row['main_account_return'], 'ea' => $row['ea_account_return'], 'challenge' => $row['challenge_account_return']];
        foreach ($map as $key => $val) {
            if ($val === null) continue;
            $f = (float) $val;
            $buckets[$key]['all'][] = $f;
            if ($d >= $lastMonday) $buckets[$key]['week'][] = $f;
        }
    }

    $calc = function(array $returns) {
        if (empty($returns)) return null;
        $factor = 1.0;
        foreach ($returns as $r) $factor *= (1 + $r / 100);
        return round(($factor - 1) * 100, 4);
    };

    $stats = [];
    foreach ($buckets as $key => $data) {
        $stats[$key] = ['all' => $calc($data['all']), 'week' => $calc($data['week'])];
    }

    $response['stats']       = $stats;
    $response['last_monday'] = $lastMonday;
    $response['today']       = $today;
}

echo json_encode($response);