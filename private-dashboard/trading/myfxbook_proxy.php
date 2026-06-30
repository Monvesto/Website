<?php
/**
 * trading/myfxbook_proxy.php
 * ===========================
 * Serverseitiger Proxy für MyFxBook API-Aufrufe.
 * Gibt immer JSON zurück.
 *
 * ?action=save_settings       (POST) → Ausgangskontostand + MyFxBook-ID speichern
 * ?action=save_trading_start  (POST) → Globales Handelstag-Startdatum speichern
 * ?action=fetch_all           (GET)  → Kontodaten + offene Positionen laden
 * ?action=logout              (GET)  → Session beenden
 */

// ── Fehlerbehandlung: immer JSON zurückgeben, nie leere 500er ────────────────
ini_set('display_errors', 0);
set_exception_handler(function ($e) {
    if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'PHP Exception: ' . $e->getMessage()]);
    exit;
});
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'PHP Error: ' . $errstr . ' in ' . basename($errfile) . ':' . $errline]);
    exit;
});

require_once __DIR__ . '/../config/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

// ── Admin-Check ───────────────────────────────────────────────────────────────
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Zugriff verweigert.']);
    exit;
}

$db     = get_db();
$action = $_GET['action'] ?? 'fetch_all';

// ════════════════════════════════════════════════════════════════════════════
// ACTION: save_trading_start – läuft OHNE MyFxBook-Login
// ════════════════════════════════════════════════════════════════════════════
if ($action === 'save_trading_start') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'POST erwartet.']);
        exit;
    }
    $date = trim($_POST['trading_start_date'] ?? '');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        echo json_encode(['success' => false, 'message' => 'Ungültiges Datum.']);
        exit;
    }
    setTradingStartDate($date);
    echo json_encode(['success' => true, 'message' => 'Startdatum gespeichert.']);
    exit;
}

