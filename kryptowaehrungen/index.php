<?php
$title    = "Kryptowährungen verstehen – Bitcoin, Ethereum & Co. | Monvesto";
$meta     = "Was sind Kryptowährungen? Wie sicher ist Krypto? Bitcoin, Ethereum, Stablecoins erklärt – mit Live-Tracking in Monvesto.";
$canonical = "https://monvesto.de/kryptowaehrungen/";
$schema   = '{"@context":"https://schema.org","@type":"FAQPage","mainEntity":[{"@type":"Question","name":"Was ist eine Kryptowährung?","acceptedAnswer":{"@type":"Answer","text":"Digitales Geld auf Blockchain-Basis ohne zentrale Kontrolle."}}]}';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php';
?>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/nav.php'; ?>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/api/widget.php';
$btc = get_crypto_price('bitcoin');
$eth = get_crypto_price('ethereum');
$usdt = get_crypto_price('tether');
?>

<section class="hero hero-bg-orange">
  <div class="hero-badge">Kryptowährungen</div>
  <h1>Krypto verstehen –<br><span class="highlight">von Bitcoin bis DeFi</span></h1>
  <p class="hero-sub">Was sind Kryptowährungen? Wie sicher ist Krypto? Bitcoin, Ethereum, Stablecoins erklärt – mit Live-Tracking in Monvesto.</p>
  <!-- 
  <div class="hero-actions">
    <a href="https://app.monvesto.de" class="btn btn-primary btn-lg">Kostenlos starten →</a>
    <a href="#inhalt" class="btn btn-secondary btn-lg">Alles erklärt ↓</a>
  </div>
-->
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
  <p class="mt-16" style="font-size:16px;color:var(--text-muted);line-height:1.7;">
  Bekannte Beispiele sind Bitcoin, Ethereum oder Solana. Der Markt umfasst inzwischen tausende
  Projekte mit unterschiedlichen Anwendungsfällen – von Zahlungsnetzwerken bis hin zu
  dezentralen Finanzsystemen.</p>
  <p class="mt-16" style="font-size:16px;color:var(--text-muted);line-height:1.7;">Bitcoin wurde 2009 als Reaktion auf die Finanzkrise geschaffen. Heute ist Krypto sowohl Technologie als auch Anlageklasse – mit hohen Chancen und erheblichen Risiken.</p>
</section>

<hr class="divider" />

<section class="section">
  <div class="section-label">Die wichtigsten Coins</div>
  <h2 class="section-title">Bitcoin, Ethereum & Co.</h2>

<p class="mt-16" style="font-size:16px;color:var(--text-muted);line-height:1.7;">
  Der Kryptomarkt besteht aus tausenden Projekten – doch nur wenige haben sich
  langfristig etabliert. Viele Anleger konzentrieren sich deshalb auf große und
  bekannte Kryptowährungen, die eine hohe Marktkapitalisierung und breite Nutzung haben.<br>

Bitcoin, Ethereum und Stablecoins erfüllen dabei unterschiedliche Rollen im Portfolio:
  von langfristiger Wertanlage über technologische Plattform bis hin zu stabilen
  „Zwischenpark“-Lösungen.
