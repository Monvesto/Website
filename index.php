<?php
$title    = "Finanz-Cockpit für Konten, ETFs & Krypto | Monvesto";
$meta     = "Monvesto bringt alle deine Finanzen auf einen Blick: Konten, Aktien, ETFs, Krypto und P2P-Kredite. Kostenlos starten.";
$canonical = "https://monvesto.de/";
$schema   = '{"@context":"https://schema.org","@type":"SoftwareApplication","name":"Monvesto","applicationCategory":"FinanceApplication","offers":{"@type":"Offer","price":"0","priceCurrency":"EUR"}}';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php';
?>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/nav.php'; ?>


<section class="hero hero-bg-green">
  <div class="hero-badge">Dein persönliches Finanz-Cockpit</div>
  <h1>Alle deine Finanzen.<br><span class="highlight">Ein Überblick.</span></h1>
  <p class="hero-sub">Konten, Aktien, ETFs, Krypto und P2P-Kredite – endlich alles zusammen. In Echtzeit. Kostenlos.</p>
  <div class="hero-actions">
   <!-- <a href="https://app.monvesto.de" class="btn btn-primary btn-lg">Jetzt kostenlos starten →</a> -->
    <a href="#themen" class="btn btn-primary btn-lg">Alle Bereiche ansehen</a>
  </div>
  <p style="margin-top:20px;font-size:13px;color:var(--text-light);">
    Für Einsteiger und Profis &middot; Alles auf einen Blick &middot; <!-- <span style="color:var(--green);font-weight:600;"> --> Keine Chance verpassen <!--</span>-->
  </p>

  <!-- DASHBOARD MOCK -->
<div style="max-width:900px;margin:48px auto 0;background:white;border:0.5px solid var(--border);border-radius:var(--radius-lg);box-shadow:0 20px 60px rgba(0,0,0,0.1);overflow:hidden;">
  <div style="background:var(--bg);border-bottom:0.5px solid var(--border);padding:10px 16px;display:flex;align-items:center;gap:6px;">
    <div style="width:10px;height:10px;border-radius:50%;background:#FF5F57;"></div>
    <div style="width:10px;height:10px;border-radius:50%;background:#FFBD2E;"></div>
    <div style="width:10px;height:10px;border-radius:50%;background:#28C840;"></div>
    <div style="margin-left:8px;font-size:12px;color:var(--text-muted);background:white;border:0.5px solid var(--border);border-radius:6px;padding:3px 12px;">app.monvesto.de/dashboard</div>
  </div>
  <div style="display:flex;min-height:280px;">
    <!-- Sidebar – auf Mobile versteckt -->
    <div style="width:180px;flex-shrink:0;background:#111827;padding:16px 12px;display:flex;flex-direction:column;gap:4px;" class="mock-sidebar-inner">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
        <div style="width:24px;height:24px;border-radius:7px;background:var(--green);display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:11px;">M</div>
        <span style="color:white;font-weight:700;font-size:13px;">Monvesto</span>
      </div>
      <div style="padding:7px 10px;border-radius:8px;background:rgba(29,158,117,0.2);font-size:11px;color:white;">● Übersicht</div>
      <div style="padding:7px 10px;font-size:11px;color:rgba(255,255,255,0.5);">● Konten</div>
      <div style="padding:7px 10px;font-size:11px;color:rgba(255,255,255,0.5);">● Portfolio</div>
      <div style="padding:7px 10px;font-size:11px;color:rgba(255,255,255,0.5);">● Krypto</div>
      <div style="padding:7px 10px;font-size:11px;color:rgba(255,255,255,0.5);">● Sparpläne</div>
      <div style="padding:7px 10px;font-size:11px;color:rgba(255,255,255,0.5);">● P2P</div>
      <div style="padding:7px 10px;font-size:11px;color:rgba(255,255,255,0.5);">● KI-Analyse</div>
      <div style="padding:7px 10px;font-size:11px;color:rgba(255,255,255,0.5);">● Steuern</div>
    </div>
    <!-- Content -->
    <div style="flex:1;padding:20px;background:var(--bg);min-width:0;">
      <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">Guten Morgen, Max 👋</div>
      <div style="font-size:clamp(18px,4vw,28px);font-weight:800;letter-spacing:-0.5px;">128.450,00 €</div>
      <div style="font-size:11px;color:var(--text-muted);margin-bottom:16px;">Gesamtvermögen · <span style="color:var(--green);">+2.340 € diesen Monat</span></div>
      <div class="mock-cards-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:12px;">
        <div style="background:white;border:0.5px solid var(--border);border-radius:8px;padding:10px;">
          <div style="font-size:9px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Konten</div>
          <div style="font-size:clamp(12px,2vw,15px);font-weight:700;color:var(--green);">14.230 €</div>
        </div>
        <div style="background:white;border:0.5px solid var(--border);border-radius:8px;padding:10px;">
          <div style="font-size:9px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Portfolio</div>
          <div style="font-size:clamp(12px,2vw,15px);font-weight:700;color:#2563EB;">89.120 €</div>
        </div>
        <div style="background:white;border:0.5px solid var(--border);border-radius:8px;padding:10px;">
          <div style="font-size:9px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Krypto</div>
          <div style="font-size:clamp(12px,2vw,15px);font-weight:700;color:#F7931A;">25.100 €</div>
        </div>
      </div>
      <div style="background:white;border:0.5px solid var(--border);border-radius:8px;padding:12px;height:60px;display:flex;align-items:flex-end;gap:3px;">
        <div style="background:var(--green-light);border-radius:3px;flex:1;height:40%;"></div>
        <div style="background:var(--green-light);border-radius:3px;flex:1;height:55%;"></div>
        <div style="background:var(--green-light);border-radius:3px;flex:1;height:45%;"></div>
        <div style="background:var(--green-light);border-radius:3px;flex:1;height:65%;"></div>
        <div style="background:var(--green-light);border-radius:3px;flex:1;height:58%;"></div>
        <div style="background:var(--green-light);border-radius:3px;flex:1;height:72%;"></div>
        <div style="background:var(--green-light);border-radius:3px;flex:1;height:68%;"></div>
        <div style="background:var(--green);border-radius:3px;flex:1;height:85%;"></div>
      </div>
    </div>
  </div>
