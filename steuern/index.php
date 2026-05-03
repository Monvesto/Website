<?php
$title    = "Steuerübersicht – Kapitalerträge & Sparerpauschbetrag | Monvesto";
$meta     = "Abgeltungssteuer, Sparerpauschbetrag, Krypto-Steuer: Monvesto fasst alles automatisch zusammen.";
$canonical = "https://monvesto.de/steuern/";
$schema   = "";
require_once $_SERVER["DOCUMENT_ROOT"] . "/includes/head.php";
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/includes/nav.php"; ?>

<section class="hero hero-bg-green">
  <div class="hero-badge">Steuerübersicht</div>
  <h1>Investmentsteuern<br><span class="highlight">einfach erklärt</span></h1>
  <p class="hero-sub">Abgeltungssteuer, Sparerpauschbetrag, Krypto-Steuer – einfach zusammengefasst.</p>
  <!--
  <div class="hero-actions"><a href="https://app.monvesto.de" class="btn btn-primary btn-lg">Steuerübersicht öffnen →</a></div>
-->
</section>
<section class="section">
  <div class="section-label">Steuerarten</div>
  <h2 class="section-title">Was wird wie besteuert?</h2>
  <div class="grid-3 mt-40">
    <div class="card" style="border:2px solid var(--green);">
      <div style="font-size:28px;margin-bottom:12px;">📊</div>
      <div style="font-size:17px;font-weight:700;margin-bottom:8px;">Abgeltungssteuer</div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;margin-bottom:14px;">Auf Dividenden, Zinsen und Kursgewinne. Automatisch von der Bank abgeführt.</p>
      <div style="background:#FEF2F2;border-radius:8px;padding:10px 14px;font-size:14px;font-weight:700;color:#991B1B;">25 % + Soli = 26,375 %</div>
    </div>
    <div class="card" style="border:2px solid var(--green);">
      <div style="font-size:28px;margin-bottom:12px;">🎁</div>
      <div style="font-size:17px;font-weight:700;margin-bottom:8px;">Sparerpauschbetrag</div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;margin-bottom:14px;">Jährlicher Freibetrag. Bis hier sind alle Kapitalerträge steuerfrei. Freistellungsauftrag stellen!</p>
      <div style="background:var(--green-light);border-radius:8px;padding:10px 14px;font-size:14px;font-weight:700;color:var(--green-dark);">1.000 € Singles / 2.000 € Paare</div>
    </div>
    <ddiv class="card" style="border:2px solid var(--green);">
      <div style="font-size:28px;margin-bottom:12px;">₿</div>
      <div style="font-size:17px;font-weight:700;margin-bottom:8px;">Krypto-Steuer</div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;margin-bottom:14px;">Privates Veräußerungsgeschäft. Nach 1 Jahr Haltedauer: vollständig steuerfrei!</p>
      <div style="background:#FFFBEB;border-radius:8px;padding:10px 14px;font-size:14px;font-weight:700;color:#92400E;">Persönlicher Steuersatz</div>
    </div>
  </div>
</section>

<hr class="divider" />

<section class="section" id="inhalt">
  <div class="section-label">Grundlagen</div>
  <h2 class="section-title">Welche Steuern fallen bei Investments an?</h2>
  <p class="section-intro">Je nach Anlageklasse werden Erträge unterschiedlich besteuert.
  Dividenden, Zinsen und realisierte Kursgewinne aus Aktien oder ETFs
  fallen meist unter die Abgeltungssteuer.</p>
  <p class="mt-16" style="font-size:16px;color:var(--text-muted);line-height:1.7;">Kryptowährungen gelten steuerlich dagegen als private Veräußerungsgeschäfte.
  Hier ist vor allem die Haltedauer entscheidend: Nach einem Jahr können Gewinne
  steuerfrei sein.</p>
</section>

<hr class="divider" />