</p>

  <div class="grid-3 mt-40">
    <div class="card">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
        <div style="width:48px;height:48px;border-radius:50%;background:#F7931A;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:20px;">₿</div>
        <div>
        <div style="font-size:17px;font-weight:700;">Bitcoin </div>
        <div style="font-size:17px;font-weight:700;"><?= number_format($btc['price'], 2, ',', '.') ?> € </div>
        <div style="font-size:12px;color:var(--text-muted);">BTC</div>
      </div>
      </div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;margin-bottom:14px;">
        Die erste und größte Kryptowährung. Wird oft als „digitales Gold“ und langfristige Wertanlage gesehen.</p>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Steuerfrei nach</span><span style="font-weight:600;">1 Jahr Haltefrist</span></div>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Volatilität</span><span style="color:#EF4444;font-weight:600;">Hoch</span></div>
    </div>
    <div class="card">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
        <div style="width:48px;height:48px;border-radius:50%;background:#627EEA;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:18px;">Ξ</div>
        <div>
        <div style="font-size:17px;font-weight:700;">Ethereum</div>
        <div style="font-size:17px;font-weight:700;"><?= number_format($eth['price'], 2, ',', '.') ?> € </div>
        <div style="font-size:12px;color:var(--text-muted);">ETH</div>
      </div>
      </div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;margin-bottom:14px;">
        Plattform für Smart Contracts und Grundlage vieler Krypto-Projekte wie Smart Contracts, DeFi und NFTs. Einer der wichtigsten Bausteine im Krypto-Ökosystem.</p>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Steuerfrei nach</span><span style="font-weight:600;">1 Jahr Haltefrist</span></div>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Volatilität</span><span style="color:#EF4444;font-weight:600;">Hoch</span></div>
    </div>
    <div class="card">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
        <div style="width:48px;height:48px;border-radius:50%;background:#26A17B;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:16px;">$</div>
        <div>
        <div style="font-size:17px;font-weight:700;">Stablecoins</div>
        <div style="font-size:17px;font-weight:700;"><?= number_format($usdt['price'], 2, ',', '.') ?> € </div>
        <div style="font-size:12px;color:var(--text-muted);">USDC, USDT, DAI</div>
      </div>
      </div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;margin-bottom:14px;">
        Krypto ohne große Kursschwankungen. Sie sind meist 1:1 an Währungen wie den US-Dollar gekoppelt.</p>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Volatilität</span><span style="color:var(--green);font-weight:600;">Sehr gering</span></div>
      <div style="font-size:13px;padding:6px 0;border-top:0.5px solid var(--border);display:flex;justify-content:space-between;"><span class="text-muted">Risiko</span><span style="color:#F59E0B;font-weight:600;">Emittentenrisiko</span></div>
    </div>
  </div>

<p class="mt-16" style="font-size:16px;color:var(--text-muted);line-height:1.7;">
  Statt auf viele kleine Projekte zu setzen, fokussieren sich viele Anleger auf wenige,
  etablierte Kryptowährungen. Das reduziert Komplexität und hilft, Risiken besser zu
  kontrollieren.<br>

Wichtig ist dabei: Jede Kategorie erfüllt einen anderen Zweck. Während Bitcoin oft als
  langfristige Wertanlage gesehen wird, bildet Ethereum die Grundlage vieler Anwendungen
  und Stablecoins sorgen für Stabilität im Portfolio.<br><br>

 <strong>Krypto-Grundregel:</strong> Wenige große Coins schlagen oft viele kleine Spekulationen.
</p>

</section>

<hr class="divider" />

