<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ETF-Sparpläne – Automatisches Investieren | Monvesto</title>
  <meta name="description" content="Was ist ein ETF-Sparplan? Welcher ETF ist der richtige? Wie funktioniert der Cost-Average-Effekt? Alles erklärt – mit Tracking in Monvesto." />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://monvesto.de/sparplaene/" />
  <link rel="stylesheet" href="../assets/style.css" />
  <script type="application/ld+json">{"@context":"https://schema.org","@type":"FAQPage","mainEntity":[{"@type":"Question","name":"Was ist ein ETF-Sparplan?","acceptedAnswer":{"@type":"Answer","text":"Automatische, regelmäßige Investition in einen ETF."}}]}</script>
</head>
<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/nav.php'; ?>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/api/widget.php';
$vwce = get_etf('vwce');  // FTSE All-World → VWCE.AS oder VWRL.AS
$msci = get_etf('msci');  // MSCI World → EUNL.DE oder EUNL.AS
$vhyl = get_etf('vhyl');  // High Dividend → VHYL.AS
?>

<section class="hero hero-bg-green">
  <div class="hero-badge">ETF-Sparpläne</div>
  <h1>Monatlich automatisch investieren –<br><span class="highlight">einfacher geht es nicht</span></h1>
  <p class="hero-sub">Was ist ein ETF-Sparplan, welcher ETF ist der richtige und wie trackt Monvesto deinen Fortschritt in Echtzeit?</p>
  <div class="hero-actions">
    <a href="https://app.monvesto.de" class="btn btn-primary btn-lg">Kostenlos starten →</a>
    <a href="#inhalt" class="btn btn-secondary btn-lg">Alles erklärt ↓</a>
  </div>
</section>

<div class="trust-bar"><div class="trust-item">
  <span class='trust-check'>✓</span> Ab 1 € monatlich</div>
  <div class="trust-item">📅 Automatisch & kostenlos</div>
  <div class="trust-item">🌍 MSCI World, FTSE All-World & mehr</div>
  <div class="trust-item">📊 Live-Tracking</div></div>

<section class="section" id="inhalt">
  <div class="section-label">Grundlagen</div>
  <h2 class="section-title">Was ist ein ETF-Sparplan?</h2>
  <p class="section-intro">Ein ETF-Sparplan ist automatisches Investieren in börsengehandelte Fonds. Du legst einmal fest: wie viel, in welchen ETF, wie oft. Dann läuft es von selbst.</p>
  <p class="mt-16" style="font-size:16px;color:var(--text-muted);line-height:1.7;">Keine Marktanalyse, kein manuelles Kaufen, keine emotionalen Entscheidungen. In Abschwüngen kaufst du automatisch günstiger ein – der Cost-Average-Effekt. Über 10, 20 oder 30 Jahre kann daraus ein erhebliches Vermögen entstehen.</p>
</section>
<hr class="divider" />
<section class="section">
  <div class="section-label">ETF-Empfehlungen</div>
  <h2 class="section-title">Die beliebtesten Sparplan-ETFs</h2>
  <div class="grid-3 mt-40">
    <div class="card" style="border:2px solid var(--green);">
      <div class="card-tag">Beliebtester Sparplan-ETF</div>
      <div style="font-size:17px;font-weight:700;margin-bottom:4px;">Vanguard FTSE All-World</div>
      <div style="font-size:17px;font-weight:700;"><?= format_price($vwce) ?> <?= format_change($vwce) ?> € </div>
      <div style="font-family:monospace;font-size:12px;color:var(--text-muted);margin-bottom:12px;">IE00B3RBWM25 / IE00BK5BQT80</div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;margin-bottom:14px;">4.000+ Unternehmen aus 50+ Ländern. Das umfassendste Einzel-ETF-Portfolio.</p>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">TER</span><span style="color:var(--green);font-weight:600;">0,22 % / Jahr</span></div>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Unternehmen</span><span style="font-weight:600;">4.200+</span></div>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Volumen</span><span style="font-weight:600;">+15 Mrd $</span></div>
    </div>
    <div class="card" style="border:2px solid var(--green);">
      <div class="card-tag">Industrieländer-Klassiker</div>
      <div style="font-size:17px;font-weight:700;margin-bottom:4px;">iShares MSCI World</div>
      <div style="font-size:17px;font-weight:700;"><?= format_price($msci) ?> <?= format_change($msci) ?> € </div>
      <div style="font-family:monospace;font-size:12px;color:var(--text-muted);margin-bottom:12px;">IE00B4L5Y983</div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;margin-bottom:14px;">1.500+ Unternehmen aus 23 Industrieländern. Kombinierbar mit EM-ETF (80/20 Portfolio).</p>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">TER</span><span style="color:var(--green);font-weight:600;">0,20 % / Jahr</span></div>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">US-Anteil</span><span style="font-weight:600;">ca. 70 %</span></div>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Volumen</span><span style="font-weight:600;">+50 Mrd $</span></div>
    </div>
    <div class="card" style="border:2px solid var(--green);">
      <div class="card-tag">Dividenden</div>
      <div style="font-size:17px;font-weight:700;margin-bottom:4px;">Vanguard FTSE All-World High Div.</div>
      <div style="font-size:17px;font-weight:700;"><?= format_price($vhyl) ?> <?= format_change($vhyl) ?> € </div>
      <div style="font-family:monospace;font-size:12px;color:var(--text-muted);margin-bottom:12px;">IE00B8GKDB10</div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;margin-bottom:14px;">Fokus auf hohe Dividenden. Quartalsweise Ausschüttungen. Für Einkommensinvestoren.</p>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">TER</span><span style="font-weight:600;">0,29 % / Jahr</span></div>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Dividendenrendite</span><span style="color:var(--green);font-weight:600;">ca. 3–4 % / Jahr</span></div>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Volumen</span><span style="font-weight:600;">+5 Mrd $</span></div>
    </div>
  </div>
