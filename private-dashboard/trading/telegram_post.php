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
 *   channel   string  'live' | 'test' | 'both'
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

if (!defined('TELEGRAM_BOT_TOKEN') || (!defined('TELEGRAM_CHANNEL_LIVE') && !defined('TELEGRAM_CHANNEL_TEST'))) {
    echo json_encode(['success' => false, 'message' => 'Telegram nicht konfiguriert. TELEGRAM_BOT_TOKEN, TELEGRAM_CHANNEL_LIVE und TELEGRAM_CHANNEL_TEST in config.php setzen.']);
    exit;
}

require_once __DIR__ . '/telegram.php';

$db     = get_db();
$action = $_REQUEST['action'] ?? 'post';
$type   = in_array($_REQUEST['type'] ?? 'combined', ['combined','main','ea','challenge'])
          ? ($_REQUEST['type'] ?? 'combined') : 'combined';
$format = ($_REQUEST['format'] ?? 'feed') === 'story' ? 'story' : 'feed';

// ── Channel(s) bestimmen: 'live', 'test' oder 'both' ──────────────────────────
$channelParam = $_REQUEST['channel'] ?? 'test';
$channels     = $channelParam === 'both' ? ['live', 'test'] : [($channelParam === 'live' ? 'live' : 'test')];

// ── Test: Bot + Channel prüfen (nutzt ersten konfigurierten Channel) ──────────
if ($action === 'test') {
    $testChannelId = defined('TELEGRAM_CHANNEL_LIVE') ? TELEGRAM_CHANNEL_LIVE : TELEGRAM_CHANNEL_TEST;
    $tg   = new TelegramBot(TELEGRAM_BOT_TOKEN, $testChannelId);
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

// ── Pro Channel: passende Caption bauen und senden ────────────────────────────
$results  = [];
$anySent  = false;

foreach ($channels as $ch) {
    $channelId = $ch === 'live'
        ? (defined('TELEGRAM_CHANNEL_LIVE') ? TELEGRAM_CHANNEL_LIVE : '')
        : (defined('TELEGRAM_CHANNEL_TEST') ? TELEGRAM_CHANNEL_TEST : '');

    if (!$channelId) {
        $results[$ch] = ['success' => false, 'message' => 'Channel-ID nicht konfiguriert.'];
        continue;
    }

    $caption = $ch === 'live'
        ? buildTelegramCaptionLive($entry, $stats, $settings, $challengeBal)
        : buildTelegramCaptionTest($entry, $stats, $settings, $challengeBal);

    $tg     = new TelegramBot(TELEGRAM_BOT_TOKEN, $channelId);
    $result = $tg->sendPhoto($filepath, $caption, 'HTML');

    $results[$ch] = [
        'success'    => $result['success'],
        'message'    => $result['success'] ? 'OK' : ('Telegram error: ' . $result['message']),
        'message_id' => $result['data']['message_id'] ?? null,
    ];

    if ($result['success']) $anySent = true;
}

// ── Gesamt-Antwort ─────────────────────────────────────────────────────────────
echo json_encode([
    'success' => $anySent,
    'message' => $anySent ? 'Posted to Telegram ✓' : 'Telegram: konnte an keinen Channel gesendet werden.',
    'results' => $results,
]);