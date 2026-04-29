<?php
$title    = "Kryptowährungen verstehen – Bitcoin, Ethereum & Co. | Monvesto";
$meta     = "Was sind Kryptowährungen? Wie sicher ist Krypto? Bitcoin, Ethereum, Stablecoins erklärt – mit Live-Tracking in Monvesto.";
$canonical = "https://monvesto.de/kryptowaehrungen/";
$schema   = '{"@context":"https://schema.org","@type":"FAQPage","mainEntity":[{"@type":"Question","name":"Was ist eine Kryptowährung?","acceptedAnswer":{"@type":"Answer","text":"Digitales Geld auf Blockchain-Basis ohne zentrale Kontrolle."}}]}';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php';
?>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/nav.php'; ?>


<section class="hero hero-bg-orange">
  <div class="hero-badge">Kryptowährungen</div>
  <h1>Krypto verstehen –<br><span class="highlight">von Bitcoin bis DeFi</span></h1>
  <p class="hero-sub">Was sind Kryptowährungen? Wie sicher ist Krypto? Bitcoin, Ethereum, Stablecoins erklärt – mit Live-Tracking in Monvesto.</p>
  <div class="hero-actions">
    <a href="https://app.monvesto.de" class="btn btn-primary btn-lg">Kostenlos starten →</a>
    <a href="#inhalt" class="btn btn-secondary btn-lg">Alles erklärt ↓</a>
  </div>
</section>

<div class="trust-bar">
  <div class="trust-item">₿ Bitcoin seit 2009</div>
  <div class="trust-item">🔐 Blockchain-Technologie</div>
  <div class="trust-item"><span class='trust-check'>✓</span> Live-Kurse via CoinGecko</div>
  <div class="trust-item">🇩🇪 Steuerregeln Deutschland</div>
  <div class="trust-item"><span class='trust-check'>✓</span> Coinbase Import</div></div>


<section class="section" id="inhalt">
  <div class="section-label">Grundlagen</div>
  <h2 class="section-title">Was sind Kryptowährungen?</h2>
  <p class="section-intro">Kryptowährungen sind digitales Geld ohne Mittelmann. Keine Bank, keine Regierung – nur Mathematik und ein dezentrales Netzwerk weltweit.</p>
  <p class="mt-16" style="font-size:16px;color:var(--text-muted);line-height:1.7;">Bitcoin wurde 2009 als Reaktion auf die Finanzkrise geschaffen. Heute ist Krypto sowohl Technologie als auch Anlageklasse – mit hohen Chancen und erheblichen Risiken.</p>
</section>
<hr class="divider" />
<section class="section">
  <div class="section-label">Die wichtigsten Coins</div>
  <h2 class="section-title">Bitcoin, Ethereum & Co.</h2>
  <div class="grid-3 mt-40">
    <div class="card" style="border:2px solid #F7931A;">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
        <div style="width:48px;height:48px;border-radius:50%;background:#F7931A;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:20px;">₿</div>
        <div><div style="font-size:17px;font-weight:700;">Bitcoin</div><div style="font-size:12px;color:var(--text-muted);">BTC</div></div>
      </div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;margin-bottom:14px;">Digitales Gold. Begrenzt auf 21 Mio. Coins. Wertaufbewahrung und inflationsresistente Anlage.</p>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Steuerfrei nach</span><span style="font-weight:600;">1 Jahr Haltefrist</span></div>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Volatilität</span><span style="color:#EF4444;font-weight:600;">Hoch</span></div>
    </div>
    <div class="card">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
        <div style="width:48px;height:48px;border-radius:50%;background:#627EEA;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:18px;">Ξ</div>
        <div><div style="font-size:17px;font-weight:700;">Ethereum</div><div style="font-size:12px;color:var(--text-muted);">ETH</div></div>
      </div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;margin-bottom:14px;">Die programmierbare Blockchain. Grundlage für Smart Contracts, DeFi und NFTs.</p>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Steuerfrei nach</span><span style="font-weight:600;">1 Jahr Haltefrist</span></div>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Volatilität</span><span style="color:#EF4444;font-weight:600;">Hoch</span></div>
    </div>
    <div class="card">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
        <div style="width:48px;height:48px;border-radius:50%;background:#26A17B;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:16px;">$</div>
        <div><div style="font-size:17px;font-weight:700;">Stablecoins</div><div style="font-size:12px;color:var(--text-muted);">USDC, USDT, DAI</div></div>
      </div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;margin-bottom:14px;">Krypto ohne Kursschwankung. 1:1 an den US-Dollar gekoppelt.</p>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Volatilität</span><span style="color:var(--green);font-weight:600;">Sehr gering</span></div>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Risiko</span><span style="color:#F59E0B;font-weight:600;">Emittentenrisiko</span></div>
    </div>
  </div>