</div>
</section>

<!-- Trustbar -->
<div class="trust-bar">
  <div class="trust-item"><span class="trust-check">✓</span> DSGVO-konform</div>
  <div class="trust-item"><span class="trust-check">✓</span> EU-Server Frankfurt</div>
  <div class="trust-item"><span class="trust-check">✓</span> PSD2-zertifiziert</div>
  <div class="trust-item"><span class="trust-check">✓</span> Keine Datenweitergabe</div>
  <div class="trust-item"><span class="trust-check">✓</span> Kostenlos starten</div>
</div>

<!-- Übersicht -->
<section class="section" id="themen">
  <div class="section-center mb-40">
    <div class="section-label">Alle Funktionen</div>
    <h2 class="section-title">Alles was deine Finanzen brauchen</h2>
    <p class="section-intro center">Von Girokonten über ETF-Sparpläne bis zu Kryptowährungen und Steuern – alle Bereiche auf einen Blick.</p>
  </div>
  <div class="grid-3">
    <a href="/konten-kreditkarten/" style="text-decoration:none;" class="card card-hover">
      <div class="card-tag">Basis</div><div class="card-icon">🏦</div>
      <div style="font-size:18px;font-weight:700;margin-bottom:8px;">Konten & Kreditkarten</div>
      <p class="text-muted" style="font-size:14px;line-height:1.65;margin-bottom:14px;">Giro, Tagesgeld, Festgeld und Kreditkarten auf einen Blick. Unterschiede zusammengefasst und erklärt.</p>
      <div style="font-size:13px;font-weight:700;color:var(--green);">Mehr erfahren →</div>
    </a>
    <a href="/sparplaene/" style="text-decoration:none;" class="card card-hover">
      <div class="card-tag">Automatisch</div><div class="card-icon">🔄</div>
      <div style="font-size:18px;font-weight:700;margin-bottom:8px;">ETF-Sparpläne</div>
      <p class="text-muted" style="font-size:14px;line-height:1.65;margin-bottom:14px;">Die einfachste und sicherste Anlagemöglichkeit für jeden, egal ob Einsteiger oder Profi.</p>
      <div style="font-size:13px;font-weight:700;color:var(--green);">Mehr erfahren →</div>
    </a>
    <a href="/kryptowaehrungen/" style="text-decoration:none;" class="card card-hover">
      <div class="card-tag">Digital Assets</div><div class="card-icon">₿</div>
      <div style="font-size:18px;font-weight:700;margin-bottom:8px;">Kryptowährungen</div>
      <p class="text-muted" style="font-size:14px;line-height:1.65;margin-bottom:14px;">Bitcoin, Ethereum und sogenannte Alt-Coins. Livedaten, Stärken und Schwächen von Kryptowährungen.</p>
      <div style="font-size:13px;font-weight:700;color:var(--green);">Mehr erfahren →</div>
    </a>
    <a href="/p2p-kredite/" style="text-decoration:none;" class="card card-hover">
      <div class="card-tag">Rendite</div><div class="card-icon">🤝</div>
      <div style="font-size:18px;font-weight:700;margin-bottom:8px;">P2P-Kredite</div>
      <p class="text-muted" style="font-size:14px;line-height:1.65;margin-bottom:14px;">P2P-Investments, oder auch: Kredite von Person zu Person. Kluges Investment und große Chance?</p>
      <div style="font-size:13px;font-weight:700;color:var(--green);">Mehr erfahren →</div>
    </a>
    <a href="/steuern/" style="text-decoration:none;" class="card card-hover">
      <div class="card-tag">Finanzamt & Co.</div><div class="card-icon">📈</div>
      <div style="font-size:18px;font-weight:700;margin-bottom:8px;">Steuern</div>
      <p class="text-muted" style="font-size:14px;line-height:1.65;margin-bottom:14px;">Steuern, Kapitalerträge, Gewinne und Verluste übersichtlich im Blick behalten.</p>
      <div style="font-size:13px;font-weight:700;color:var(--green);">Mehr erfahren →</div>
    </a>
    <a href="/portfolio/" style="text-decoration:none;" class="card card-hover card-disabled">
      <div class="coming-badge">Coming soon</div>
      <div class="card-tag">Investments</div><div class="card-icon">📈</div>
      <div style="font-size:18px;font-weight:700;margin-bottom:8px;">Portfolio & Aktien</div>
      <p class="text-muted" style="font-size:14px;line-height:1.65;margin-bottom:14px;">Bankkonten, Aktien, ETFs und Krypto mit Live-Kursen tracken. Gesamtvermögen, Gewinn/Verlust und Performance in Echtzeit.</p>
      <div style="font-size:13px;font-weight:700;color:var(--green);">Mehr erfahren →</div>
    </a>
    <a href="/ki-analyse/" style="text-decoration:none;" class="card card-hover card-disabled">
      <div class="coming-badge">Coming soon</div>
      <div class="card-tag">KI-Powered</div><div class="card-icon">🤖</div>
      <div style="font-size:18px;font-weight:700;margin-bottom:8px;">KI-Finanzanalyse</div>
      <p class="text-muted" style="font-size:14px;line-height:1.65;margin-bottom:14px;">Dein persönlicher KI-Berater analysiert dein Vermögen – powered by Anthropic Claude.</p>
      <div style="font-size:13px;font-weight:700;color:var(--green);">Mehr erfahren →</div>
    </a>
  </div>