<section class="section" id="inhalt">
  <div class="section-label">Grundlagen</div>
  <h2 class="section-title">Wo kann ich Kryptowährungen kaufen oder handeln?</h2>
  <p class="section-intro">Für Einsteiger sind einfache Apps oft ausreichend – fortgeschrittene Nutzer
  achten stärker auf Funktionen wie Wallets oder Trading-Tools.</p>
  <p class="mt-16" style="font-size:16px;color:var(--text-muted);line-height:1.7;">
  Kryptowährungen kannst du über spezialisierte Krypto-Börsen (auch Exchange genannt) - oder
  bei einigen Brokern kaufen. Die Unterschiede liegen vor allem bei
  Gebühren, Sicherheit und Benutzerfreundlichkeit.</p>

  <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:24px;margin-top:40px;">

    <div class="card" style="border:2px solid var(--green);position:relative;">
      <div style="position:absolute;top:-12px;left:20px;background:var(--green);color:white;font-size:11px;font-weight:700;padding:4px 12px;border-radius:100px;">Sollte jeder haben</div>
      <div class="card-icon">💳</div>
      <div style="font-size:18px;font-weight:700;margin-bottom:8px;">Einsteiger-Exchange</div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;margin-bottom:16px;">Einfacher Einstieg in Kryptowährungen. Kaufen, verkaufen und verwalten
    über eine intuitive App – ideal für Anfänger.</p>
      <div style="display:flex;flex-direction:column;gap:0;">
        <div style="display:flex;justify-content:space-between;font-size:13px;padding:7px 0;border-top:0.5px solid var(--border);"><span class="text-muted">Gebühren</span><span style="font-weight:600;color:var(--green);">0,5 – 2,0 %</span></div>
        <div style="display:flex;justify-content:space-between;font-size:13px;padding:7px 0;border-top:0.5px solid var(--border);"><span class="text-muted">Verfügbarkeit</span><span style="font-weight:600;">Täglich</span></div>
        <div style="display:flex;justify-content:space-between;font-size:13px;padding:7px 0;border-top:0.5px solid var(--border);"><span class="text-muted">Anbieter</span><span style="font-weight:600;">Coinbase, Bitpanda</span></div>
      </div>
    </div>

    <div class="card">
      <div class="people-icon">👫</div>
      <div style="font-size:18px;font-weight:700;margin-bottom:8px;">Trading-Exchange</div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;margin-bottom:16px;">Für aktive Nutzer mit Fokus auf günstige Gebühren und viele Handelsmöglichkeiten.
    Mehr Funktionen, aber auch komplexer in der Bedienung.</p>
      <div style="display:flex;flex-direction:column;gap:0;">
        <div style="display:flex;justify-content:space-between;font-size:13px;padding:7px 0;border-top:0.5px solid var(--border);"><span class="text-muted">Gebühren</span><span style="font-weight:600;color:var(--green);">0,1 – 0,5 %</span></div>
        <div style="display:flex;justify-content:space-between;font-size:13px;padding:7px 0;border-top:0.5px solid var(--border);"><span class="text-muted">Verfügbarkeit</span><span style="font-weight:600;">24/7</span></div>
        <div style="display:flex;justify-content:space-between;font-size:13px;padding:7px 0;border-top:0.5px solid var(--border);"><span class="text-muted">Anbieter</span><span style="font-weight:600;">Binance, Kraken</span></div>
      </div>
    </div>

    <div class="card">
      <div class="card-icon">💰</div>
      <div style="font-size:18px;font-weight:700;margin-bottom:8px;">Hardware Wallet (Sicherheit)</div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;margin-bottom:16px;">Für größere Beträge sinnvoll: Eigene Verwahrung deiner Kryptowährungen
    außerhalb von Börsen – maximale Sicherheit.</p>
      <div style="display:flex;flex-direction:column;gap:0;">
        <div style="display:flex;justify-content:space-between;font-size:13px;padding:7px 0;border-top:0.5px solid var(--border);"><span class="text-muted">Kosten</span><span style="font-weight:600;color:var(--green);">Einmalig ca. 50 – 150 €</span></div>
        <div style="display:flex;justify-content:space-between;font-size:13px;padding:7px 0;border-top:0.5px solid var(--border);"><span class="text-muted">Zugriff</span><span style="font-weight:600;">Manuell</span></div>
        <div style="display:flex;justify-content:space-between;font-size:13px;padding:7px 0;border-top:0.5px solid var(--border);"><span class="text-muted">Anbieter</span><span style="font-weight:600;">Ledger, Trezor</span></div>
      </div>
    </div>

    <div class="card">
      <div class="card-icon">🔒</div>
      <div style="font-size:18px;font-weight:700;margin-bottom:8px;">Sparplan / Auto-Invest</div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;margin-bottom:16px;">Regelmäßig automatisch in Kryptowährungen investieren – ähnlich wie
    ein ETF-Sparplan. Ideal für langfristigen Vermögensaufbau.</p>
      <div style="display:flex;flex-direction:column;gap:0;">
        <div style="display:flex;justify-content:space-between;font-size:13px;padding:7px 0;border-top:0.5px solid var(--border);"><span class="text-muted">Kosten</span><span style="font-weight:600;color:var(--green);">Oft kostenlos oder geringe Gebühren</span></div>
        <div style="display:flex;justify-content:space-between;font-size:13px;padding:7px 0;border-top:0.5px solid var(--border);"><span class="text-muted">Intervall</span><span style="font-weight:600;">Wöchentlich / monatlich</span></div>
        <div style="display:flex;justify-content:space-between;font-size:13px;padding:7px 0;border-top:0.5px solid var(--border);"><span class="text-muted">Anbieter</span><span style="font-weight:600;">Coinbase, Binance, Bitpanda</span></div>
      </div>
    </div>

  </div>

  <p style="font-size:16px;color:var(--text-muted);line-height:1.7;margin-top:36px;">
    Wie du siehst, ist eine Kombination der Modelle sinnvoll, weil du so Liquidität, Struktur und Rendite gleichzeitig optimierst.
    Du trennst Alltag, Rücklagen und langfristiges Sparen klar voneinander und behältst jederzeit den Überblick.
  </p>

  <section class="insight-section" style="margin-top:36px ;">
    <div class="insight-inner">
      <!-- <div class="insight-label">Wichtig</div> -->
      <div class="insight-title">Die wichtigste Erkenntnis</div>
      <p class="insight-text">
        Kryptowährungen sind kein Ersatz für dein Portfolio – sondern eine Ergänzung
    mit höherem Risiko und Potenzial.<br>
    Die Plattform ist zweitrangig – wichtiger ist, dass du deine Kryptowährungen
    verstehst und langfristig hältst statt ständig zu traden.
      </p>
    </div>
  </section>