</section>
<hr class="divider" />
<section class="section">
  <div class="section-label">Chancen & Risiken</div>
  <h2 class="section-title">Wie sicher ist Krypto wirklich?</h2>
  <div class="grid-3 mt-40">
    <div class="card"><div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#EF4444;margin-bottom:10px;">⚠ Hohes Risiko</div><div style="font-size:16px;font-weight:700;margin-bottom:8px;">Kursschwankungen</div><p class="text-muted" style="font-size:14px;line-height:1.65;">Bitcoin hat historisch 50–80 % verloren – und danach neue Hochs erreicht.</p><div style="margin-top:12px;padding:10px;background:#FEF2F2;border-radius:8px;font-size:12px;color:#991B1B;">Nur Geld investieren dessen Totalverlust du verkraftest.</div></div>
    <div class="card"><div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#EF4444;margin-bottom:10px;">⚠ Hohes Risiko</div><div style="font-size:16px;font-weight:700;margin-bottom:8px;">Verlust des Private Key</div><p class="text-muted" style="font-size:14px;line-height:1.65;">Wer seinen Seed verliert, verliert dauerhaft den Zugang. Kein Support, keine Wiederherstellung.</p><div style="margin-top:12px;padding:10px;background:#FEF2F2;border-radius:8px;font-size:12px;color:#991B1B;">Seed-Phrase niemals digital speichern.</div></div>
    <div class="card"><div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--green);margin-bottom:10px;">✓ Chance</div><div style="font-size:16px;font-weight:700;margin-bottom:8px;">Steuerfrei nach 1 Jahr</div><p class="text-muted" style="font-size:14px;line-height:1.65;">In Deutschland sind Krypto-Gewinne nach einem Jahr Haltedauer vollständig steuerfrei.</p><div style="margin-top:12px;padding:10px;background:var(--green-light);border-radius:8px;font-size:12px;color:var(--green-dark);">5–10 % Portfolioanteil empfohlen.</div></div>
  </div>
</section>

<section class="cta-banner">
  <h2>Dein Krypto-Portfolio. Immer aktuell.</h2>
  <p>Tracke Bitcoin, Ethereum und alle Coins in Echtzeit. Sieh Gewinn/Verlust und wie Krypto in dein Gesamtvermögen passt.</p>
  <a href="https://app.monvesto.de" class="btn btn-white btn-lg">Jetzt kostenlos starten →</a>
</section>

<section class="section-sm" style="padding:80px 32px;max-width:760px;margin:0 auto;">
  <div class="section-label">Häufige Fragen</div>
  <h2 class="section-title">Deine Fragen beantwortet</h2>
  <div class="faq-list">
    <div class="faq-item">
      <div class="faq-q">Was ist eine Kryptowährung? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Digitales Geld auf Blockchain-Basis, das dezentral ohne Bank funktioniert. Transaktionen werden kryptographisch gesichert.</div>
    </div>    <div class="faq-item">
      <div class="faq-q">Wie sicher ist Krypto? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Die Blockchain ist sehr sicher. Hauptrisiken: Verlust des Private Keys, Exchange-Hacks und extreme Volatilität.</div>
    </div>    <div class="faq-item">
      <div class="faq-q">Muss ich Krypto-Gewinne versteuern? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Ja, bei Haltedauer unter 1 Jahr und Gewinnen über 1.000 € Freigrenze. Nach einem Jahr vollständig steuerfrei.</div>
    </div>    <div class="faq-item">
      <div class="faq-q">Wie tracke ich Krypto in Monvesto? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Coins manuell eintragen oder direkt via Coinbase API importieren. Kurse werden automatisch via CoinGecko aktualisiert.</div>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.faq-q').forEach(function(q) {
    q.addEventListener('click', function() {
      this.closest('.faq-item').classList.toggle('open');
    });
  });
});
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>