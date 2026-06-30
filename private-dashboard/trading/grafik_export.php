<?php
/**
 * grafik_export.php – Trading: Grafik-Export Endpunkt
 * =====================================================
 * Liefert strukturierte Daten für die spätere automatische Generierung von
 * Telegram- und Instagram-Grafiken.
 *
 * VORBEREITET – noch kein Grafik-Renderer aktiv.
 * Spätere Erweiterung: GD / Imagick für PNG-Generierung direkt hier.
 *
 * GET-Parameter:
 *   account  string  'main' | 'ea' | 'challenge' | 'all'  (Standard: 'all')
 *   period   string  'week' | 'month' | 'all'              (Standard: 'week')
 *   format   string  'json' | 'png'                        (Standard: 'json'; 'png' = TODO)
 */

require_once __DIR__ . '/../config/bootstrap.php';

$pdo = get_db();

header('Content-Type: application/json; charset=utf-8');

// ── Admin-Check ───────────────────────────────────────────────────────────────
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Zugriff verweigert.']);
    exit;
}

// ── Parameter ─────────────────────────────────────────────────────────────────
$account = in_array($_GET['account'] ?? 'all', ['main', 'ea', 'challenge', 'all'])
           ? ($_GET['account'] ?? 'all') : 'all';
$period  = in_array($_GET['period']  ?? 'week', ['week', 'month', 'all'])
           ? ($_GET['period']  ?? 'week') : 'week';
$format  = in_array($_GET['format']  ?? 'json', ['json', 'png'])
           ? ($_GET['format']  ?? 'json') : 'json';

// ── Zeitraum ermitteln ────────────────────────────────────────────────────────
$today             = date('Y-m-d');
$tradingStartDate  = getTradingStartDate();

switch ($period) {
    case 'week':
        $fromDate    = date('Y-m-d', strtotime('monday this week'));
        if ($fromDate > $today) $fromDate = date('Y-m-d', strtotime('monday last week'));
        $toDate      = $today;
        $kw          = date('W');
        $periodLabel = 'Diese Woche (KW' . $kw . ')';
        break;

    case 'month':
        $fromDate    = date('Y-m-01');
        $toDate      = $today;
        $periodLabel = date('F Y'); // z.B. "Juni 2026"
        break;

    case 'all':
    default:
        $fromDate    = $tradingStartDate;
        $toDate      = $today;
        $periodLabel = 'Seit Start (' . date('d.m.Y', strtotime($tradingStartDate)) . ')';
        break;
}

// ── Datenbankabfrage ──────────────────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT entry_date, trading_day,
           main_account_return, ea_account_return, challenge_account_return
    FROM trading_daily_updates
    WHERE entry_date >= ? AND entry_date <= ?
    ORDER BY entry_date ASC
");
$stmt->execute([$fromDate, $toDate]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Einträge aufbereiten ──────────────────────────────────────────────────────
$entries = [];
foreach ($rows as $row) {
    $entry = [
        'date'        => $row['entry_date'],
        'trading_day' => (int) $row['trading_day'],
    ];
    // Nur angeforderte Konten einschließen
    if ($account === 'all' || $account === 'main') {
        $entry['main'] = $row['main_account_return'] !== null ? (float) $row['main_account_return'] : null;
    }
    if ($account === 'all' || $account === 'ea') {
        $entry['ea'] = $row['ea_account_return'] !== null ? (float) $row['ea_account_return'] : null;
    }
    if ($account === 'all' || $account === 'challenge') {
        $entry['challenge'] = $row['challenge_account_return'] !== null ? (float) $row['challenge_account_return'] : null;
    }
    $entries[] = $entry;
}

// ── Kumulative Rendite je Konto berechnen ─────────────────────────────────────
/**
 * Geometrische kumulative Rendite.
 */
function calcCumulative(array $values): ?float
{
    if (empty($values)) return null;
    $factor = 1.0;
    foreach ($values as $v) {
        $factor *= (1 + ($v / 100));
    }
    return round(($factor - 1) * 100, 4);
}

$buckets = ['main' => [], 'ea' => [], 'challenge' => []];
foreach ($rows as $row) {
    if ($row['main_account_return']      !== null) $buckets['main'][]      = (float) $row['main_account_return'];
    if ($row['ea_account_return']        !== null) $buckets['ea'][]        = (float) $row['ea_account_return'];
    if ($row['challenge_account_return'] !== null) $buckets['challenge'][] = (float) $row['challenge_account_return'];
}

$summary = [];
foreach ($buckets as $key => $vals) {
    if ($account !== 'all' && $account !== $key) continue;
    $summary[$key] = [
        'cumulative' => calcCumulative($vals),
        'count'      => count($vals),
    ];
}

// ── PNG-Modus (vorbereitet, noch nicht implementiert) ─────────────────────────
if ($format === 'png') {
    // TODO: Hier GD oder Imagick einbinden
    // Beispiel-Workflow:
    //   1. Template-Bild laden (z.B. assets/grafik_template_1080x1920.png)
    //   2. Schriften laden
    //   3. Werte aus $entries + $summary eintragen
    //   4. header('Content-Type: image/png'); imagepng($img);
    //
    // Für Telegram: Bot-API /sendPhoto mit dem generierten PNG
    // Für Instagram: Meta Graph API /media (Requires long-lived token)

    http_response_code(501);
    echo json_encode([
        'success' => false,
        'message' => 'PNG-Generierung noch nicht implementiert. Kommt in Version 2.',
    ]);
    exit;
}

// ── JSON-Response zusammenbauen ───────────────────────────────────────────────
echo json_encode([
    'success'        => true,
    'account'        => $account,
    'period'         => $period,
    'period_label'   => $periodLabel,
    'from_date'      => $fromDate,
    'to_date'        => $toDate,
    'trading_start'  => $tradingStartDate,
    'entries'        => $entries,
    'summary'        => $summary,
    'generated_at'   => date('c'), // ISO 8601
], JSON_PRETTY_PRINT);