</section>

<hr class="divider" />

<!-- in 4 Schritten -->
<section class="section" style="max-width:1140px;text-align:center;">
  <div class="section-label">So einfach geht's</div>
  <h2 class="section-title">In 4 Schritten zum Überblick</h2>
  <p class="section-intro center mb-40">Keine komplizierte Einrichtung. In wenigen Minuten siehst du dein gesamtes Vermögen.</p>
  <div class="grid-4" style="margin-top:40px;text-align:center;">
    <div style="padding:24px;">
      <div style="width:52px;height:52px;border-radius:50%;background:var(--green);color:white;font-weight:800;font-size:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">1</div>
      <div style="font-size:16px;font-weight:700;margin-bottom:8px;">Konto erstellen</div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;">Kostenlos mit E-Mail registrieren. In 60 Sekunden startklar.</p>
    </div>
    <div style="padding:24px;">
      <div style="width:52px;height:52px;border-radius:50%;background:var(--green);color:white;font-weight:800;font-size:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">2</div>
      <div style="font-size:16px;font-weight:700;margin-bottom:8px;">Konten & Assets eintragen</div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;">Manuell oder per API erfassen. Banken, Wallets, Sparpläne uvm.</p>
    </div>
    <div style="padding:24px;">
      <div style="width:52px;height:52px;border-radius:50%;background:var(--green);color:white;font-weight:800;font-size:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">3</div>
      <div style="font-size:16px;font-weight:700;margin-bottom:8px;">Überblick genießen</div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;">Tracke und analysiere dein Gesamtvermögen in Echtzeit.</p>
    </div>
    <div style="padding:24px;">
      <div style="width:52px;height:52px;border-radius:50%;background:var(--green);color:white;font-weight:800;font-size:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">4</div>
      <div style="font-size:16px;font-weight:700;margin-bottom:8px;">KI-Analyse nutzen</div>
      <p class="text-muted" style="font-size:14px;line-height:1.6;">Die KI unterstützt dich mit Hinweisen zu Struktur, Risiken und Optimierungspotenzialen.</p>
    </div>
  </div>
