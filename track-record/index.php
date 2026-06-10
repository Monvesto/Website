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

<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/nav.php'; ?>

<?php
if (!function_exists('e')) {
    function e($str) {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

$data = require $_SERVER['DOCUMENT_ROOT'] . '/track-record/track-record-daten.php';

$meta    = $data['meta']            ?? [];
$summary = $data['summary']         ?? [];
$months  = $data['monthly_results'] ?? [];
$alloc   = $data['allocation']      ?? [];

function tr_val(array $arr, string $key, string $fallback = '–'): string {
    return isset($arr[$key]) && $arr[$key] !== '' ? $arr[$key] : $fallback;
}

function tr_class(string $val): string {
    if (strpos($val, '−') !== false || strpos($val, '-') !== false) return 'neg';
    if (strpos($val, '+') !== false) return 'pos';
    return '';
}
?>

<!-- HERO -->
<section class="hero hero-bg-green">
  <div class="hero-badge"><?= e(tr_val($meta, 'disclaimer_short', 'Manuell aktualisiert – keine Anlageberatung.')) ?></div>
  <h1>Monvesto <span class="highlight">Track Record</span></h1>
  <p class="hero-sub">Transparente Entwicklung unserer Echtgeld-Portfolios und Trading-Ergebnisse.</p>
  <div class="hero-actions">
    <a href="#performance" class="btn btn-primary btn-lg">Performance ansehen</a>
    <a href="#methodik" class="btn btn-secondary btn-lg">Methodik lesen</a>
  </div>
</section>

<!-- TRUST BAR -->
<div class="trust-bar">
  <div class="trust-item"><span class="trust-check">✓</span> Echtgeld-Portfolio</div>
  <div class="trust-item">🔄 Wöchentlich / monatlich aktualisiert</div>
  <div class="trust-item">📉 Rendite &amp; Drawdown sichtbar</div>
  <div class="trust-item">ℹ️ Keine Anlageberatung</div>
</div>

<!-- PERFORMANCE-ÜBERSICHT -->
<section class="section" id="performance">
  <div class="section-label">Performance</div>
  <h2 class="section-title">Performance-Übersicht</h2>
  <p class="section-intro">
    Startdatum: <strong><?= e(tr_val($meta, 'start_date')) ?></strong>
    &nbsp;·&nbsp;
    Letztes Update: <strong><?= e(tr_val($meta, 'last_update')) ?></strong>
    &nbsp;·&nbsp;
    Währung: <strong><?= e(tr_val($meta, 'currency')) ?></strong>
  </p>

  <div class="tr-kpi-grid mt-40">

    <div class="tr-kpi-card tr-kpi-highlight">
      <span class="tr-kpi-label">Gesamtperformance seit Start</span>
      <span class="tr-kpi-value <?= tr_class(tr_val($summary, 'total_return')) ?>">
        <?= e(tr_val($summary, 'total_return')) ?>
      </span>
    </div>

    <div class="tr-kpi-card">
      <span class="tr-kpi-label">Performance aktueller Monat</span>
      <span class="tr-kpi-value <?= tr_class(tr_val($summary, 'month_return')) ?>">
        <?= e(tr_val($summary, 'month_return')) ?>
      </span>
    </div>

    <div class="tr-kpi-card">
      <span class="tr-kpi-label">Performance aktuelles Jahr (YTD)</span>
      <span class="tr-kpi-value <?= tr_class(tr_val($summary, 'ytd_return')) ?>">
        <?= e(tr_val($summary, 'ytd_return')) ?>
      </span>
    </div>

    <div class="tr-kpi-card">
      <span class="tr-kpi-label">Maximaler Drawdown</span>
      <span class="tr-kpi-value neg"><?= e(tr_val($summary, 'max_drawdown')) ?></span>
    </div>

    <div class="tr-kpi-card">
      <span class="tr-kpi-label">Trefferquote</span>
      <span class="tr-kpi-value"><?= e(tr_val($summary, 'win_rate')) ?></span>
    </div>

    <div class="tr-kpi-card tr-kpi-pair">
      <div>
        <span class="tr-kpi-label">Startwert</span>
        <span class="tr-kpi-value tr-kpi-value--sm"><?= e(tr_val($summary, 'starting_value')) ?></span>
      </div>
      <div>
        <span class="tr-kpi-label">Aktueller Wert</span>
        <span class="tr-kpi-value tr-kpi-value--sm"><?= e(tr_val($summary, 'current_value')) ?></span>
      </div>
    </div>

  </div>
</section>

<hr class="divider" />

<!-- EQUITY CURVE -->
<section class="section" style="background:var(--bg)">
  <div class="section-label">Chart</div>
  <h2 class="section-title">Equity Curve</h2>
  <p class="section-intro">Die Grafik zeigt die Entwicklung des Track Records seit Start.</p>

  <!-- ================================================================
       PLATZHALTER – hier später Chart.js oder Myfxbook einbinden.

       Option A – Chart.js:
         1. <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> im <head> ergänzen
         2. Dieses div durch <canvas id="equityChart"></canvas> ersetzen
         3. JS-Block am Seitenende: new Chart(document.getElementById('equityChart'), {...})
            mit monatlichen Werten aus $data['monthly_results'] als PHP→JS-Array

       Option B – Myfxbook Widget:
         1. myfxbook.com → Widgets → Embed-Code kopieren
         2. Dieses div durch den iframe ersetzen
         3. Empfohlene Maße: width="100%" height="400"
       ================================================================ -->
  <div class="tr-chart-placeholder mt-40">
    <div class="tr-chart-placeholder-inner">
      <span class="tr-chart-icon">📈</span>
      <p class="tr-chart-label">Chart wird hier eingebunden</p>
      <p class="tr-chart-hint">Platzhalter für Chart.js oder Myfxbook-Widget</p>
    </div>
  </div>
</section>

<hr class="divider" />

<!-- MONATLICHE ERGEBNISSE -->
<section class="section">
  <div class="section-label">Verlauf</div>
  <h2 class="section-title">Monatliche Ergebnisse</h2>

  <div class="tr-table-wrap mt-40">
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
        <?php foreach ($months as $row): ?>
        <tr>
          <td><?= e(tr_val($row, 'period')) ?></td>
          <td class="tr-num <?= tr_class(tr_val($row, 'return')) ?>"><?= e(tr_val($row, 'return')) ?></td>
          <td class="tr-num <?= tr_class(tr_val($row, 'pnl')) ?>"><?= e(tr_val($row, 'pnl')) ?></td>
          <td class="tr-num neg"><?= e(tr_val($row, 'drawdown')) ?></td>
          <td class="tr-comment"><?= e(tr_val($row, 'comment', '')) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<hr class="divider" />

<!-- PORTFOLIO-AUFTEILUNG -->
<section class="section" style="background:var(--bg)">
  <div class="section-label">Aufteilung</div>
  <h2 class="section-title">Portfolio- &amp; Strategie-Aufteilung</h2>
  <p class="section-intro">Wie das Kapital aktuell verteilt ist – mit Beschreibung und Status.</p>

  <?php if (!empty($alloc)): ?>
  <div class="tr-alloc-grid mt-40">
    <?php foreach ($alloc as $item): ?>
    <?php
      $statusMap = [
        'aktiv'       => ['label' => 'Aktiv',       'class' => 'badge-green'],
        'aufbau'      => ['label' => 'Im Aufbau',   'class' => 'badge-blue'],
        'beobachtung' => ['label' => 'Beobachtung', 'class' => 'badge-yellow'],
        'pausiert'    => ['label' => 'Pausiert',    'class' => 'badge-yellow'],
      ];
      $statusKey  = strtolower(tr_val($item, 'status', 'aktiv'));
      $statusInfo = $statusMap[$statusKey] ?? ['label' => ucfirst($statusKey), 'class' => 'badge-yellow'];
      $weight     = isset($item['weight']) ? (int)$item['weight'] : 0;
    ?>
    <div class="tr-alloc-card">
      <div class="tr-alloc-header">
        <span class="tr-alloc-name"><?= e(tr_val($item, 'name')) ?></span>
        <span class="badge <?= $statusInfo['class'] ?>"><?= e($statusInfo['label']) ?></span>
      </div>
      <div class="tr-alloc-bar-wrap">
        <div class="tr-alloc-bar" style="width:<?= $weight ?>%"></div>
      </div>
      <div class="tr-alloc-weight"><?= $weight ?> %</div>
      <p class="tr-alloc-desc"><?= e(tr_val($item, 'description', '')) ?></p>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<hr class="divider" />

<!-- METHODIK -->
<section class="section" id="methodik">
  <div class="section-label">Transparenz</div>
  <h2 class="section-title">Methodik</h2>
  <p class="section-intro">Wie der Track Record entsteht und was er aussagt.</p>

  <div class="tr-methodik-grid mt-40">

    <div class="tr-methodik-block">
      <h3>Was wird gemessen?</h3>
      <p>Es wird die Gesamtperformance eines realen Echtgeld-Portfolios erfasst. Das Portfolio besteht aus mehreren Bausteinen: algorithmischem und manuellem Trading, ETF-Sparplänen, Einzelaktien, Kryptowährungen und einer Liquiditätsreserve. Alle Werte beziehen sich auf tatsächlich ausgeführte Positionen.</p>
    </div>

    <div class="tr-methodik-block">
      <h3>Wie oft wird aktualisiert?</h3>
      <p>Der Track Record wird manuell gepflegt – in der Regel wöchentlich oder monatlich, abhängig von der Marktaktivität. Das genaue Datum des letzten Updates ist oben in der Übersicht angegeben. Es gibt keine automatische Echtzeit-Synchronisation.</p>
    </div>

    <div class="tr-methodik-block">
      <h3>Welche Datenquellen werden genutzt?</h3>
      <p>Die Trading-Ergebnisse stammen aus dem MT4-Broker-Account (RoboForex). ETF- und Aktienwerte kommen aus dem jeweiligen Depot. Krypto-Positionen werden über den genutzten Exchange erfasst. Alle Werte werden manuell aus diesen Quellen übernommen.</p>
    </div>

    <div class="tr-methodik-block">
      <h3>Warum können Ergebnisse schwanken?</h3>
      <p>Kapitalmärkte sind volatil. Kurzfristige Verlustphasen (Drawdowns) sind normal und kein Indikator für dauerhaften Misserfolg. Sowohl das Trading als auch Aktien und Krypto unterliegen Marktrisiken, die sich nicht vollständig kontrollieren lassen. Der maximale Drawdown zeigt den bisher größten Rücksetzer.</p>
    </div>

    <div class="tr-methodik-block tr-methodik-block--wide">
      <h3>Vergangene Performance ist keine Garantie</h3>
      <p>Historische Ergebnisse – auch wenn sie positiv sind – erlauben keine verlässliche Prognose für zukünftige Entwicklungen. Jede Strategie kann in veränderten Marktbedingungen schlechter performen. Der Track Record soll Transparenz schaffen, nicht Sicherheit suggerieren.</p>
    </div>

  </div>
</section>

<hr class="divider" />

<!-- RISIKOHINWEIS -->
<div class="notice notice-yellow" style="max-width:800px;margin:40px auto;padding:20px 32px;">
  <span style="font-size:1.2rem;flex-shrink:0;">⚠️</span>
  <p style="margin:0;font-size:14px;line-height:1.75;">Die dargestellten Inhalte dienen ausschließlich der Transparenz und Information. Sie stellen keine Anlageberatung, keine Aufforderung zum Kauf oder Verkauf von Finanzinstrumenten und keine Empfehlung für bestimmte Broker, Strategien oder Produkte dar. Investieren und Trading sind mit Risiken verbunden. Es besteht die Möglichkeit, Teile des eingesetzten Kapitals oder das gesamte Kapital zu verlieren.</p>
</div>

<!-- CTA-BANNER -->
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