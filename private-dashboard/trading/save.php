<?php
/**
 * trading/save.php – Speichern / Aktualisieren
 * ==============================================
 * Antwortet immer als JSON.
 *
 * POST-Parameter:
 *   entry_date                  Y-m-d
 *   main_return / ea_return / challenge_return        % (Dezimal, optional)
 *   main_profit / ea_profit / challenge_profit        € Gewinn (optional)
 *   main_balance / ea_balance / challenge_balance     € Kontostand (optional)
 *   main_open / ea_open / challenge_open              JSON offene Positionen (optional)
 *   edit_id                     int  (>0 = Update)
 *   force_update                '1'  = Update ohne Rückfrage
 */

require_once __DIR__ . '/../config/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Zugriff verweigert.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage.']);
    exit;
}

$db = get_db();

// ── Eingaben einlesen ─────────────────────────────────────────────────────────
$rawDate     = trim($_POST['entry_date']   ?? '');
$editId      = (int) ($_POST['edit_id']    ?? 0);
$forceUpdate = ($_POST['force_update']     ?? '0') === '1';

// ── Validierung: Datum ────────────────────────────────────────────────────────
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate)) {
    echo json_encode(['success' => false, 'message' => 'Ungültiges Datumsformat.']);
    exit;
}
$dateObj = DateTime::createFromFormat('Y-m-d', $rawDate);
if (!$dateObj || $dateObj->format('Y-m-d') !== $rawDate) {
    echo json_encode(['success' => false, 'message' => 'Datum existiert nicht.']);
    exit;
}
if ($rawDate > date('Y-m-d')) {
    echo json_encode(['success' => false, 'message' => 'Datum darf nicht in der Zukunft liegen.']);
    exit;
}

// ── Dezimalwert parsen ────────────────────────────────────────────────────────
function parseDecimal(string $val, float $min = -100000, float $max = 100000)
{
    if ($val === '') return null;
    $val = str_replace(',', '.', $val);
    if (!is_numeric($val)) return false;
    $f = (float) $val;
    if ($f < $min || $f > $max) return false;
    return $f;
}

// Rendite %
$mainReturn      = parseDecimal(trim($_POST['main_return']      ?? ''), -100, 10000);
$eaReturn        = parseDecimal(trim($_POST['ea_return']        ?? ''), -100, 10000);
$challengeReturn = parseDecimal(trim($_POST['challenge_return'] ?? ''), -100, 10000);

// Gewinn €
$mainProfit      = parseDecimal(trim($_POST['main_profit']      ?? ''));
$eaProfit        = parseDecimal(trim($_POST['ea_profit']        ?? ''));
$challengeProfit = parseDecimal(trim($_POST['challenge_profit'] ?? ''));

// Kontostand €
$mainBalance      = parseDecimal(trim($_POST['main_balance']      ?? ''), 0, 10000000);
$eaBalance        = parseDecimal(trim($_POST['ea_balance']        ?? ''), 0, 10000000);
$challengeBalance = parseDecimal(trim($_POST['challenge_balance'] ?? ''), 0, 10000000);

// Offene Positionen JSON
$mainOpen      = trim($_POST['main_open']      ?? '') ?: null;
$eaOpen        = trim($_POST['ea_open']        ?? '') ?: null;
$challengeOpen = trim($_POST['challenge_open'] ?? '') ?: null;

// Validierung
$checks = [
    'Main Account Rendite'  => $mainReturn,
    'EA Rendite'            => $eaReturn,
    'Challenge Rendite'     => $challengeReturn,
    'Main Gewinn'           => $mainProfit,
    'EA Gewinn'             => $eaProfit,
    'Challenge Gewinn'      => $challengeProfit,
];
foreach ($checks as $label => $val) {
    if ($val === false) {
        echo json_encode(['success' => false, 'message' => $label . ' enthält einen ungültigen Wert.']);
        exit;
    }
}

if ($mainReturn === null && $eaReturn === null && $challengeReturn === null &&
    $mainProfit === null && $eaProfit === null && $challengeProfit === null) {
    echo json_encode(['success' => false, 'message' => 'Bitte mindestens einen Wert eingeben.']);
    exit;
}

// ── Handelstag berechnen ──────────────────────────────────────────────────────
$start      = new DateTime(getTradingStartDate());
$entry      = new DateTime($rawDate);
$diff       = (int) $start->diff($entry)->format('%r%a');
$tradingDay = max(1, $diff + 1);

// ── Existiert Datum bereits? ──────────────────────────────────────────────────
$stmtCheck = $db->prepare("SELECT id FROM trading_daily_updates WHERE entry_date = ?");
$stmtCheck->execute([$rawDate]);
$existingId = $stmtCheck->fetchColumn();

if ($existingId && !$forceUpdate && !$editId) {
    echo json_encode([
        'exists'  => true,
        'id'      => (int) $existingId,
        'message' => 'Für dieses Datum existiert bereits ein Eintrag.',
    ]);
    exit;
}

// ── INSERT oder UPDATE ────────────────────────────────────────────────────────
try {
    $fields = [
        'entry_date'                => $rawDate,
        'trading_day'               => $tradingDay,
        'main_account_return'       => $mainReturn,
        'ea_account_return'         => $eaReturn,
        'challenge_account_return'  => $challengeReturn,
        'main_account_profit'       => $mainProfit,
        'ea_account_profit'         => $eaProfit,
        'challenge_account_profit'  => $challengeProfit,
        'main_account_balance'      => $mainBalance,
        'ea_account_balance'        => $eaBalance,
        'challenge_account_balance' => $challengeBalance,
        'main_open_positions'       => $mainOpen,
        'ea_open_positions'         => $eaOpen,
        'challenge_open_positions'  => $challengeOpen,
    ];

    if ($existingId || $editId) {
        $targetId = $editId ?: (int) $existingId;
        $sets = implode(', ', array_map(function($k) { return "`$k` = ?"; }, array_keys($fields)));
        $stmt = $db->prepare("UPDATE trading_daily_updates SET $sets WHERE id = ?");
        $values = array_values($fields);
        $values[] = $targetId;
        $stmt->execute($values);
        echo json_encode(['success' => true, 'message' => 'Eintrag für ' . date('d.m.Y', strtotime($rawDate)) . ' aktualisiert.', 'action' => 'update']);
    } else {
        $cols         = implode(', ', array_map(function($k) { return "`$k`"; }, array_keys($fields)));
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $stmt = $db->prepare("INSERT INTO trading_daily_updates ($cols) VALUES ($placeholders)");
        $stmt->execute(array_values($fields));
        echo json_encode(['success' => true, 'message' => 'Eintrag für ' . date('d.m.Y', strtotime($rawDate)) . ' gespeichert.', 'action' => 'insert', 'id' => (int) $db->lastInsertId()]);
    }
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        echo json_encode(['success' => false, 'message' => 'Duplikat: Datum bereits vorhanden.']);
    } else {
        error_log('[trading/save.php] ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Datenbankfehler.']);
    }
}