</section>

<hr class="divider" />

<section class="section" id="preise">
  <div class="section-center mb-40">
    <div class="section-label">Preise</div>
    <h2 class="section-title">Transparent. Fair. Ohne Tricks.</h2>
    <p class="section-intro center">Starte kostenlos – upgrade nur wenn du mehr willst.</p>
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;max-width:720px;margin:0 auto;">
    <div class="card">
      <div style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-muted);margin-bottom:12px;">Free</div>
      <div style="font-size:40px;font-weight:900;letter-spacing:-1px;margin-bottom:4px;">0 <span style="font-size:16px;font-weight:500;color:var(--text-muted);">€ / Monat</span></div>
      <p class="text-muted" style="font-size:14px;margin-bottom:24px;">Dauerhaft kostenlos.</p>
      <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:28px;">
        <div style="font-size:14px;display:flex;gap:10px;"><span class="text-green fw-700">✓</span> Bis zu 2 Konten</div>
        <div style="font-size:14px;display:flex;gap:10px;"><span class="text-green fw-700">✓</span> Bis zu 5 Investments</div>
        <div style="font-size:14px;display:flex;gap:10px;"><span class="text-green fw-700">✓</span> 1 ETF-Sparplan</div>
        <div style="font-size:14px;display:flex;gap:10px;"><span class="text-green fw-700">✓</span> Live-Kurse</div>
        <div style="font-size:14px;display:flex;gap:10px;color:var(--text-light);"><span>✕</span> Bank-API Sync</div>
        <div style="font-size:14px;display:flex;gap:10px;color:var(--text-light);"><span>✕</span> Unbegrenzte Investments</div>
      </div>
      <a href="https://app.monvesto.de" class="btn btn-secondary" style="display:block;text-align:center;">Kostenlos starten</a>
    </div>
    <div class="card" style="border:2px solid var(--green);position:relative;">
      <div style="position:absolute;top:-13px;left:50%;transform:translateX(-50%);background:var(--green);color:white;font-size:12px;font-weight:700;padding:4px 16px;border-radius:100px;white-space:nowrap;">Beliebteste Wahl</div>
      <div style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-muted);margin-bottom:12px;">Premium</div>
      <div style="font-size:40px;font-weight:900;letter-spacing:-1px;margin-bottom:4px;">7,99 <span style="font-size:16px;font-weight:500;color:var(--text-muted);">€ / Monat</span></div>
      <p class="text-muted" style="font-size:14px;margin-bottom:24px;">Alles unbegrenzt.</p>
      <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:28px;">
        <div style="font-size:14px;display:flex;gap:10px;"><span class="text-green fw-700">✓</span> Unbegrenzte Konten</div>
        <div style="font-size:14px;display:flex;gap:10px;"><span class="text-green fw-700">✓</span> Unbegrenzte Investments</div>
        <div style="font-size:14px;display:flex;gap:10px;"><span class="text-green fw-700">✓</span> Bank-API Synchronisation</div>
        <div style="font-size:14px;display:flex;gap:10px;"><span class="text-green fw-700">✓</span> Vollständige KI-Analyse</div>
        <div style="font-size:14px;display:flex;gap:10px;"><span class="text-green fw-700">✓</span> Steuerübersicht & Export</div>
        <div style="font-size:14px;display:flex;gap:10px;"><span class="text-green fw-700">✓</span> Coinbase API Import</div>
      </div>
      <a href="https://app.monvesto.de" class="btn btn-primary" style="display:block;text-align:center;">Jetzt Premium starten →</a>
    </div>
  </div>
