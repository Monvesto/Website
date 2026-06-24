<?php
/**
 * trading/generate_image.php – PNG Trading Grafik mit GD
 */

// Bootstrap zuerst laden damit Session/Auth funktioniert
require_once __DIR__ . '/../config/bootstrap.php';

// Dann Output-Buffering starten
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

register_shutdown_function(function () {
    $error  = error_get_last();
    $output = ob_get_clean();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Fatal: ' . $error['message'] . ' (' . basename($error['file']) . ':' . $error['line'] . ')']);
    } elseif (!headers_sent() && empty(trim($output))) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Empty response']);
    } else {
        echo $output;
    }
});

if (!is_admin()) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

$db     = get_db();
$action = $_REQUEST['action'] ?? 'generate';
$type   = in_array($_REQUEST['type'] ?? 'combined', ['combined','main','ea','challenge'])
          ? ($_REQUEST['type'] ?? 'combined') : 'combined';
$format = ($_REQUEST['format'] ?? 'feed') === 'story' ? 'story' : 'feed';

// ── GD-Test ───────────────────────────────────────────────────────────────────
if ($action === 'test') {
    $found = [];
    $search = ['/usr/share/fonts', '/var/www/fonts', '/home'];
    foreach ($search as $dir) {
        if (!is_dir($dir)) continue;
        $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($iter as $file) {
            if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'ttf') {
                $found[] = (string)$file;
            }
        }
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'gd_available' => function_exists('imagecreatetruecolor'),
        'freetype'     => function_exists('imagettftext'),
        'dir_writable' => is_writable(__DIR__ . '/exports/'),
        'fonts_found'  => $found,
        'font_bold'    => $fontBold,
        'font_reg'     => $fontReg,
    ]);
    exit;
}

if (!function_exists('imagecreatetruecolor')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'GD not available.']);
    exit;
}

// ── Schriftpfade: Inter (wenn vorhanden) → Carlito → DejaVu ──────────────────
$fontBold = '/usr/share/fonts/google-droid/DroidSans-Bold.ttf';
$fontReg  = '/usr/share/fonts/google-droid/DroidSans.ttf';

// Fallback falls Droid nicht vorhanden
if (!file_exists($fontBold)) $fontBold = '/usr/share/fonts/liberation-mono/LiberationMono-Bold.ttf';
if (!file_exists($fontReg))  $fontReg  = '/usr/share/fonts/liberation-mono/LiberationMono-Regular.ttf';

// ── Daten laden ───────────────────────────────────────────────────────────────
$entryId = (int)($_REQUEST['entry_id'] ?? 0);
$sql = $entryId > 0
    ? "SELECT * FROM trading_daily_updates WHERE id = ?"
    : "SELECT * FROM trading_daily_updates ORDER BY entry_date DESC LIMIT 1";
$stmt = $db->prepare($sql);
$stmt->execute($entryId > 0 ? [$entryId] : []);
$e = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'No data found.']);
    exit;
}

// ── Kumulative Rendite ────────────────────────────────────────────────────────
function cumRet($db, string $col, string $date): ?float {
    $s = $db->prepare("SELECT $col FROM trading_daily_updates WHERE entry_date<=? AND $col IS NOT NULL ORDER BY entry_date ASC");
    $s->execute([$date]);
    $v = $s->fetchAll(PDO::FETCH_COLUMN);
    if (!$v) return null;
    $f = 1.0;
    foreach ($v as $x) $f *= (1 + (float)$x / 100);
    return round(($f - 1) * 100, 2);
}

function lastBalance($db, string $col, string $date): ?float {
    $s = $db->prepare("SELECT $col FROM trading_daily_updates WHERE entry_date<=? AND $col IS NOT NULL ORDER BY entry_date DESC LIMIT 1");
    $s->execute([$date]);
    $v = $s->fetchColumn();
    return $v !== false ? (float)$v : null;
}

