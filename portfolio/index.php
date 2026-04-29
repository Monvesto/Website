<?php
$title    = "Aktien & ETFs – Portfolio aufbauen & tracken | Monvesto";
$meta     = "Was sind Aktien und ETFs? Strategien, Broker-Vergleich und Live-Tracking mit Monvesto.";
$canonical = "https://monvesto.de/portfolio/";
$schema   = '{"@context":"https://schema.org","@type":"FAQPage","mainEntity":[{"@type":"Question","name":"Was ist eine Aktie?","acceptedAnswer":{"@type":"Answer","text":"Ein Anteil an einem Unternehmen."}}]}';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php';
?>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/nav.php'; ?>


<section class="hero hero-bg-blue">
  <div class="hero-badge">Portfolio & Investments</div>
  <h1>Aktien & ETFs –<br><span class="highlight">Vermögen aufbauen leicht gemacht</span></h1>
  <p class="hero-sub">Was sind Aktien und ETFs? Strategien, Broker-Vergleich und Live-Tracking mit Monvesto.</p>
  <div class="hero-actions">
    <a href="https://app.monvesto.de" class="btn btn-primary btn-lg">Kostenlos starten →</a>
    <a href="#inhalt" class="btn btn-secondary btn-lg">Alles erklärt ↓</a>
  </div>
</section>

<div class="trust-bar"><div class="trust-item">📈 Live-Kurse via Yahoo Finance</div><div class="trust-item">🌍 Alle Börsen weltweit</div><div class="trust-item">💰 Dividenden tracken</div><div class="trust-item">📊 Gewinn/Verlust in Echtzeit</div></div>


<section class="section" id="inhalt">
  <div class="section-label">Grundlagen</div>
  <h2 class="section-title">Warum investieren statt nur sparen?</h2>
  <p class="section-intro">Auf einem Sparkonto verliert Geld durch Inflation real an Wert. Der MSCI World hat über 30 Jahre durchschnittlich 7 % p.a. erzielt.</p>
  <p class="mt-16" style="font-size:16px;color:var(--text-muted);line-height:1.7;">200 € monatlich über 30 Jahre bei 7 % Rendite ergeben über 240.000 € – bei nur 72.000 € Eigenleistung. Der wichtigste Faktor: früh anfangen und dabeibleiben.</p>
</section>

<hr class="divider" />

<section class="section" id="inhalt">
  <div class="section-label">Anlageklassen</div>
  <h2 class="section-title">Aktien, ETFs – was ist was?</h2>
  <p class="section-intro">Drei verschiedene Wege um an der Börse zu investieren – mit sehr unterschiedlichem Aufwand, Risiko und Renditechance.</p>

  <div class="grid-3 mt-40" style="margin-bottom:32px;">
    <div class="card" style="background:#F0FDF4;border-color:#86EFAC;">
      <div style="font-size:13px;font-weight:700;color:var(--green);margin-bottom:8px;">FÜR WEN?</div>
      <div style="font-size:16px;font-weight:700;margin-bottom:8px;">ETFs – für jeden</div>
      <p class="text-muted" style="font-size:14px;line-height:1.65;">Ein ETF kauft automatisch viele Aktien auf einmal – du brauchst kein Fachwissen. Einmal einrichten, regelmäßig besparen, Rendite mitnehmen. Das ist die meistempfohlene Methode für Privatanleger.</p>
    </div>
    <div class="card" style="background:#EFF6FF;border-color:#BFDBFE;">
      <div style="font-size:13px;font-weight:700;color:#2563EB;margin-bottom:8px;">FÜR WEN?</div>
      <div style="font-size:16px;font-weight:700;margin-bottom:8px;">Einzelaktien – für Erfahrene</div>
      <p class="text-muted" style="font-size:14px;line-height:1.65;">Du kaufst gezielt Anteile an einzelnen Unternehmen – Apple, SAP, Tesla. Höhere Chancen, aber auch höheres Risiko. Erfordert Zeit für Recherche und ein gutes Verständnis der Unternehmen.</p>
    </div>
    <div class="card" style="background:#FFFBEB;border-color:#FDE68A;">
      <div style="font-size:13px;font-weight:700;color:#92400E;margin-bottom:8px;">FÜR WEN?</div>
      <div style="font-size:16px;font-weight:700;margin-bottom:8px;">Dividenden-Aktien – für Einkommensinvestoren</div>
      <p class="text-muted" style="font-size:14px;line-height:1.65;">Unternehmen wie BASF oder Allianz zahlen regelmäßig einen Teil ihres Gewinns als Dividende aus. Ideal für alle die passives Einkommen aufbauen wollen – ähnlich wie Mietzahlungen aus Immobilien.</p>
    </div>
  </div>
  <!-- Die drei Karten mit Details folgen hier -->
  <div class="grid-3 mt-40">
    <div class="card" style="border:2px solid #2563EB;position:relative;">
      <div style="position:absolute;top:-12px;left:20px;background:#2563EB;color:white;font-size:11px;font-weight:700;padding:4px 12px;border-radius:100px;">Empfehlung für Einsteiger</div>
      <div class="card-icon" style="background:#EFF6FF;">🌍</div>
      <div style="font-size:18px;font-weight:700;margin-bottom:8px;">ETFs</div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;margin-bottom:14px;">Bildet einen Index nach, enthält hunderte bis tausende Unternehmen. Günstig, diversifiziert, passiv.</p>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">TER</span><span style="color:var(--green);font-weight:600;">0,07–0,50 % / Jahr</span></div>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Risiko</span><span style="color:#F59E0B;font-weight:600;">Mittel (gestreut)</span></div>
    </div>
    <div class="card">
      <div class="card-icon" style="background:#EFF6FF;">📊</div>
      <div style="font-size:18px;font-weight:700;margin-bottom:8px;">Einzelaktien</div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;margin-bottom:14px;">Anteile an einzelnen Unternehmen. Für erfahrene Anleger mit Zeit für Research.</p>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Orderkosten</span><span style="font-weight:600;">1–12,90 €</span></div>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Risiko</span><span style="color:#EF4444;font-weight:600;">Hoch (Einzelrisiko)</span></div>
    </div>
    <div class="card">
      <div class="card-icon" style="background:#EFF6FF;">💵</div>
      <div style="font-size:18px;font-weight:700;margin-bottom:8px;">Dividenden-Aktien</div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;margin-bottom:14px;">Regelmäßige Ausschüttungen zusätzlich zu Kursgewinnen. Ideal für passives Einkommen.</p>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Dividendenrendite</span><span style="color:var(--green);font-weight:600;">2–7 % / Jahr</span></div>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Steuer</span><span style="font-weight:600;">25 % Abgeltungssteuer</span></div>
    </div>
  </div>