// ════════════════════════════════════════════════════════════════════════════
// ACTION: save_settings – läuft OHNE MyFxBook-Login
// ════════════════════════════════════════════════════════════════════════════
if ($action === 'save_settings') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'POST erwartet.']);
        exit;
    }

    $accountKey = trim($_POST['account_key'] ?? '');
    $startBal   = trim($_POST['start_balance'] ?? '');
    $startDate  = trim($_POST['start_date']    ?? '');
    $calcBasis  = trim($_POST['calc_basis']    ?? '');
    $myfxbookId = trim($_POST['myfxbook_id']   ?? '');
    $currency   = trim($_POST['currency']      ?? 'USD');
    $rfType     = trim($_POST['rf_account_type'] ?? '');
    $rfAccId    = trim($_POST['rf_account_id']   ?? '');
    $rfServer   = trim($_POST['rf_server']       ?? '');
    $rfLeverage = trim($_POST['rf_leverage']     ?? '');

    if (!in_array($accountKey, ['main', 'ea', 'challenge'], true)) {
        echo json_encode(['success' => false, 'message' => 'Ungültiger account_key: ' . $accountKey]);
        exit;
    }

    $startBalFloat  = ($startBal  !== '') ? (float) str_replace(',', '.', $startBal)  : null;
    $calcBasisFloat = ($calcBasis !== '') ? (float) str_replace(',', '.', $calcBasis) : null;
    $myfxbookId     = ($myfxbookId !== '') ? $myfxbookId : null;
    $startDateVal   = ($startDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) ? $startDate : null;
    $rfType         = ($rfType   !== '') ? $rfType   : null;
    $rfAccId        = ($rfAccId  !== '') ? $rfAccId  : null;
    $rfServer       = ($rfServer !== '') ? $rfServer : null;
    $rfLeverage     = ($rfLeverage !== '') ? $rfLeverage : null;

    $stmt = $db->prepare("
        UPDATE trading_account_settings
        SET start_balance    = ?,
            start_date       = ?,
            calc_basis       = ?,
            myfxbook_id      = ?,
            currency         = ?,
            rf_account_type  = ?,
            rf_account_id    = ?,
            rf_server        = ?,
            rf_leverage      = ?
        WHERE account_key = ?
    ");
    $stmt->execute([$startBalFloat, $startDateVal, $calcBasisFloat, $myfxbookId, $currency, $rfType, $rfAccId, $rfServer, $rfLeverage, $accountKey]);

    if ($stmt->rowCount() === 0) {
        $labels = ['main' => 'Main Account', 'ea' => 'Monvesto EA', 'challenge' => 'Road to 100k'];
        $stmt2  = $db->prepare("
            INSERT INTO trading_account_settings
                (account_key, label, start_balance, start_date, calc_basis, myfxbook_id, currency, rf_account_type, rf_account_id, rf_server, rf_leverage)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                start_balance   = VALUES(start_balance),
                start_date      = VALUES(start_date),
                calc_basis      = VALUES(calc_basis),
                myfxbook_id     = VALUES(myfxbook_id),
                currency        = VALUES(currency),
                rf_account_type = VALUES(rf_account_type),
                rf_account_id   = VALUES(rf_account_id),
                rf_server       = VALUES(rf_server),
                rf_leverage     = VALUES(rf_leverage)
        ");
        $stmt2->execute([$accountKey, $labels[$accountKey], $startBalFloat, $startDateVal, $calcBasisFloat, $myfxbookId, $currency, $rfType, $rfAccId, $rfServer, $rfLeverage]);
    }

    echo json_encode(['success' => true, 'message' => 'Einstellungen gespeichert.']);
    exit;
}

// ════════════════════════════════════════════════════════════════════════════
// Ab hier: MyFxBook API nötig
// ════════════════════════════════════════════════════════════════════════════
require_once __DIR__ . '/api/myfxbook.php';

// ── Session-Funktionen ────────────────────────────────────────────────────────
function loadCachedSession($db)
{
    try {
        $stmt = $db->prepare("
            SELECT session FROM trading_myfxbook_session
            WHERE created_at > DATE_SUB(NOW(), INTERVAL 12 HOUR)
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute();
        $val = $stmt->fetchColumn();
        return $val ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

function saveSession($db, $session)
{
    try {
        $db->exec("DELETE FROM trading_myfxbook_session");
        $db->prepare("INSERT INTO trading_myfxbook_session (session) VALUES (?)")
           ->execute([$session]);
    } catch (PDOException $e) {
        error_log('[myfxbook_proxy] Session speichern: ' . $e->getMessage());
    }
}

// ── API-Instanz ───────────────────────────────────────────────────────────────
$cachedSession = loadCachedSession($db);
$api = new MyfxbookApi(
    defined('MYFXBOOK_EMAIL')    ? MYFXBOOK_EMAIL    : '',
    defined('MYFXBOOK_PASSWORD') ? MYFXBOOK_PASSWORD : '',
    $cachedSession
);

// ── Login falls nötig ─────────────────────────────────────────────────────────
if (!$api->getSession()) {
    if (!defined('MYFXBOOK_EMAIL') || !MYFXBOOK_EMAIL) {
        echo json_encode([
            'success' => false,
            'message' => 'MyFxBook Zugangsdaten nicht konfiguriert.',
        ]);
        exit;
    }
    $login = $api->login();
    if (!$login['success']) {
        echo json_encode(['success' => false, 'message' => 'MyFxBook Login fehlgeschlagen: ' . $login['message']]);
        exit;
    }
    saveSession($db, $api->getSession());
}

// ════════════════════════════════════════════════════════════════════════════
// ACTION: logout
// ════════════════════════════════════════════════════════════════════════════
if ($action === 'logout') {
    $api->logout();
    $db->exec("DELETE FROM trading_myfxbook_session");
    echo json_encode(['success' => true, 'message' => 'MyFxBook Session beendet.']);
    exit;
}

// ════════════════════════════════════════════════════════════════════════════
// ACTION: fetch_all
// ════════════════════════════════════════════════════════════════════════════
if ($action === 'fetch_all') {

    $settingsStmt = $db->prepare("SELECT account_key, label, myfxbook_id, start_balance, currency FROM trading_account_settings");
    $settingsStmt->execute();
    $settings = [];
    foreach ($settingsStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $settings[$row['account_key']] = $row;
    }

    $accountsResult = $api->getMyAccounts();
    if (!$accountsResult['success']) {
        $db->exec("DELETE FROM trading_myfxbook_session");
        $login = $api->login();
        if ($login['success']) {
            saveSession($db, $api->getSession());
            $accountsResult = $api->getMyAccounts();
        }
    }

    if (!$accountsResult['success']) {
        echo json_encode(['success' => false, 'message' => 'Konten konnten nicht geladen werden: ' . $accountsResult['message']]);
        exit;
    }

    $allAccounts = $accountsResult['data']['accounts'] ?? [];
    $today       = date('Y-m-d');
    $response    = ['success' => true, 'accounts' => [], 'synced_at' => date('c')];

    foreach (['main', 'ea', 'challenge'] as $key) {
        $cfg      = $settings[$key] ?? [];
        $mfxId    = $cfg['myfxbook_id'] ?? null;
        $label    = $cfg['label']       ?? $key;
        $startBal = (isset($cfg['start_balance']) && $cfg['start_balance'] !== null) ? (float) $cfg['start_balance'] : null;
        $currency = $cfg['currency']    ?? 'USD';

        $accountData = [
            'key'            => $key,
            'label'          => $label,
            'myfxbook_id'    => $mfxId,
            'start_balance'  => $startBal,
            'currency'       => $currency,
            'balance'        => null,
            'equity'         => null,
            'today_profit'   => null,
            'today_return'   => null,
            'open_trades'    => null,
            'open_positions' => [],
            'gain'           => null,
            'drawdown'       => null,
            'error'          => null,
        ];

        if (!$mfxId) {
            $accountData['error'] = 'Keine MyFxBook Account-ID konfiguriert.';
            $response['accounts'][$key] = $accountData;
            continue;
        }

        $mfxAccount = null;
        foreach ($allAccounts as $acc) {
            if ((string) $acc['id'] === (string) $mfxId) {
                $mfxAccount = $acc;
                break;
            }
        }

        if (!$mfxAccount) {
            $accountData['error'] = 'Konto-ID ' . $mfxId . ' nicht gefunden.';
            $response['accounts'][$key] = $accountData;
            continue;
        }

        $balance     = (float) ($mfxAccount['balance'] ?? 0);
        $equity      = (float) ($mfxAccount['equity']  ?? $balance);
        $todayProfit = null;
        $todayReturn = null;

        $dailyResult = $api->getDailyGain((int) $mfxId, $today, $today);
        if ($dailyResult['success']) {
            $dailyData = $dailyResult['data']['dataDaily'] ?? [];
            if (!empty($dailyData)) {
                // MyFxBook liefert dataDaily als [[{...}]] (doppelt verschachtelt)
                $entry = isset($dailyData[0][0]) ? $dailyData[0][0] : $dailyData[0];
                $todayProfit = (float) ($entry['profit'] ?? 0);
                $base = ($startBal && $startBal > 0) ? $startBal : ($balance - $todayProfit);
                if ($base > 0) {
                    $todayReturn = round(($todayProfit / $base) * 100, 4);
                }
            }
        }

        $openResult      = $api->getOpenTrades((int) $mfxId);
        $openPositions   = [];
        $openTradesCount = 0;

        if ($openResult['success']) {
            $openTrades      = $openResult['data']['openTrades'] ?? [];
            $openTradesCount = count($openTrades);
            foreach ($openTrades as $t) {
                $openPositions[] = [
                    'symbol'     => $t['symbol']    ?? '',
                    'type'       => $t['type']       ?? '',
                    'profit'     => $t['profit']     ?? 0,
                    'pips'       => $t['pips']       ?? 0,
                    'open_price' => $t['openPrice']  ?? 0,
                    'open_time'  => $t['openTime']   ?? '',
                ];
            }
        }

        $accountData['balance']        = $balance;
        $accountData['equity']         = $equity;
        $accountData['today_profit']   = $todayProfit;
        $accountData['today_return']   = $todayReturn;
        $accountData['open_trades']    = $openTradesCount;
        $accountData['open_positions'] = $openPositions;
        $accountData['gain']           = (float) ($mfxAccount['gain']     ?? 0);
        $accountData['drawdown']       = (float) ($mfxAccount['drawdown'] ?? 0);

        $response['accounts'][$key] = $accountData;
    }

    echo json_encode($response);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unbekannte Aktion: ' . $action]);