<section class="section">
  <div class="section-label">Schnellvergleich</div>
  <h2 class="section-title">Was ist steuerpflichtig?</h2>
  <div style="overflow-x:auto;margin-top:32px;">
    <table class="data-table">
      <thead><tr><th>Anlageklasse</th><th>Steuersatz</th><th>Freibetrag</th><th>Besonderheit</th></tr></thead>
      <tbody>
        <tr><td><strong>Aktien-Dividenden</strong></td><td>26,375 %</td><td class="check">1.000 €</td><td>Freistellungsauftrag</td></tr>
        <tr><td><strong>ETF-Gewinne</strong></td><td>26,375 %</td><td class="check">1.000 €</td><td>Teilfreistellung 30 %</td></tr>
        <tr><td><strong>Tagesgeld-Zinsen</strong></td><td>26,375 %</td><td class="check">1.000 €</td><td>Freistellungsauftrag</td></tr>
        <tr><td><strong>Krypto &lt; 1 Jahr</strong></td><td>Persönl. ESt.</td><td class="check">1.000 € Freigrenze</td><td>Privates Veräußerungsgeschäft</td></tr>
        <tr><td><strong>Krypto &gt; 1 Jahr</strong></td><td class="check fw-700">0 % – Steuerfrei!</td><td>–</td><td>Jahresfrist einhalten!</td></tr>
        <tr><td><strong>P2P-Zinsen</strong></td><td>26,375 %</td><td class="check">1.000 €</td><td>Selbst in Steuererklärung angeben</td></tr>
      </tbody>
    </table>
  </div>
</section>

<section class="cta-banner" style="margin-top:0;">
  <h2>Deine Steuern. Automatisch zusammengefasst.</h2>
  <p>Kapitalerträge, Sparerpauschbetrag und Krypto-Haltefristen auf einen Blick.</p>
  <a href="https://app.monvesto.de" class="btn btn-white btn-lg">Steuerübersicht öffnen →</a>
</section>

<div class="notice notice-yellow" style="max-width:760px;margin:32px auto;">⚠️ Alle Angaben ohne Gewähr. Für individuelle Steuerberatung bitte einen Steuerberater kontaktieren.</div>

<section class="section-sm" style="padding:80px 32px;max-width:760px;margin:0 auto;">
  <div class="section-label">Häufige Fragen</div>
  <h2 class="section-title">Steuern auf Investments – deine Fragen</h2>
  <div class="faq-list">
    <div class="faq-item open">
      <div class="faq-q">Was ist die Abgeltungssteuer? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Die Abgeltungssteuer beträgt 25 % auf Kapitalerträge wie Dividenden, Zinsen und Kursgewinne – plus Solidaritätszuschlag, insgesamt 26,375 %. Sie wird automatisch von deiner Bank einbehalten und direkt ans Finanzamt abgeführt.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Was ist der Sparerpauschbetrag und wie nutze ich ihn? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Der Sparerpauschbetrag ist ein jährlicher Freibetrag von 1.000 € für Singles und 2.000 € für Ehepaare. Alle Kapitalerträge bis zu dieser Grenze sind steuerfrei. Stelle bei jeder Bank einen Freistellungsauftrag – sonst zieht die Bank automatisch Steuern ab, auch wenn du unter der Grenze bist.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Muss ich Aktiengewinne nach 1 Jahr nicht versteuern? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Nein – bei Aktien und ETFs gilt die Abgeltungssteuer unabhängig von der Haltedauer. Die 1-Jahres-Regelung gilt nur für Kryptowährungen. Nach einem Jahr Krypto-Haltefrist sind Gewinne vollständig steuerfrei.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Wie werden Krypto-Gewinne besteuert? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Kryptowährungen gelten als privates Veräußerungsgeschäft. Gewinne über 1.000 € Freigrenze sind steuerpflichtig wenn die Haltedauer unter einem Jahr liegt – mit dem persönlichen Einkommensteuersatz (bis zu 45 %). Nach einem Jahr sind alle Gewinne vollständig steuerfrei.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Was ist die Teilfreistellung bei ETFs? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Bei Aktien-ETFs werden nur 70 % der Gewinne besteuert – 30 % sind steuerfrei. Der effektive Steuersatz liegt damit bei ca. 18,5 % statt 26,375 %.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Wie hilft Monvesto bei der Steuer? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Monvesto fasst alle realisierten Gewinne, Dividenden, Zinsen und P2P-Erträge automatisch zusammen. Du siehst wie viel deines Sparerpauschbetrags bereits genutzt ist und welche Krypto-Positionen bald die steuerfreie Jahresfrist erreichen.</div>
    </div>
  </div>
</section>
 
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/includes/footer.php"; ?>
</body>
</html>