</section>

<hr class="divider" />

<section class="section" id="inhalt">
  <div class="section-label">Wallets</div>
  <h2 class="section-title">Was ist ein Krypto-Wallet?</h2>
  <p class="section-intro">Ein Krypto-Wallet ist dein Zugang zu Kryptowährungen. Es speichert nicht die Coins selbst,
  sondern die sogenannten privaten Schlüssel, mit denen du auf deine Kryptowährungen zugreifen
  und Transaktionen durchführen kannst.</p>
  <p class="mt-16" style="font-size:16px;color:var(--text-muted);line-height:1.7;">
   Grundsätzlich unterscheidet man zwischen Wallets auf Börsen (z. B. Coinbase oder Binance)
  und eigenen Wallets, bei denen du die volle Kontrolle über deine Kryptowährungen hast.<br>
  Es gibt zwei Hauptarten von Wallets:</p>
  <p class="mt-16" style="font-size:16px;color:var(--text-muted);line-height:1.7;">  
  <strong>Hot Wallets:</strong> Online verfügbar (Apps oder Börsen) – einfach zu bedienen,
    und in der Regel bei jedem Anbieter verfügbar. Bei weniger seriösen Anbietern, jedoch oft einem Risiko ausgesetzt.</p>
    <p class="mt-16" style="font-size:16px;color:var(--text-muted);line-height:1.7;"><strong>Cold Wallets:</strong> Offline (Hardware Wallets) – deutlich sicherer, da sie nicht öffentlich verfügbar sind. Dadurch sind sie aber auch weniger flexibel im Alltag und eher für das HODLn (Verwahren auf längere Zeit) gedacht.</p>

<!-- Tabelle folgt hier -->
  <div style="overflow-x:auto; margin-top:32px;">
    <table class="data-table">
      <thead><tr><th>Art der Wallet</th><th>Hot Wallet</th><th>Cold Wallet</th></tr></thead>
      <tbody>
        <tr><td><strong>Sicherheit</strong></td><td class="cross">geringer (online angreifbar)</td><td class="check">sehr hoch (offline gespeichert)</td></tr>
        <tr><td><strong>Zugriff</strong></td><td class="check">jederzeit online</td><td class="cross">Nur mit physischem Gerät</td></tr>
        <tr><td><strong>Kosten</strong></td><td class="check">oft kostenlos</td><td class="cross">50 - 150 € Einmalig</td></tr>
        <tr><td><strong>Verwendung</strong></td><td class="check">kleine Beträge</td><td class="check">Größere Beträge</td></tr>
        <tr><td><strong>Geeignet für</strong></td><td class="check">Einsteiger</td><td class="check">langfristige Anleger</td></tr>
        <tr><td><strong>Anbieter</strong></td><td class="check">Coinbase, Binance</td><td class="check">Ledger, Trezor</td></tr>
      </tbody>
    </table>
  </div>

  <section class="insight-section" style="margin-top:36px;">
    <div class="insight-inner" style="border: 2px solid #ff000050;">
      <!-- <div class="insight-label">Wichtig</div> -->
      <div class="insight-title">Achtung</div>
      <p class="insight-text">
        „Not your keys, not your coins“ - Nur wenn du deine privaten Schlüssel selbst kontrollierst, gehören dir deine Kryptowährungen wirklich! Deshalb schütze deinen Prvate-Key so, dass er immer für dich zugänglich ist und nicht gestohlen werden kann!
      </p>
    </div>

  </section>
