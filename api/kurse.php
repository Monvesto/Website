<?php
/**
 * Monvesto – Zentrale Kurs-API
 * Holt Live-Kurse von CoinGecko (Krypto) und Yahoo Finance (Aktien/ETFs)
 * Cache: 5 Minuten
 */

// Nur JSON-Header senden wenn direkt aufgerufen, nicht wenn included
if (basename($_SERVER['SCRIPT_FILENAME']) === 'kurse.php') {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
}

// ── CACHE EINSTELLUNGEN ──────────────────────────
$cache_dir  = sys_get_temp_dir() . '/monvesto_cache/';
$cache_time = 300; // 5 Minuten

if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0755, true);
}

function get_cache($key, $cache_dir, $cache_time) {
    $file = $cache_dir . md5($key) . '.json';
    if (file_exists($file) && (time() - filemtime($file)) < $cache_time) {
        return json_decode(file_get_contents($file), true);
    }
    return null;
}

function set_cache($key, $data, $cache_dir) {
    $file = $cache_dir . md5($key) . '.json';
    file_put_contents($file, json_encode($data));
}

function fetch_url($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Monvesto/1.0)');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $result = curl_exec($ch);
    $error  = curl_error($ch);
    curl_close($ch);
    if ($error) return null;
    return $result;
}

// ── KRYPTO via CoinGecko ─────────────────────────
function get_crypto_price($coin_id) {
    global $cache_dir, $cache_time;
    $cache_key = 'crypto_' . $coin_id;
    $cached = get_cache($cache_key, $cache_dir, $cache_time);
    if ($cached) return $cached;

    $url  = "https://api.coingecko.com/api/v3/simple/price?ids={$coin_id}&vs_currencies=eur&include_24hr_change=true&include_market_cap=false";
    $raw  = fetch_url($url);
    if (!$raw) return null;

    $data = json_decode($raw, true);
    if (!isset($data[$coin_id])) return null;

    $result = [
        'id'       => $coin_id,
        'price'    => $data[$coin_id]['eur'],
        'change'   => round($data[$coin_id]['eur_24h_change'], 2),
        'currency' => 'EUR',
        'source'   => 'CoinGecko',
        'updated'  => date('H:i'),
    ];

    set_cache($cache_key, $result, $cache_dir);
    return $result;
}

// ── AKTIE/ETF via Yahoo Finance ──────────────────
function get_stock_price($ticker) {
    global $cache_dir, $cache_time;
    $cache_key = 'stock_' . $ticker;
    $cached = get_cache($cache_key, $cache_dir, $cache_time);
    if ($cached) return $cached;

    $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$ticker}?interval=1d&range=2d";
    $raw = fetch_url($url);
    if (!$raw) return null;

    $data = json_decode($raw, true);
    if (!isset($data['chart']['result'][0])) return null;

    $chart    = $data['chart']['result'][0];
    $meta     = $chart['meta'];
    $price    = $meta['regularMarketPrice'];
    $prev     = $meta['chartPreviousClose'];
    $change   = round((($price - $prev) / $prev) * 100, 2);
    $currency = $meta['currency'] ?? 'USD';

    $result = [
        'id'       => $ticker,
        'price'    => round($price, 2),
        'change'   => $change,
        'currency' => $currency,
        'name'     => $meta['shortName'] ?? $ticker,
        'source'   => 'Yahoo Finance',
        'updated'  => date('H:i'),
    ];

    set_cache($cache_key, $result, $cache_dir);
    return $result;
}

// ── ETF/AKTIE MIT FALLBACK ───────────────────────
// Versucht mehrere Börsenplätze der Reihe nach
function get_etf_price($tickers) {
    foreach ($tickers as $ticker) {
        $data = get_stock_price($ticker);
        if ($data && isset($data['price']) && $data['price'] > 0) {
            return $data;
        }
    }
    return null;
}

// Vordefinierte ETFs mit Fallback-Tickern
function get_etf($name) {
    $etfs = [
        'vwce'  => ['VWCE.AS', 'VWCE.L',  'VWCE.DE'],   // FTSE All-World
        'msci'  => ['EUNL.DE', 'EUNL.AS', 'IWDA.AS'],   // MSCI World
        'vhyl'  => ['VHYL.AS', 'VHYL.L',  'VHYL.DE'],   // All-World High Div
        'sp500' => ['CSPX.L',  'SXR8.DE', 'IUSA.AS'],   // S&P 500
        'em'    => ['EIMI.DE', 'EIMI.AS', 'EMIM.AS'],    // Emerging Markets
        'ism'   => ['IS3N.DE', 'IS3N.AS'],                // MSCI World SRI
    ];
    if (!isset($etfs[$name])) return null;
    return get_etf_price($etfs[$name]);
}


if (basename($_SERVER['SCRIPT_FILENAME']) !== 'kurse.php') return;

$type = $_GET['type']   ?? '';   // 'crypto' oder 'stock'
$id   = $_GET['id']     ?? '';   // z.B. 'bitcoin' oder 'AAPL'
$ids  = $_GET['ids']    ?? '';   // mehrere, kommagetrennt z.B. 'bitcoin,ethereum'

if (!$type || (!$id && !$ids)) {
    echo json_encode(['error' => 'Bitte type und id angeben. Beispiel: ?type=crypto&id=bitcoin']);
    exit;
}

// Mehrere auf einmal
if ($ids) {
    $id_list = explode(',', $ids);
    $results = [];
    foreach ($id_list as $single_id) {
        $single_id = trim($single_id);
        if ($type === 'crypto') {
            $results[$single_id] = get_crypto_price($single_id);
        } else {
            $results[$single_id] = get_stock_price($single_id);
        }
    }
    echo json_encode($results);
    exit;
}

// Einzeln
if ($type === 'crypto') {
    $result = get_crypto_price($id);
} else {
    $result = get_stock_price($id);
}

if (!$result) {
    echo json_encode(['error' => 'Kurs nicht gefunden für: ' . $id]);
    exit;
}

echo json_encode($result);