</section>
<hr class="divider" />
<section class="section">
  <div class="section-label">Cost-Average-Effekt</div>
  <h2 class="section-title">Warum regelmäßig besser ist als einmalig</h2>
  <div style="background:var(--bg);border:0.5px solid var(--border);border-radius:var(--radius);padding:32px;margin-top:32px;">
    <p style="font-size:15px;color:var(--text-muted);margin-bottom:20px;">Du investierst 100 € pro Monat. Der Kurs schwankt:</p>
    <table class="data-table" style="margin-top:0;">
      <thead><tr><th>Monat</th><th>ETF-Kurs</th><th>Investiert</th><th>Anteile</th></tr></thead>
      <tbody>
        <tr><td>Januar</td><td>50 €</td><td>100 €</td><td>2,00</td></tr>
        <tr><td>Februar</td><td>40 €</td><td>100 €</td><td style="color:var(--green);font-weight:600;">2,50 (günstiger!)</td></tr>
        <tr><td>März</td><td>60 €</td><td>100 €</td><td>1,67</td></tr>
        <tr><td style="font-weight:700;">Gesamt</td><td>Ø 50 €</td><td>300 €</td><td style="font-weight:700;">6,17 @ Ø 48,62 €</td></tr>
      </tbody>
    </table>
    <p style="font-size:14px;color:var(--text-muted);margin-top:16px;">Durch die Schwankungen kaufst du meist zu einem günstigeren Durchschnittspreis als wenn der Kurs immer gleich wäre.</p>
  </div>
</section>


<section class="cta-banner">
  <h2>Sparplan einrichten – Monvesto trackt ihn.</h2>
  <p>Sieh jederzeit wie viel du eingezahlt hast, was er heute wert ist und wie deine Performance aussieht.</p>
  <a href="https://app.monvesto.de" class="btn btn-white btn-lg">Jetzt kostenlos starten →</a>
</section>

<section class="section-sm" style="padding:80px 32px; max-width:760px; margin:0 auto;">
  <div class="section-label">Häufige Fragen</div>
  <h2 class="section-title">Deine Fragen beantwortet</h2>
  <div class="faq-list"><div class="faq-item">
      <div class="faq-q">Was ist ein ETF-Sparplan? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Ein ETF-Sparplan ist eine automatische, regelmäßige Investition in einen ETF. Du legst einmal fest: Betrag, ETF und Intervall. Der Broker kauft dann automatisch.</div>
    </div><div class="faq-item">
      <div class="faq-q">Ab wie viel Euro kann ich starten? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Bei Trade Republic und Scalable Capital ab 1 € pro Monat. Bei ING ab 1 €, Comdirect ab 25 €.</div>
    </div><div class="faq-item">
      <div class="faq-q">Was ist der Cost-Average-Effekt? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Bei regelmäßigen Investitionen kaufst du bei niedrigen Kursen mehr Anteile und bei hohen weniger – im Schnitt ein günstigerer Einstiegspreis als bei einmaligem Kauf.</div>
    </div><div class="faq-item">
      <div class="faq-q">Soll ich thesaurierend oder ausschüttend wählen? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Thesaurierende ETFs reinvestieren Dividenden automatisch – für den Vermögensaufbau oft effizienter. Ausschüttende ETFs zahlen Dividenden aus – ideal für passives Einkommen.</div>
    </div><div class="faq-item">
      <div class="faq-q">Kann ich den Sparplan jederzeit stoppen? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Ja, jederzeit. Keine Mindestlaufzeit, keine Strafe. Deine Anteile bleiben in deinem Depot.</div>
    </div></div>
</section>

<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/includes/footer.php"; ?>
</body>
</html>