</section>

</section>

<hr class="divider" />

<section class="section">
  <div class="section-label">Chancen & Risiken</div>
  <h2 class="section-title">Wie sicher ist Krypto wirklich?</h2>
  <div class="grid-3 mt-40">
    <div class="card"><div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#EF4444;margin-bottom:10px;">
      ⚠ Hohes Risiko</div><div style="font-size:16px;font-weight:700;margin-bottom:8px;">Kursschwankungen</div><p class="text-muted" style="font-size:14px;line-height:1.65;">Bitcoin hat historisch 50–80 % verloren – und danach neue Hochs erreicht.</p><div style="margin-top:12px;padding:10px;background:#FEF2F2;border-radius:8px;font-size:12px;color:#991B1B;">Nur Geld investieren dessen Totalverlust du verkraftest.</div></div>
    <div class="card"><div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#EF4444;margin-bottom:10px;">
      ⚠ Hohes Risiko</div><div style="font-size:16px;font-weight:700;margin-bottom:8px;">Verlust des Private Key</div><p class="text-muted" style="font-size:14px;line-height:1.65;">Wer seinen Seed verliert, verliert dauerhaft den Zugang. Kein Support, keine Wiederherstellung.</p><div style="margin-top:12px;padding:10px;background:#FEF2F2;border-radius:8px;font-size:12px;color:#991B1B;">Seed-Phrase niemals digital speichern.</div></div>
    <div class="card"><div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--green);margin-bottom:10px;">
      ✓ Chance</div><div style="font-size:16px;font-weight:700;margin-bottom:8px;">Steuerfrei nach 1 Jahr</div><p class="text-muted" style="font-size:14px;line-height:1.65;">In Deutschland sind Krypto-Gewinne nach einem Jahr Haltedauer vollständig steuerfrei.</p><div style="margin-top:12px;padding:10px;background:var(--green-light);border-radius:8px;font-size:12px;color:var(--green-dark);">5–10 % Portfolioanteil empfohlen.</div></div>
    <div class="card"><div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--green);margin-bottom:10px;">
      ✓ Chance</div><div style="font-size:16px;font-weight:700;margin-bottom:8px;">Klein starten</div><p class="text-muted" style="font-size:14px;line-height:1.65;">Beginne mit einem kleinen Betrag und sammle Erfahrung, bevor du größere Summen investierst.</p><div style="margin-top:12px;padding:10px;background:var(--green-light);border-radius:8px;font-size:12px;color:var(--green-dark);">Oft reicht es schon, 20, 30 oder 50 € zu investieren. Die Höhe der Gebühren sollte man dabei jedoch immer berücksichtigen.</div></div>
    <div class="card"><div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--green);margin-bottom:10px;">
      ✓ Chance</div><div style="font-size:16px;font-weight:700;margin-bottom:8px;">Auf große Coins fokussieren</div><p class="text-muted" style="font-size:14px;line-height:1.65;">Bitcoin und Ethereum bilden oft die Basis vieler Krypto-Portfolios, da sie etablierter und weniger riskant sind.</p><div style="margin-top:12px;padding:10px;background:var(--green-light);border-radius:8px;font-size:12px;color:var(--green-dark);">Große Coins sollte immer die Basis der Auswahl sein. Nach und nach kann man Alt-Coins in das Portfolio integrieren.</div></div>
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

<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/includes/footer.php"; ?>
</body>
</html>