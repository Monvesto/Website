<?php
/**
 * Monvesto – Kurs-Widget
 * 
 * Verwendung auf jeder Seite:
 * 
 * Krypto:
 *   <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/api/widget.php';
 *         monvesto_kurs_widget('crypto', 'bitcoin', 'Bitcoin', '₿'); ?>
 * 
 * Aktie/ETF:
 *   <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/api/widget.php';
 *         monvesto_kurs_widget('stock', 'AAPL', 'Apple', '📈'); ?>
 * 
 * Mehrere nebeneinander:
 *   <?php monvesto_kurs_widgets([
 *     ['crypto', 'bitcoin',  'Bitcoin',  '₿'],
 *     ['crypto', 'ethereum', 'Ethereum', 'Ξ'],
 *     ['stock',  'VWCE.DE',  'FTSE All-World', '🌍'],
 *   ]); ?>
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/kurse.php';

function format_price($data) {
    if (!$data || !isset($data['price'])) return '–';
    $price = $data['price'];
    $cur   = $data['currency'] ?? 'EUR';
    // GBp (Pence) → Pfund umrechnen
    if ($cur === 'GBp') {
        $price = $price / 100;
        $cur   = 'GBP';
    }
    switch($cur) {
        case 'EUR': $symbol = '€'; break;
        case 'USD': $symbol = '$'; break;
        case 'GBP': $symbol = '£'; break;
        case 'CHF': $symbol = 'CHF'; break;
        default:    $symbol = $cur;
    }
    return number_format($price, 2, ',', '.') . ' ' . $symbol;
}

function format_change($data) {
    if (!$data || !isset($data['change'])) return '';
    $change = $data['change'];
    $color  = $change >= 0 ? 'var(--green)' : '#EF4444';
    $arrow  = $change >= 0 ? '▲' : '▼';
    $val    = number_format(abs($change), 2, ',', '.') . ' %';
    return '<span style="font-size:11px;color:' . $color . ';">' . $arrow . ' ' . $val . '</span>';
}

function monvesto_kurs_widget($type, $id, $name, $icon = '📈', $show_name = true) {
    if ($type === 'crypto') {
        $data = get_crypto_price($id);
    } else {
        $data = get_stock_price($id);
    }

    if (!$data) {
        echo '<div class="kurs-badge kurs-badge--error">– Kurs nicht verfügbar –</div>';
        return;
    }

    $price    = number_format($data['price'], 2, ',', '.');
    $change   = $data['change'];
    $currency = $data['currency'] === 'EUR' ? '€' : $data['currency'];
    $up       = $change >= 0;
    $arrow    = $up ? '▲' : '▼';
    $color    = $up ? 'var(--green)' : '#EF4444';
    $bg       = $up ? 'var(--green-light)' : '#FEF2F2';
    $change_f = ($up ? '+' : '') . number_format($change, 2, ',', '.') . ' %';

    echo '<div class="kurs-badge">';
    echo '<div class="kurs-badge__icon">' . htmlspecialchars($icon) . '</div>';
    echo '<div class="kurs-badge__info">';
    if ($show_name) {
        echo '<div class="kurs-badge__name">' . htmlspecialchars($name) . '</div>';
    }
    echo '<div class="kurs-badge__price">' . $price . ' ' . $currency . '</div>';
    echo '</div>';
    echo '<div class="kurs-badge__change" style="color:' . $color . ';background:' . $bg . ';">';
    echo $arrow . ' ' . $change_f;
    echo '</div>';
    echo '</div>';
}

function monvesto_kurs_widgets($items) {
    echo '<div class="kurs-badges">';
    foreach ($items as $item) {
        monvesto_kurs_widget(
            $item[0],                      // type: crypto/stock
            $item[1],                      // id
            $item[2],                      // name
            $item[3] ?? '📈',             // icon
            $item[4] ?? true               // show_name
        );
    }
    echo '</div>';
}