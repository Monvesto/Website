<?php
/**
 * trading/get_entries.php – Einträge + Statistiken
 */

ini_set('display_errors', 0);
set_exception_handler(function ($e) {
    if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Exception: ' . $e->getMessage()]);
    exit;
});
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Error: ' . $errstr . ' in ' . basename($errfile) . ':' . $errline]);
    exit;
});
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Fatal: ' . $error['message'] . ' in ' . basename($error['file']) . ':' . $error['line']]);
    }
});

require_once __DIR__ . '/../config/bootstrap.php';

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
        SELECT entry_date,
               main_account_profit, ea_account_profit, challenge_account_profit
        FROM trading_daily_updates ORDER BY entry_date ASC
    ");
    $stmtAll->execute();
    $all = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

    $today      = date('Y-m-d');
    $lastMonday = date('Y-m-d', strtotime('monday this week'));
    if ($lastMonday > $today) $lastMonday = date('Y-m-d', strtotime('monday last week'));

    // Startsummen laden
    $settingsStmt = $db->prepare("SELECT account_key, start_balance FROM trading_account_settings");
    $settingsStmt->execute();
    $startBalances = [];
    foreach ($settingsStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $startBalances[$row['account_key']] = $row['start_balance'] !== null ? (float)$row['start_balance'] : null;
    }

    // Summe EUR / Startsumme
    $profitCols = ['main' => 'main_account_profit', 'ea' => 'ea_account_profit', 'challenge' => 'challenge_account_profit'];
    $stats = [];
    foreach ($profitCols as $key => $col) {
        $startBal = $startBalances[$key] ?? null;
        if (!$startBal || $startBal <= 0) {
            $stats[$key] = ['all' => null, 'week' => null];
            continue;
        }
        $sumAll = $sumWeek = 0.0;
        foreach ($all as $row) {
            if ($row[$col] === null) continue;
            $p = (float)$row[$col];
            $sumAll += $p;
            if ($row['entry_date'] >= $lastMonday) $sumWeek += $p;
        }
        $stats[$key] = [
            'all'  => round($sumAll  / $startBal * 100, 2),
            'week' => round($sumWeek / $startBal * 100, 2),
        ];
    }

    $response['stats']       = $stats;
    $response['last_monday'] = $lastMonday;
    $response['today']       = $today;
}

echo json_encode($response);