</section>
<hr class="divider" />
<section class="section">
  <div class="section-label">Broker-Vergleich</div>
  <h2 class="section-title">Welcher Broker ist der richtige?</h2>
  <div style="overflow-x:auto;margin-top:32px;">
    <table class="data-table">
      <thead><tr><th>Broker</th><th>Depotgebühr</th><th>Order-Kosten</th><th>Sparplan ab</th></tr></thead>
      <tbody>
        <tr><td><strong>Trade Republic</strong></td><td class="check">0 €</td><td>1 €</td><td>1 €</td></tr>
        <tr><td><strong>Scalable Capital</strong></td><td>0 € / 4,99 €</td><td>0,99 € / kostenlos</td><td>1 €</td></tr>
        <tr><td><strong>ING</strong></td><td class="check">0 €</td><td>4,90 € + 0,25 %</td><td>1 €</td></tr>
        <tr><td><strong>Comdirect</strong></td><td class="cross">12,90 €/Quartal</td><td>12,90 € + 0,25 %</td><td>25 €</td></tr>
      </tbody>
    </table>
  </div>
</section>

<section class="cta-banner">
  <h2>Dein Portfolio. Live. Mit Monvesto.</h2>
  <p>Tracke Aktien und ETFs bei jedem Broker in Echtzeit.</p>
  <a href="https://app.monvesto.de" class="btn btn-white btn-lg">Jetzt kostenlos starten →</a>
</section>

<section class="section-sm" style="padding:80px 32px;max-width:760px;margin:0 auto;">
  <div class="section-label">Häufige Fragen</div>
  <h2 class="section-title">Deine Fragen beantwortet</h2>
  <div class="faq-list">
    <div class="faq-item">
      <div class="faq-q">Was ist eine Aktie? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Ein Anteil an einem Unternehmen. Du verdienst durch Kurssteigerungen und Dividenden.</div>
    </div>    <div class="faq-item">
      <div class="faq-q">Was ist der Unterschied zwischen ETF und aktivem Fonds? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">ETF bildet einen Index passiv nach – sehr günstig. Aktiver Fonds hat einen Manager – bei 1,5–2,5 % Kosten, schlägt langfristig selten den Markt.</div>
    </div>    <div class="faq-item">
      <div class="faq-q">Muss ich Aktiengewinne versteuern? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Ja. Abgeltungssteuer 26,375 %. Davon ausgenommen: Sparerpauschbetrag 1.000 € pro Jahr.</div>
    </div>    <div class="faq-item">
      <div class="faq-q">Wie tracke ich mein Portfolio in Monvesto? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Ticker, Kaufpreis und Anzahl eintragen. Monvesto ruft automatisch den aktuellen Kurs ab.</div>
    </div>
  </div>
</section>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>