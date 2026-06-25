<?php
/**
 * trading/telegram_post.php
 * ==========================
 * Sendet Trading-Grafik + Caption an Telegram.
 * Wird per AJAX aufgerufen.
 *
 * GET/POST Parameter:
 *   entry_id  int     ID aus trading_daily_updates
 *   type      string  'combined' | 'main' | 'ea' | 'challenge'
 *   format    string  'feed' | 'story'
 *   action    string  'post' | 'test'
 */

require_once __DIR__ . '/../config/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
set_exception_handler(function ($e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
});
set_error_handler(function ($errno, $errstr) {
    echo json_encode(['success' => false, 'message' => $errstr]);
    exit;
});

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

if (!defined('TELEGRAM_BOT_TOKEN') || !defined('TELEGRAM_CHANNEL_ID')) {
    echo json_encode(['success' => false, 'message' => 'Telegram nicht konfiguriert. TELEGRAM_BOT_TOKEN und TELEGRAM_CHANNEL_ID in config.php setzen.']);
    exit;
}

require_once __DIR__ . '/telegram.php';

$db     = get_db();
$action = $_REQUEST['action'] ?? 'post';
$type   = in_array($_REQUEST['type'] ?? 'combined', ['combined','main','ea','challenge'])
          ? ($_REQUEST['type'] ?? 'combined') : 'combined';
$format = ($_REQUEST['format'] ?? 'feed') === 'story' ? 'story' : 'feed';

$tg = new TelegramBot(TELEGRAM_BOT_TOKEN, TELEGRAM_CHANNEL_ID);

// ── Test: Bot + Channel prüfen ────────────────────────────────────────────────
if ($action === 'test') {
    $bot  = $tg->getMe();
    $chat = $tg->getChat();
    echo json_encode([
        'success'  => $bot['success'] && $chat['success'],
        'bot'      => $bot['data']  ?? $bot['message'],
        'chat'     => $chat['data'] ?? $chat['message'],
    ]);
    exit;
}

// ── Eintrag laden ─────────────────────────────────────────────────────────────
$entryId = (int)($_REQUEST['entry_id'] ?? 0);
$sql = $entryId > 0
    ? "SELECT * FROM trading_daily_updates WHERE id = ?"
    : "SELECT * FROM trading_daily_updates ORDER BY entry_date DESC LIMIT 1";
$stmt = $db->prepare($sql);
$stmt->execute($entryId > 0 ? [$entryId] : []);
$entry = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$entry) {
    echo json_encode(['success' => false, 'message' => 'No entry found.']);
    exit;
}

// ── Grafik-Datei prüfen / erstellen ──────────────────────────────────────────
$exportsDir = __DIR__ . '/exports/';
$filename   = $entry['entry_date'] . '_' . $type . '_' . $format . '.png';
$filepath   = $exportsDir . $filename;

// Wenn Grafik noch nicht existiert → erst generieren
if (!file_exists($filepath)) {
    // generate_image.php intern aufrufen
    $_REQUEST['action']   = 'generate';
    $_REQUEST['entry_id'] = $entry['id'];
    $_REQUEST['type']     = $type;
    $_REQUEST['format']   = $format;

    ob_start();
    include __DIR__ . '/generate_image.php';
    ob_end_clean();

    if (!file_exists($filepath)) {
        echo json_encode(['success' => false, 'message' => 'Could not generate image.']);
        exit;
    }
}

// ── Statistiken für Caption laden ─────────────────────────────────────────────
function cumRetTg($db, string $profitCol, string $date, ?float $startBal): ?float {
    if (!$startBal || $startBal <= 0) return null;
    $s = $db->prepare("SELECT $profitCol FROM trading_daily_updates WHERE entry_date<=? AND $profitCol IS NOT NULL");
    $s->execute([$date]);
    $vals = $s->fetchAll(PDO::FETCH_COLUMN);
    if (!$vals) return null;
    return round(array_sum(array_map('floatval', $vals)) / $startBal * 100, 2);
}

function weekRetTg($db, string $profitCol, string $date, ?float $startBal): ?float {
    if (!$startBal || $startBal <= 0) return null;
    $monday = date('Y-m-d', strtotime('monday this week', strtotime($date)));
    if ($monday > $date) $monday = date('Y-m-d', strtotime('monday last week', strtotime($date)));
    $s = $db->prepare("SELECT $profitCol FROM trading_daily_updates WHERE entry_date>=? AND entry_date<=? AND $profitCol IS NOT NULL");
    $s->execute([$monday, $date]);
    $vals = $s->fetchAll(PDO::FETCH_COLUMN);
    if (!$vals) return null;
    return round(array_sum(array_map('floatval', $vals)) / $startBal * 100, 2);
}

$d = $entry['entry_date'];

// Account-Settings + Startsummen
$settingsStmt = $db->prepare("SELECT account_key, start_balance, currency FROM trading_account_settings");
$settingsStmt->execute();
$settings = [];
foreach ($settingsStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $settings[$row['account_key']] = $row;
}

$startBals = [
    'main'      => isset($settings['main']['start_balance'])      ? (float)$settings['main']['start_balance']      : null,
    'ea'        => isset($settings['ea']['start_balance'])        ? (float)$settings['ea']['start_balance']        : null,
    'challenge' => isset($settings['challenge']['start_balance']) ? (float)$settings['challenge']['start_balance'] : null,
];

$stats = [
    'main'      => ['all' => cumRetTg($db,'main_account_profit',      $d, $startBals['main']),      'week' => weekRetTg($db,'main_account_profit',      $d, $startBals['main'])],
    'ea'        => ['all' => cumRetTg($db,'ea_account_profit',        $d, $startBals['ea']),        'week' => weekRetTg($db,'ea_account_profit',        $d, $startBals['ea'])],
    'challenge' => ['all' => cumRetTg($db,'challenge_account_profit', $d, $startBals['challenge']), 'week' => weekRetTg($db,'challenge_account_profit', $d, $startBals['challenge'])],
];

// Challenge-Kontostand
$balStmt = $db->prepare("SELECT challenge_account_balance FROM trading_daily_updates WHERE entry_date<=? AND challenge_account_balance IS NOT NULL ORDER BY entry_date DESC LIMIT 1");
$balStmt->execute([$d]);
$challengeBal = $balStmt->fetchColumn();
$challengeBal = $challengeBal !== false ? (float)$challengeBal : null;

// ── Caption bauen ─────────────────────────────────────────────────────────────
$caption = buildTelegramCaption($entry, $stats, $settings, $challengeBal);

// ── An Telegram senden ────────────────────────────────────────────────────────
$result = $tg->sendPhoto($filepath, $caption, 'HTML');

if ($result['success']) {
    echo json_encode([
        'success' => true,
        'message' => 'Posted to Telegram ✓',
        'message_id' => $result['data']['message_id'] ?? null,
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Telegram error: ' . $result['message'],
    ]);
}