$d = $e['entry_date'];
$data = [
    'main'      => ['label'=>'MAIN ACCOUNT', 'today'=>$e['main_account_return'],      'profit'=>$e['main_account_profit'],      'total'=>cumRet($db,'main_account_return',$d),      'bal'=>null,  'prog'=>null],
    'ea'        => ['label'=>'MONVESTO EA',   'today'=>$e['ea_account_return'],        'profit'=>$e['ea_account_profit'],        'total'=>cumRet($db,'ea_account_return',$d),        'bal'=>null,  'prog'=>null],
    'challenge' => ['label'=>'ROAD TO 100K', 'today'=>$e['challenge_account_return'], 'profit'=>$e['challenge_account_profit'], 'total'=>cumRet($db,'challenge_account_return',$d), 'bal'=>lastBalance($db,'challenge_account_balance',$d), 'prog'=>null],
];
// Progress: wenn Kontostand vorhanden → echte %, sonst cum. Rendite als Schätzung
if ($data['challenge']['bal'] !== null) {
    $data['challenge']['prog'] = min(100, max(0, $data['challenge']['bal'] / 1000));
} elseif ($data['challenge']['total'] !== null) {
    // Fallback: zeige Gesamtrendite als Fortschritt (Start = 0, Ziel = 100%)
    $data['challenge']['prog'] = min(100, max(0, (float)$data['challenge']['total']));
}

// ── Formatierung ──────────────────────────────────────────────────────────────
function pct(?float $v): string {
    if ($v === null) return '--';
    return ($v >= 0 ? '+' : '-') . number_format(abs($v), 2, '.', '') . '%';
}
function eur(?float $v): string {
    if ($v === null) return '--';
    return ($v >= 0 ? '+' : '-') . number_format(abs($v), 2, '.', '') . ' EUR';
}

// ── Bild-Dimensionen ─────────────────────────────────────────────────────────
$W = 1080;
$H = $format === 'story' ? 1920 : 1080;
$img = imagecreatetruecolor($W, $H);
imagealphablending($img, true);

// ── Farben ────────────────────────────────────────────────────────────────────
$WHITE   = imagecolorallocate($img, 255, 255, 255);
$BG      = imagecolorallocate($img, 215, 222, 232);   // fast weiß, leicht grau
$GREEN   = imagecolorallocate($img, 29,  158, 117);
$GREEND  = imagecolorallocate($img, 15,  110,  86);
$GREENL  = imagecolorallocate($img, 209, 240, 229);
$TEXT    = imagecolorallocate($img, 15,  23,  42);    // fast schwarz
$MUTED   = imagecolorallocate($img, 100, 116, 139);
$LIGHT   = imagecolorallocate($img, 148, 163, 184);
$BORDER  = imagecolorallocate($img, 226, 232, 240);
$CARD    = imagecolorallocate($img, 255, 255, 255);
$RED     = imagecolorallocate($img, 220,  38,  38);
$REDL    = imagecolorallocate($img, 254, 226, 226);

// ── Hintergrund ───────────────────────────────────────────────────────────────
imagefilledrectangle($img, 0, 0, $W, $H, $BG);

// ── Hilfsfunktionen ───────────────────────────────────────────────────────────
function tc($img, float $sz, int $cx, int $y, $col, $font, string $txt): void {
    if (!$font) return;
    $txt = mb_convert_encoding($txt, 'UTF-8', 'auto');
    $bb  = imagettfbbox($sz, 0, $font, $txt);
    $tw  = abs($bb[4] - $bb[0]);
    imagettftext($img, $sz, 0, (int)($cx - $tw/2), $y, $col, $font, $txt);
}
function tl($img, float $sz, int $x, int $y, $col, $font, string $txt): void {
    if (!$font) return;
    $txt = mb_convert_encoding($txt, 'UTF-8', 'auto');
    imagettftext($img, $sz, 0, $x, $y, $col, $font, $txt);
}
function rr($img, int $x1, int $y1, int $x2, int $y2, int $r, $fill): void {
    imagefilledrectangle($img, $x1+$r, $y1, $x2-$r, $y2, $fill);
    imagefilledrectangle($img, $x1, $y1+$r, $x2, $y2-$r, $fill);
    imagefilledellipse($img, $x1+$r, $y1+$r, $r*2, $r*2, $fill);
    imagefilledellipse($img, $x2-$r, $y1+$r, $r*2, $r*2, $fill);
    imagefilledellipse($img, $x1+$r, $y2-$r, $r*2, $r*2, $fill);
    imagefilledellipse($img, $x2-$r, $y2-$r, $r*2, $r*2, $fill);
}

// ════════════════════════════════════════════════════════════════════════════
// HEADER
// ════════════════════════════════════════════════════════════════════════════
$HH = 180; // Header-Höhe
imagefilledrectangle($img, 0, 0, $W, $HH, $GREEN);

// Dezente Streifen-Textur im Header
for ($i = 0; $i < $W; $i += 60) {
    $stripe = imagecolorallocatealpha($img, 255, 255, 255, 120);
    imageline($img, $i, 0, $i - $HH, $HH, $stripe);
}

// Monvesto
tc($img, 42, 540, 80, $WHITE, $fontBold, 'Monvesto');

