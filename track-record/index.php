<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Track Record – Transparente Performance-Übersicht | Monvesto</title>
  <meta name="description" content="Monvesto Track Record: Transparente Entwicklung unserer Echtgeld-Portfolios und Trading-Ergebnisse. Rendite, Drawdown und monatliche Ergebnisse im Überblick." />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://monvesto.de/track-record/" />
  <link rel="stylesheet" href="../assets/style.css" />
  <link rel="stylesheet" href="../assets/vergleich-components.css" />
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "Monvesto Track Record",
    "description": "Transparente Entwicklung der Monvesto Echtgeld-Portfolios und Trading-Ergebnisse.",
    "url": "https://monvesto.de/track-record/",
    "publisher": {
      "@type": "Organization",
      "name": "Monvesto",
      "url": "https://monvesto.de"
    }
  }
  </script>
</head>
<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/nav.php'; ?>

<?php
// ---------------------------------------------------------------
// Hilfsfunktion – falls noch nicht global definiert
// ---------------------------------------------------------------
if (!function_exists('e')) {
    function e($str) {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

// ---------------------------------------------------------------
// Daten laden
// ---------------------------------------------------------------
$data = require $_SERVER['DOCUMENT_ROOT'] . '/anbieter/track-record-daten.php';

$meta    = $data['meta']            ?? [];
$summary = $data['summary']         ?? [];
$months  = $data['monthly_results'] ?? [];
$alloc   = $data['allocation']      ?? [];

// Hilfsfunktion für sichere Array-Werte
function tr_val(array $arr, string $key, string $fallback = '–'): string {
    return isset($arr[$key]) && $arr[$key] !== '' ? $arr[$key] : $fallback;
}

// Positive/negative Klasse für Rendite-Werte
function tr_class(string $val): string {
    if (strpos($val, '−') !== false || strpos($val, '-') !== false) return 'tr-neg';
    if (strpos($val, '+') !== false) return 'tr-pos';
    return '';
}
?>

<!-- ================================================================
     HERO
     ================================================================ -->
<section class="hero">
  <div class="container">
    <div class="hero-content">
      <p class="hero-badge"><?= e(tr_val($meta, 'disclaimer_short', 'Manuell aktualisiert – keine Anlageberatung.')) ?></p>
      <h1>Monvesto Track Record</h1>
      <p class="hero-sub">Transparente Entwicklung unserer Echtgeld-Portfolios und Trading-Ergebnisse.</p>
      <div class="hero-ctas">
        <a href="#performance" class="btn btn-primary">Performance ansehen</a>
        <a href="#methodik" class="btn btn-outline">Methodik lesen</a>
      </div>
    </div>
  </div>
</section>

<!-- ================================================================
     TRUST BAR
     ================================================================ -->
<section class="tr-trust-bar">
  <div class="container">
    <div class="tr-trust-items">
      <div class="tr-trust-item">
        <span class="tr-trust-icon">📊</span>
        <span>Echtgeld-Portfolio</span>
      </div>
      <div class="tr-trust-item">
        <span class="tr-trust-icon">🔄</span>
        <span>Wöchentlich / monatlich aktualisiert</span>
      </div>
      <div class="tr-trust-item">
        <span class="tr-trust-icon">📉</span>
        <span>Rendite &amp; Drawdown sichtbar</span>
      </div>
      <div class="tr-trust-item">
        <span class="tr-trust-icon">ℹ️</span>
        <span>Keine Anlageberatung</span>
      </div>
    </div>
  </div>
</section>

<!-- ================================================================
     PERFORMANCE-ÜBERSICHT
     ================================================================ -->
<section class="section" id="performance">
  <div class="container">
    <div class="tr-section-header">
      <h2>Performance-Übersicht</h2>
      <p class="tr-meta-line">
        Startdatum: <strong><?= e(tr_val($meta, 'start_date')) ?></strong>
        &nbsp;·&nbsp;
        Letztes Update: <strong><?= e(tr_val($meta, 'last_update')) ?></strong>
        &nbsp;·&nbsp;
        Währung: <strong><?= e(tr_val($meta, 'currency')) ?></strong>
      </p>
    </div>

    <div class="tr-kpi-grid">

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
        <span class="tr-kpi-value tr-neg">
          <?= e(tr_val($summary, 'max_drawdown')) ?>
        </span>
      </div>

      <div class="tr-kpi-card">
        <span class="tr-kpi-label">Trefferquote</span>
        <span class="tr-kpi-value">
          <?= e(tr_val($summary, 'win_rate')) ?>
        </span>
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
  </div>
</section>

<!-- ================================================================
     EQUITY CURVE (Platzhalter)
     ================================================================ -->
<section class="section tr-section-alt">
  <div class="container">
    <div class="tr-section-header">
      <h2>Equity Curve</h2>
      <p>Die Grafik zeigt die Entwicklung des Track Records seit Start.</p>
    </div>

    <!-- ============================================================
         PLATZHALTER – hier später Chart.js oder Myfxbook einbinden.

         Option A – Chart.js:
           1. Script-Tag hinzufügen:
              <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
           2. Dieses div durch ein <canvas id="equityChart"> ersetzen.
           3. Chart.js-Initialisierung mit monatlichen Werten aus
              $data['monthly_results'] als JS-Array übergeben.

         Option B – Myfxbook Widget:
           1. Auf myfxbook.com unter "Widgets" den Embed-Code kopieren.
           2. Dieses div durch den Myfxbook-iframe-Code ersetzen.
           3. Widget-Dimensionen: width="100%" height="400"
         ============================================================ -->
    <div class="tr-chart-placeholder">
      <div class="tr-chart-placeholder-inner">
        <span class="tr-chart-icon">📈</span>
        <p class="tr-chart-label">Chart wird hier eingebunden</p>
        <p class="tr-chart-hint">Platzhalter für Chart.js oder Myfxbook-Widget</p>
      </div>
    </div>

  </div>
</section>

<!-- ================================================================
     PERFORMANCE-TABELLE (monatliche Ergebnisse)
     ================================================================ -->
<section class="section">
  <div class="container">
    <div class="tr-section-header">
      <h2>Monatliche Ergebnisse</h2>
    </div>

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
          <tr>
            <td colspan="5" class="tr-empty">Noch keine Daten vorhanden.</td>
          </tr>
          <?php else: ?>
          <?php foreach ($months as $row): ?>
          <tr>
            <td><?= e(tr_val($row, 'period')) ?></td>
            <td class="tr-num <?= tr_class(tr_val($row, 'return')) ?>"><?= e(tr_val($row, 'return')) ?></td>
            <td class="tr-num <?= tr_class(tr_val($row, 'pnl')) ?>"><?= e(tr_val($row, 'pnl')) ?></td>
            <td class="tr-num tr-neg"><?= e(tr_val($row, 'drawdown')) ?></td>
            <td class="tr-comment"><?= e(tr_val($row, 'comment', '')) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</section>

<!-- ================================================================
     PORTFOLIO- / STRATEGIE-AUFTEILUNG
     ================================================================ -->
<section class="section tr-section-alt">
  <div class="container">
    <div class="tr-section-header">
      <h2>Portfolio- &amp; Strategie-Aufteilung</h2>
      <p>Wie das Kapital aktuell verteilt ist – mit Beschreibung und Status.</p>
    </div>

    <?php if (!empty($alloc)): ?>
    <div class="tr-alloc-grid">
      <?php foreach ($alloc as $item): ?>
      <?php
        $statusMap = [
          'aktiv'        => ['label' => 'Aktiv',        'class' => 'tr-status-aktiv'],
          'aufbau'       => ['label' => 'Im Aufbau',    'class' => 'tr-status-aufbau'],
          'beobachtung'  => ['label' => 'Beobachtung',  'class' => 'tr-status-neutral'],
          'pausiert'     => ['label' => 'Pausiert',      'class' => 'tr-status-neutral'],
        ];
        $statusKey = strtolower(tr_val($item, 'status', 'aktiv'));
        $statusInfo = $statusMap[$statusKey] ?? ['label' => ucfirst($statusKey), 'class' => 'tr-status-neutral'];
        $weight = isset($item['weight']) ? (int)$item['weight'] : 0;
      ?>
      <div class="tr-alloc-card">
        <div class="tr-alloc-header">
          <span class="tr-alloc-name"><?= e(tr_val($item, 'name')) ?></span>
          <span class="tr-alloc-badge <?= $statusInfo['class'] ?>"><?= e($statusInfo['label']) ?></span>
        </div>
        <div class="tr-alloc-bar-wrap">
          <div class="tr-alloc-bar" style="width: <?= $weight ?>%"></div>
        </div>
        <div class="tr-alloc-weight"><?= $weight ?> %</div>
        <p class="tr-alloc-desc"><?= e(tr_val($item, 'description', '')) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</section>

<!-- ================================================================
     METHODIK
     ================================================================ -->
<section class="section" id="methodik">
  <div class="container">
    <div class="tr-section-header">
      <h2>Methodik</h2>
      <p>Wie der Track Record entsteht und was er aussagt.</p>
    </div>

    <div class="tr-methodik-grid">

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
  </div>
</section>

<!-- ================================================================
     RISIKOHINWEIS
     ================================================================ -->
<section class="tr-disclaimer-section">
  <div class="container">
    <div class="tr-disclaimer-box">
      <span class="tr-disclaimer-icon">⚠️</span>
      <p>Die dargestellten Inhalte dienen ausschließlich der Transparenz und Information. Sie stellen keine Anlageberatung, keine Aufforderung zum Kauf oder Verkauf von Finanzinstrumenten und keine Empfehlung für bestimmte Broker, Strategien oder Produkte dar. Investieren und Trading sind mit Risiken verbunden. Es besteht die Möglichkeit, Teile des eingesetzten Kapitals oder das gesamte Kapital zu verlieren.</p>
    </div>
  </div>
</section>

<!-- ================================================================
     CTA-BANNER
     ================================================================ -->
<section class="cta-banner">
  <div class="container">
    <div class="cta-content">
      <h2>Alle Portfolios. Ein Überblick. Mit Monvesto.</h2>
      <p>Monvesto hilft dir, Konten, Depots und Vermögen zentral im Blick zu behalten.</p>
      <!-- CTA-Button wird aktiviert, wenn App live ist:
      <a href="https://app.monvesto.de" class="btn btn-primary btn-lg">Kostenlos starten</a>
      -->
    </div>
  </div>
</section>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>

</body>
</html>