</section>

<hr class="divider" />

<!-- KONTAKT SECTION-->
<section class="section" id="kontakt" style="max-width:760px;margin:0 auto;text-align:center;">
  <div class="section-label">Kontakt</div>
  <h2 class="section-title">Fragen? Wir helfen gerne.</h2>
  <p class="section-intro center" style="margin-bottom:32px;">
    Egal ob Support, allgemeine Fragen oder Kooperationsanfragen – schreib uns einfach.
  </p>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:36px;">
    <div class="card" style="text-align:center;padding:24px;">
      <div style="font-size:28px;margin-bottom:10px;">💬</div>
      <div style="font-size:15px;font-weight:700;margin-bottom:6px;">Allgemeine Anfragen</div>
      <p class="text-muted" style="font-size:13px;">Feedback und Fragen zu Monvesto</p>
    </div>
    <div class="card" style="text-align:center;padding:24px;">
      <div style="font-size:28px;margin-bottom:10px;">🛠️</div>
      <div style="font-size:15px;font-weight:700;margin-bottom:6px;">Support & Hilfe</div>
      <p class="text-muted" style="font-size:13px;">Technische Probleme & App-Fragen</p>
    </div>
    <div class="card" style="text-align:center;padding:24px;">
      <div style="font-size:28px;margin-bottom:10px;">📰</div>
      <div style="font-size:15px;font-weight:700;margin-bottom:6px;">Presse & Kooperationen</div>
      <p class="text-muted" style="font-size:13px;">Medien, Partner & Affiliate</p>
    </div>
  </div>
  <a href="/kontakt/" class="btn btn-primary btn-lg">Zum Kontaktformular →</a>
  <p style="margin-top:16px;font-size:13px;color:var(--text-muted);">
    Oder direkt per E-Mail: <a href="mailto:info@monvesto.de" style="color:var(--green);font-weight:600;">info@monvesto.de</a>
  </p>
</section>

<hr class="divider" />

<!-- Call to Action-->
<section class="cta-banner">
  <h2>Dein Vermögen. Endlich auf einen Blick.</h2>
  <p>Starte kostenlos in 60 Sekunden. Keine Kreditkarte. Kein Risiko.</p>
  <div class="cta-feats">
    <div class="cta-feat">2 Konten dauerhaft gratis</div>
    <div class="cta-feat">DSGVO-konform</div>
    <div class="cta-feat">Jederzeit kündbar</div>
    <div class="cta-feat">EU-Server</div>
  </div>
  <a href="https://app.monvesto.de" class="btn btn-white btn-lg">Jetzt kostenlos starten →</a>
</section>

<!-- FAQ-->
<section class="section-sm" style="padding:80px 32px;max-width:760px;margin:0 auto;">
  <div class="section-label">FAQ</div>
  <h2 class="section-title">Häufige Fragen</h2>
  <div class="faq-list">
    <div class="faq-item open"><div class="faq-q">Sind meine Daten bei Monvesto sicher? <span class="faq-arrow">▾</span></div><div class="faq-a">Ja. Alle Daten werden verschlüsselt auf EU-Servern gespeichert. DSGVO-konform, keine Datenweitergabe. Bank-API hat nur Lesezugriff.</div></div>
    <div class="faq-item"><div class="faq-q">Welche Banken werden unterstützt? <span class="faq-arrow">▾</span></div><div class="faq-a">Alle großen deutschen Banken: Sparkasse, ING, DKB, Comdirect, N26, Volksbank und mehr. Investments manuell oder per Coinbase-API.</div></div>
    <div class="faq-item"><div class="faq-q">Was kostet Monvesto? <span class="faq-arrow">▾</span></div><div class="faq-a">Dauerhaft kostenlos für bis zu 2 Konten und 5 Investments. Premium kostet 7,99 € pro Monat, jederzeit kündbar.</div></div>
    <div class="faq-item"><div class="faq-q">Funktioniert Monvesto auf dem Smartphone? <span class="faq-arrow">▾</span></div><div class="faq-a">Die Web-App unter app.monvesto.de funktioniert auf allen Geräten. Eine native App ist in Planung.</div></div>
  </div>
</section>

<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/includes/footer.php"; ?>
</body>
</html>