// Trading Update
$TU = imagecolorallocate($img, 180, 230, 210);
tc($img, 15, 540, 105, $TU, $fontBold, 'TRADING UPDATE');

// Datum + Tag – kein Pill, direkt als Text, größer
$dateStr = date('d.m.Y', strtotime($e['entry_date']));
tc($img, 18, 540, 135, $WHITE, $fontBold, $dateStr . '   |   Day ' . $e['trading_day']);

// ════════════════════════════════════════════════════════════════════════════
// KONTO-BLÖCKE (combined)
// ════════════════════════════════════════════════════════════════════════════
$PAD    = 36;
$GAP    = 14;
$CX     = (int)($W / 2);
$LX     = (int)($W / 4);
$RX     = (int)($W * 3 / 4);

if ($type === 'combined') {
    // Höhen berechnen
    $baseH = $format === 'story' ? 420 : 240;
    $progExtra = $format === 'story' ? 90 : 72;

    // Verfügbarer Platz
    $available = $H - $HH - 28 - 60 - ($GAP * 2); // 60 = Footer
    $totalH    = $baseH * 3 + $progExtra + $GAP * 2;
    if ($totalH > $available) {
        $baseH = (int)(($available - $progExtra - $GAP * 2) / 3);
    }

    $y = $HH + 28;
    foreach ($data as $key => $acc) {
        $hasP  = $acc['prog'] !== null;
        $bH    = $baseH + ($hasP ? $progExtra : 0);
        $bx1   = $PAD;
        $bx2   = $W - $PAD;
        $by1   = $y;
        $by2   = $y + $bH;

        // Card-Shadow-Effekt (leichter grauer Rand)
        rr($img, $bx1+3, $by1+3, $bx2+3, $by2+3, 16, $BORDER);
        // Card
        rr($img, $bx1, $by1, $bx2, $by2, 16, $CARD);

        // Farbiger linker Rand-Akzent
        $accColor = ($acc['today'] !== null && (float)$acc['today'] < 0) ? $RED : $GREEN;
        imagefilledrectangle($img, $bx1, $by1+16, $bx1+5, $by2-16, $accColor);

        // Konto-Label
        tc($img, 13, $CX, $by1 + 42, $MUTED, $fontBold, $acc['label']);

        // Trennlinie horizontal unter Label
        imageline($img, $bx1+40, $by1+54, $bx2-40, $by1+54, $BORDER);

        // Vertikale Trennlinie
        $divY1 = $by1 + 62;
        $divY2 = $hasP ? $by2 - $progExtra : $by2 - 20;
        imageline($img, $CX, $divY1, $CX, $divY2, $BORDER);

        // ── TODAY (links) ────────────────────────────────────────────────────
        $todayC = ($acc['today'] === null || (float)$acc['today'] >= 0) ? $GREEN : $RED;

        tc($img, 12, $LX, $by1 + 88, $LIGHT, $fontBold, 'TODAY');

        $pctSize = $format === 'story' ? 52 : 44;
        tc($img, $pctSize, $LX, $by1 + 88 + $pctSize + 20, $todayC, $fontBold, pct($acc['today'] !== null ? (float)$acc['today'] : null));

        // Gewinn
        $profitY = $by1 + 88 + $pctSize + 46;
        $profitC = ($acc['profit'] !== null && (float)$acc['profit'] < 0) ? $RED : $MUTED;
        tc($img, 15, $LX, $profitY, $profitC, $fontReg, eur($acc['profit'] !== null ? (float)$acc['profit'] : null));

        // ── TOTAL (rechts) ───────────────────────────────────────────────────
        $totalC = ($acc['total'] === null || $acc['total'] >= 0) ? $GREEN : $RED;

        tc($img, 12, $RX, $by1 + 88, $LIGHT, $fontBold, 'TOTAL');
        tc($img, $pctSize, $RX, $by1 + 88 + $pctSize + 20, $totalC, $fontBold, pct($acc['total']));
        tc($img, 15, $RX, $profitY, $LIGHT, $fontReg, 'since start');

        // ── PROGRESS BAR (Challenge) ─────────────────────────────────────────
        if ($hasP) {
            $bPad = 60;
            $bY   = $by2 - $progExtra + 16;
            $bHH  = 18;
            $bX1  = $bx1 + $bPad;
            $bX2  = $bx2 - $bPad;
            $bW   = $bX2 - $bX1;

            imageline($img, $bx1+40, $by2-$progExtra+6, $bx2-40, $by2-$progExtra+6, $BORDER);

            // Bar Hintergrund
            rr($img, $bX1, $bY, $bX2, $bY+$bHH, 9, $BORDER);

            // Bar Fortschritt
            $fw = max(18, (int)($bW * $acc['prog'] / 100));
            rr($img, $bX1, $bY, $bX1+$fw, $bY+$bHH, 9, $GREEN);

            // Progress Text – nur EUR-Wert
            $pText = $acc['bal'] !== null
                ? number_format($acc['bal'], 0, '.', ',') . ' EUR / 100,000 EUR'
                : '-- EUR / 100,000 EUR';
            tc($img, 12, $CX, $bY + $bHH + 24, $MUTED, $fontReg, $pText);
        }

        $y += $bH + $GAP;
    }

} else {
    // ── EINZELKONTO ──────────────────────────────────────────────────────────
    $acc = $data[$type];

    // Große Card
    $bx1 = $PAD; $bx2 = $W - $PAD;
    $by1 = $HH + 40; $by2 = $H - 100;

    rr($img, $bx1+3, $by1+3, $bx2+3, $by2+3, 20, $BORDER);
    rr($img, $bx1, $by1, $bx2, $by2, 20, $CARD);

    // Farbiger Akzent oben
    $accC = ($acc['today'] !== null && (float)$acc['today'] < 0) ? $RED : $GREEN;
    imagefilledrectangle($img, $bx1, $by1, $bx2, $by1+6, $accC);

    // Label
    tc($img, 18, $CX, $by1+60, $MUTED, $fontBold, $acc['label']);
    imageline($img, $bx1+60, $by1+76, $bx2-60, $by1+76, $BORDER);

    // Vertikale Trennlinie
    imageline($img, $CX, $by1+90, $CX, $by2-80, $BORDER);

    // TODAY
    $tC = ($acc['today'] === null || (float)$acc['today'] >= 0) ? $GREEN : $RED;
    tc($img, 16, $LX, $by1+120, $LIGHT, $fontBold, 'TODAY');
    tc($img, 80, $LX, $by1+230, $tC, $fontBold, pct($acc['today'] !== null ? (float)$acc['today'] : null));
    tc($img, 20, $LX, $by1+262, $MUTED, $fontReg, eur($acc['profit'] !== null ? (float)$acc['profit'] : null));

    // TOTAL
    $totC = ($acc['total'] === null || $acc['total'] >= 0) ? $GREEN : $RED;
    tc($img, 16, $RX, $by1+120, $LIGHT, $fontBold, 'TOTAL');
    tc($img, 80, $RX, $by1+230, $totC, $fontBold, pct($acc['total']));
    tc($img, 20, $RX, $by1+262, $LIGHT, $fontReg, 'since start');

    // Progress
    if ($acc['prog'] !== null) {
        $bY  = $by2 - 120;
        $bX1 = $bx1+80; $bX2 = $bx2-80; $bW = $bX2-$bX1;
        imageline($img, $bx1+60, $bY-20, $bx2-60, $bY-20, $BORDER);
        rr($img, $bX1, $bY, $bX2, $bY+28, 14, $BORDER);
        $fw = max(28, (int)($bW * $acc['prog'] / 100));
        rr($img, $bX1, $bY, $bX1+$fw, $bY+28, 14, $GREEN);
        $pt = $acc['bal'] !== null
            ? number_format($acc['bal'],0,'.',',') . ' / 100,000 EUR  —  ' . number_format($acc['prog'],1) . '%'
            : '-- / 100,000 EUR';
        tc($img, 15, $CX, $bY+54, $MUTED, $fontReg, $pt);
    }
}

// ── Footer ────────────────────────────────────────────────────────────────────
imagefilledrectangle($img, 0, $H-40, $W, $H, $GREEN);
tc($img, 14, 540, $H-16, $WHITE, $fontReg, 'monvesto.de');

// ── Speichern ─────────────────────────────────────────────────────────────────
$exportsDir = __DIR__ . '/exports/';
if (!is_dir($exportsDir)) mkdir($exportsDir, 0755, true);
$filename = $e['entry_date'] . '_' . $type . '_' . $format . '.png';
$filepath = $exportsDir . $filename;

ob_end_clean();
imagepng($img, $filepath, 6);
imagedestroy($img);

if ($action === 'download') {
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath); exit;
}

$fileUrl = str_replace(rtrim($_SERVER['DOCUMENT_ROOT'],'/'), '', $filepath);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success'=>true, 'file'=>$filename, 'url'=>$fileUrl, 'message'=>'Created: '.$filename]);