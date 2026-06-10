<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Track Record – Transparente Performance-Übersicht | Monvesto</title>
  <meta name="description" content="Monvesto Track Record: Transparente Entwicklung unserer Echtgeld-Portfolios und Trading-Ergebnisse. Rendite, Drawdown und monatliche Ergebnisse im Überblick." />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://monvesto.de/track-record/" />
  <link rel="stylesheet" href="/assets/style.css" />
  <script type="application/ld+json">{"@context":"https://schema.org","@type":"WebPage","name":"Monvesto Track Record","description":"Transparente Entwicklung der Monvesto Echtgeld-Portfolios und Trading-Ergebnisse.","url":"https://monvesto.de/track-record/"}</script>
</head>
<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/nav.php'; ?>

<?php
// ════════════════════════════════════════════════════════════════
// HILFSFUNKTIONEN
// ════════════════════════════════════════════════════════════════
if (!function_exists('e')) {
    function e($str) {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

function fmt_pct(float $val, int $dec = 1): string {
    return ($val >= 0 ? '+' : '') . number_format($val, $dec, ',', '.') . ' %';
}

function fmt_eur(float $val): string {
    return ($val >= 0 ? '+' : '−') . number_format(abs($val), 2, ',', '.') . ' €';
}

function pct_class(float $val): string {
    return $val >= 0 ? 'pos' : 'neg';
}

// ════════════════════════════════════════════════════════════════
// BERECHNUNGEN AUS monthly_results
// ════════════════════════════════════════════════════════════════

/**
 * Aktueller Wert: Startwert + Summe aller pnl-Einträge
 */
function calc_current_value(float $start, array $months): float {
    $val = $start;
    foreach ($months as $m) {
        $val += (float)($m['pnl'] ?? 0);
    }
    return $val;
}

/**
 * Ø Monatsrendite: Durchschnitt aller return_pct
 */
function calc_avg_month(array $months): ?float {
    if (empty($months)) return null;
    $sum = 0.0;
    foreach ($months as $m) $sum += (float)($m['return_pct'] ?? 0);
    return $sum / count($months);
}

/**
 * Ø Jahresrendite: Pro Kalenderjahr Ø berechnen, dann Mittelwert der Jahre.
 * Jahreszahl wird aus dem period-String extrahiert (letztes Wort).
 */
function calc_avg_yearly(array $months): ?float {
    if (empty($months)) return null;
    $by_year = [];
    foreach ($months as $m) {
        $parts = explode(' ', trim($m['period'] ?? ''));
        $year  = end($parts);
        if (!$year) continue;
        $by_year[$year][] = (float)($m['return_pct'] ?? 0);
    }
    if (empty($by_year)) return null;
    $yearly_avgs = [];
    foreach ($by_year as $months_in_year) {
        $yearly_avgs[] = array_sum($months_in_year) / count($months_in_year);
    }
    return array_sum($yearly_avgs) / count($yearly_avgs);
}

/**
 * Max Drawdown: kleinster (negativster) drawdown_pct-Wert
 */
function calc_max_drawdown(array $months): ?float {
    if (empty($months)) return null;
    $min = 0.0;
    foreach ($months as $m) {
        $dd = (float)($m['drawdown_pct'] ?? 0);
        if ($dd < $min) $min = $dd;
    }
    return $min;
}

/**
 * Positive Monate in % (für ETF/Aktien/Krypto statt Win Rate)
 * Gibt ['positive' => int, 'total' => int, 'pct' => float] zurück
 */
function calc_positive_months(array $months): ?array {
    if (empty($months)) return null;
    $pos = 0;
    foreach ($months as $m) {
        if ((float)($m['return_pct'] ?? 0) > 0) $pos++;
    }
    $total = count($months);
    return [
        'positive' => $pos,
        'total'    => $total,
        'pct'      => $total > 0 ? ($pos / $total) * 100 : 0,
    ];
}

// ════════════════════════════════════════════════════════════════
// DATEN LADEN & PRO ASSET BERECHNEN
// ════════════════════════════════════════════════════════════════
$assets_raw = require __DIR__ . '/track-record-daten.php';
$asset_keys = ['trading', 'aktien', 'etf', 'krypto'];

// Trading-Assets haben Win Rate, alle anderen Positive Monate
$trading_assets = ['trading'];

$assets = [];
foreach ($asset_keys as $key) {
    $raw    = $assets_raw[$key] ?? [];
    $months = $raw['monthly_results'] ?? [];
    $start  = (float)($raw['meta']['starting_value'] ?? 0);

    $assets[$key] = $raw;
    $assets[$key]['_calc'] = [
        'current_value'    => calc_current_value($start, $months),
        'avg_month'        => calc_avg_month($months),
        'avg_yearly'       => calc_avg_yearly($months),
        'max_drawdown'     => calc_max_drawdown($months),
        'positive_months'  => in_array($key, $trading_assets) ? null : calc_positive_months($months),
        'win_rate'         => $raw['summary']['win_rate'] ?? null, // manuell gepflegt
    ];
}

// ── Gesamtstatistik ──────────────────────────────────────────────
$total_start   = 0.0;
$total_current = 0.0;
foreach ($asset_keys as $key) {
    $total_start   += (float)($assets[$key]['meta']['starting_value'] ?? 0);
    $total_current += $assets[$key]['_calc']['current_value'];
}

$total_pnl        = $total_current - $total_start;
$total_return_pct = $total_start > 0 ? (($total_current / $total_start) - 1) * 100 : 0.0;

// Gewichtete Ø Jahresrendite
$ytd_weighted = 0.0;
foreach ($asset_keys as $key) {
    $weight = $total_start > 0 ? (float)($assets[$key]['meta']['starting_value'] ?? 0) / $total_start : 0;
    $yr     = $assets[$key]['_calc']['avg_yearly'];
    $ytd_weighted += $weight * (float)($yr ?? 0);
}

// Schlechtester Drawdown gesamt
$worst_dd = 0.0;
foreach ($asset_keys as $key) {
    $dd = $assets[$key]['_calc']['max_drawdown'] ?? 0.0;
    if ($dd !== null && $dd < $worst_dd) $worst_dd = $dd;
}

// Letztes Update
$last_update = '';
foreach ($asset_keys as $key) {
    $d = $assets[$key]['meta']['last_update'] ?? '';
    if ($d > $last_update) $last_update = $d;
}

$bar_colors = [
    'trading' => 'var(--green)',
    'aktien'  => '#3B82F6',
    'etf'     => '#8B5CF6',
    'krypto'  => '#F59E0B',
];
?>

<!-- ════════════════════════════════════════════════════════════════
     HERO
     ════════════════════════════════════════════════════════════════ -->
<section class="hero hero-bg-green">
  <div class="hero-badge">Manuell aktualisiert – keine Anlageberatung</div>
  <h1>Monvesto <span class="highlight">Track Record</span></h1>
  <p class="hero-sub">Transparente Entwicklung unserer Echtgeld-Portfolios und Trading-Ergebnisse.</p>
  <div class="hero-actions">
    <a href="#gesamt" class="btn btn-primary btn-lg">Gesamtübersicht</a>
    <a href="#methodik" class="btn btn-secondary btn-lg">Methodik lesen</a>
  </div>
</section>

<div class="trust-bar">
  <div class="trust-item"><span class="trust-check">✓</span> Echtgeld-Portfolio</div>
  <div class="trust-item">🔄 Wöchentlich / monatlich aktualisiert</div>
  <div class="trust-item">📉 Rendite &amp; Drawdown sichtbar</div>
  <div class="trust-item">ℹ️ Keine Anlageberatung</div>
</div>

<!-- ════════════════════════════════════════════════════════════════
     GESAMTSTATISTIK
     ════════════════════════════════════════════════════════════════ -->
<section class="section" id="gesamt">
  <div class="section-label">Gesamt</div>
  <h2 class="section-title">Gesamtübersicht</h2>
  <p class="section-intro">
    Alle Assets zusammengefasst – automatisch aus den Einzelwerten berechnet.
    Letztes Update: <strong><?= e($last_update) ?></strong>
  </p>

  <div class="tr-kpi-grid mt-40">

    <div class="tr-kpi-card tr-kpi-highlight">
      <span class="tr-kpi-label">Gesamtperformance seit Start</span>
      <span class="tr-kpi-value <?= pct_class($total_return_pct) ?>"><?= fmt_pct($total_return_pct) ?></span>
    </div>

    <div class="tr-kpi-card">
      <span class="tr-kpi-label">Ø Jahresrendite (gewichtet)</span>
      <span class="tr-kpi-value <?= pct_class($ytd_weighted) ?>"><?= fmt_pct($ytd_weighted) ?></span>
    </div>

    <div class="tr-kpi-card">
      <span class="tr-kpi-label">Gesamter Gewinn / Verlust</span>
      <span class="tr-kpi-value <?= pct_class($total_pnl) ?>"><?= fmt_eur($total_pnl) ?></span>
    </div>

    <div class="tr-kpi-card">
      <span class="tr-kpi-label">Max. Drawdown (schlechtester Asset)</span>
      <span class="tr-kpi-value neg"><?= $worst_dd !== 0.0 ? fmt_pct($worst_dd) : '–' ?></span>
    </div>

    <div class="tr-kpi-card tr-kpi-pair">
      <div>
        <span class="tr-kpi-label">Startkapital gesamt</span>
        <span class="tr-kpi-value tr-kpi-value--sm"><?= number_format($total_start, 0, ',', '.') ?> €</span>
      </div>
      <div>
        <span class="tr-kpi-label">Aktueller Wert gesamt</span>
        <span class="tr-kpi-value tr-kpi-value--sm"><?= number_format($total_current, 0, ',', '.') ?> €</span>
      </div>
    </div>

  </div>

  <!-- Kapitalaufteilung Balken -->
  <h3 style="font-size:16px;font-weight:700;margin:48px 0 20px;">Kapitalaufteilung nach Asset</h3>
  <div class="tr-alloc-bars">
    <?php foreach ($asset_keys as $key):
      $a     = $assets[$key];
      $cv    = $a['_calc']['current_value'];
      $sv    = (float)($a['meta']['starting_value'] ?? 0);
      $pnl_a = $cv - $sv;
      $ret_a = $sv > 0 ? (($cv / $sv) - 1) * 100 : 0.0;
      $pct_of_total = $total_current > 0 ? ($cv / $total_current) * 100 : 0;
      $color = $bar_colors[$key] ?? 'var(--green)';
    ?>
    <div class="tr-bar-row">
      <div class="tr-bar-label">
        <span class="tr-bar-icon"><?= e($a['meta']['icon'] ?? '') ?></span>
        <span class="tr-bar-name"><?= e($a['meta']['name'] ?? $key) ?></span>
        <span class="tr-bar-pct-label"><?= number_format($pct_of_total, 1, ',', '.') ?> %</span>
      </div>
      <div class="tr-bar-track">
        <div class="tr-bar-fill" style="width:<?= number_format($pct_of_total, 2, '.', '') ?>%;background:<?= $color ?>"></div>
      </div>
      <div class="tr-bar-stats">
        <span><?= number_format($cv, 0, ',', '.') ?> €</span>
        <span class="<?= pct_class($ret_a) ?>"><?= fmt_pct($ret_a) ?></span>
        <span class="<?= pct_class($pnl_a) ?>"><?= fmt_eur($pnl_a) ?></span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="tr-legend">
    <?php foreach ($asset_keys as $key): ?>
    <span class="tr-legend-item">
      <span class="tr-legend-dot" style="background:<?= $bar_colors[$key] ?? 'var(--green)' ?>"></span>
      <?= e($assets[$key]['meta']['name'] ?? $key) ?>
    </span>
    <?php endforeach; ?>
  </div>

</section>

<hr class="divider" />

<!-- ════════════════════════════════════════════════════════════════
     EINZELNE ASSETS
     ════════════════════════════════════════════════════════════════ -->
<?php foreach ($asset_keys as $i => $key):
  $a      = $assets[$key];
  $meta_a = $a['meta']            ?? [];
  $months = $a['monthly_results'] ?? [];
  $calc   = $a['_calc'];
  $name   = $meta_a['name']       ?? ucfirst($key);
  $icon   = $meta_a['icon']       ?? '';
  $sv     = (float)($meta_a['starting_value'] ?? 0);
  $cv     = $calc['current_value'];
  $pnl_a  = $cv - $sv;
  $ret_a  = $sv > 0 ? (($cv / $sv) - 1) * 100 : 0.0;
  $bg     = ($i % 2 !== 0) ? 'style="background:var(--bg)"' : '';
?>

<section class="section" id="<?= e($key) ?>" <?= $bg ?>>
  <div class="section-label"><?= e($icon) ?> <?= e($name) ?></div>
  <h2 class="section-title"><?= e($name) ?></h2>
  <p class="section-intro">
    Startkapital: <strong><?= number_format($sv, 0, ',', '.') ?> €</strong>
    &nbsp;·&nbsp;
    Aktuell: <strong><?= number_format($cv, 0, ',', '.') ?> €</strong>
    &nbsp;·&nbsp;
    Start: <strong><?= e($meta_a['start_date'] ?? '–') ?></strong>
  </p>

  <div class="tr-kpi-grid mt-40">

    <div class="tr-kpi-card tr-kpi-highlight">
      <span class="tr-kpi-label">Performance seit Start</span>
      <span class="tr-kpi-value <?= pct_class($ret_a) ?>"><?= fmt_pct($ret_a) ?></span>
    </div>

    <div class="tr-kpi-card">
      <span class="tr-kpi-label">Gewinn / Verlust gesamt</span>
      <span class="tr-kpi-value <?= pct_class($pnl_a) ?>"><?= fmt_eur($pnl_a) ?></span>
    </div>

    <div class="tr-kpi-card">
      <span class="tr-kpi-label">Ø Monatsrendite</span>
      <?php $am = $calc['avg_month']; ?>
      <span class="tr-kpi-value <?= $am !== null ? pct_class($am) : '' ?>">
        <?= $am !== null ? fmt_pct($am) : '–' ?>
      </span>
    </div>

    <div class="tr-kpi-card">
      <span class="tr-kpi-label">Ø Jahresrendite</span>
      <?php $ay = $calc['avg_yearly']; ?>
      <span class="tr-kpi-value <?= $ay !== null ? pct_class($ay) : '' ?>">
        <?= $ay !== null ? fmt_pct($ay) : '–' ?>
      </span>
    </div>

    <div class="tr-kpi-card">
      <span class="tr-kpi-label">Max. Drawdown</span>
      <?php $dd = $calc['max_drawdown']; ?>
      <span class="tr-kpi-value <?= $dd !== null && $dd < 0 ? 'neg' : '' ?>">
        <?= $dd !== null && $dd !== 0.0 ? fmt_pct($dd) : '–' ?>
      </span>
    </div>

    <?php if (in_array($key, $trading_assets)): ?>
    <!-- Win Rate (manuell, nur Trading) -->
    <div class="tr-kpi-card">
      <span class="tr-kpi-label">Win Rate</span>
      <?php $wr = $calc['win_rate']; ?>
      <span class="tr-kpi-value"><?= $wr !== null ? number_format((float)$wr, 0, ',', '.') . ' %' : '–' ?></span>
    </div>

    <?php else: ?>
    <!-- Positive Monate (automatisch, ETF / Aktien / Krypto) -->
    <div class="tr-kpi-card">
      <span class="tr-kpi-label">Positive Monate</span>
      <?php $pm = $calc['positive_months']; ?>
      <span class="tr-kpi-value"><?= $pm !== null ? $pm['positive'] . ' / ' . $pm['total'] : '–' ?></span>
      <?php if ($pm !== null): ?>
      <span class="tr-kpi-sub <?= pct_class($pm['pct'] - 50) ?>"><?= fmt_pct($pm['pct'], 0) ?></span>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </div>

  <!-- Equity Curve Platzhalter -->
  <!--
    PLATZHALTER – <?= e($name) ?> Equity Curve
    Option A – Chart.js: <canvas id="chart_<?= e($key) ?>"></canvas> + JS am Seitenende
    Option B – Myfxbook: iframe-Embed hier einfügen (width="100%" height="400")
  -->
  <div class="tr-chart-placeholder" style="margin-top:40px;">
    <div class="tr-chart-placeholder-inner">
      <span class="tr-chart-icon"><?= e($icon) ?></span>
      <p class="tr-chart-label"><?= e($name) ?> – Equity Curve</p>
      <p class="tr-chart-hint">Platzhalter für Chart.js oder Myfxbook-Widget</p>
    </div>
  </div>

  <!-- Monatliche Ergebnisse -->
  <h3 style="font-size:16px;font-weight:700;margin:40px 0 16px;">Monatliche Ergebnisse</h3>
  <div class="tr-table-wrap">
    <table class="tr-table">
      <thead>
        <tr>
          <th>Zeitraum</th>
          <th class="tr-num">Rendite</th>
          <th class="tr-num">Gewinn&nbsp;/&nbsp;Verlust</th>
          <th class="tr-num">Drawdown</th>
          <th class="tr-comment">Kommentar</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($months)): ?>
        <tr><td colspan="5" class="tr-empty">Noch keine Daten vorhanden.</td></tr>
        <?php else: ?>
        <?php foreach ($months as $row):
          $rp = (float)($row['return_pct']   ?? 0);
          $pl = (float)($row['pnl']          ?? 0);
          $dp = (float)($row['drawdown_pct'] ?? 0);
        ?>
        <tr>
          <td><?= e($row['period'] ?? '–') ?></td>
          <td class="tr-num <?= pct_class($rp) ?>"><?= fmt_pct($rp) ?></td>
          <td class="tr-num <?= pct_class($pl) ?>"><?= fmt_eur($pl) ?></td>
          <td class="tr-num <?= $dp < 0 ? 'neg' : '' ?>"><?= $dp !== 0.0 ? fmt_pct($dp) : '–' ?></td>
          <td class="tr-comment"><?= e($row['comment'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</section>

<hr class="divider" />

<?php endforeach; ?>

<!-- ════════════════════════════════════════════════════════════════
     METHODIK
     ════════════════════════════════════════════════════════════════ -->
<section class="section" id="methodik">
  <div class="section-label">Transparenz</div>
  <h2 class="section-title">Methodik</h2>
  <p class="section-intro">Wie der Track Record entsteht und was er aussagt.</p>

  <div class="tr-methodik-grid mt-40">

    <div class="tr-methodik-block">
      <h3>Was wird gemessen?</h3>
      <p>Es wird die Gesamtperformance eines realen Echtgeld-Portfolios erfasst. Das Portfolio besteht aus vier Bausteinen: algorithmischem und manuellem Trading, ETF-Sparplänen, Einzelaktien und Kryptowährungen. Alle Werte beziehen sich auf tatsächlich ausgeführte Positionen.</p>
    </div>

    <div class="tr-methodik-block">
      <h3>Wie werden Renditen berechnet?</h3>
      <p>Die Ø Monatsrendite ist der arithmetische Mittelwert aller monatlichen Renditen. Die Ø Jahresrendite wird pro Kalenderjahr berechnet und dann über alle Jahre gemittelt. Der aktuelle Wert ergibt sich aus dem Startkapital plus der Summe aller monatlichen Gewinne und Verluste.</p>
    </div>

    <div class="tr-methodik-block">
      <h3>Was bedeutet „Positive Monate"?</h3>
      <p>Bei ETFs, Aktien und Krypto wird statt einer Win Rate der Anteil positiver Monate angezeigt – also wie viele der bisher erfassten Monate mit einem Gewinn abgeschlossen haben. Je höher dieser Wert, desto konstanter die Entwicklung.</p>
    </div>

    <div class="tr-methodik-block">
      <h3>Welche Datenquellen werden genutzt?</h3>
      <p>Trading-Ergebnisse stammen aus dem MT4-Broker-Account (RoboForex). ETF- und Aktienwerte kommen aus dem jeweiligen Depot. Krypto-Positionen werden über den genutzten Exchange erfasst. Alle Werte werden manuell übernommen.</p>
    </div>

    <div class="tr-methodik-block tr-methodik-block--wide">
      <h3>Vergangene Performance ist keine Garantie</h3>
      <p>Historische Ergebnisse erlauben keine verlässliche Prognose für zukünftige Entwicklungen. Jede Strategie kann in veränderten Marktbedingungen schlechter performen. Der Track Record soll Transparenz schaffen, nicht Sicherheit suggerieren.</p>
    </div>

  </div>
</section>

<div class="notice notice-yellow" style="max-width:800px;margin:0 auto 40px;padding:20px 32px;">
  <span style="font-size:1.2rem;flex-shrink:0;">⚠️</span>
  <p style="margin:0;font-size:14px;line-height:1.75;">Die dargestellten Inhalte dienen ausschließlich der Transparenz und Information. Sie stellen keine Anlageberatung, keine Aufforderung zum Kauf oder Verkauf von Finanzinstrumenten und keine Empfehlung für bestimmte Broker, Strategien oder Produkte dar. Investieren und Trading sind mit Risiken verbunden.</p>
</div>

<section class="cta-banner">
  <h2>Alle Portfolios. Ein Überblick. Mit Monvesto.</h2>
  <p>Monvesto hilft dir, Konten, Depots und Vermögen zentral im Blick zu behalten.</p>
  <!--
  <a href="https://app.monvesto.de" class="btn btn-white btn-lg">Kostenlos starten →</a>
  -->
</section>

<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/includes/footer.php"; ?>
</body>
</html>