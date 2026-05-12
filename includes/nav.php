<?php 
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<body>
<nav>
  <a href="/" class="nav-logo">
    <div class="nav-logo-mark">M</div>
    <span class="nav-logo-text">Monvesto</span>
  </a>
  <button class="nav-burger" id="navBurger" aria-label="Menü öffnen" aria-expanded="false">
    <span></span><span></span><span></span>
  </button>
  <div class="nav-links" id="navLinks">

    <a href="/" class="nav-link <?php if($uri === '/') echo 'active'; ?>">Startseite</a>

    <div class="nav-item has-dropdown <?php if(strpos($uri,'/konten-kreditkarten/')!==false||strpos($uri,'/girokonto-vergleich/')!==false||strpos($uri,'/kreditkarten-vergleich/')!==false||strpos($uri,'/tagesgeld-vergleich/')!==false) echo 'active'; ?>">
      <button class="nav-dropdown-toggle" aria-expanded="false">
        Konten &amp; Kreditkarten
        <svg class="nav-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <ul class="nav-dropdown">
        <li><a href="/konten-kreditkarten/" <?php if($uri==='/konten-kreditkarten/') echo 'class="active"'; ?>>Übersicht</a></li>
        <li><a href="/girokonto-vergleich/" <?php if(strpos($uri,'/girokonto-vergleich/')!==false) echo 'class="active"'; ?>>Girokonten vergleichen</a></li>
        <li><a href="/kreditkarten-vergleich/" <?php if(strpos($uri,'/kreditkarten-vergleich/')!==false) echo 'class="active"'; ?>>Kreditkarten vergleichen</a></li>
        <li><a href="/tagesgeld-vergleich/" <?php if(strpos($uri,'/tagesgeld-vergleich/')!==false) echo 'class="active"'; ?>>Tagesgeld vergleichen</a></li>
      </ul>
    </div>

    <a href="/sparplaene/" class="nav-link <?php if(strpos($uri,'/sparplaene/')!==false) echo 'active'; ?>">Sparpläne</a>
    <a href="/kryptowaehrungen/" class="nav-link <?php if(strpos($uri,'/kryptowaehrungen/')!==false) echo 'active'; ?>">Kryptowährungen</a>
    <a href="/p2p-kredite/" class="nav-link <?php if(strpos($uri,'/p2p-kredite/')!==false) echo 'active'; ?>">P2P-Kredite</a>

    <div class="nav-item has-dropdown <?php if(strpos($uri,'/trading/')!==false||strpos($uri,'/broker-vergleich/')!==false) echo 'active'; ?>">
      <button class="nav-dropdown-toggle" aria-expanded="false">
        Trading
        <svg class="nav-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <ul class="nav-dropdown">
        <li><a href="/trading/" <?php if($uri==='/trading/') echo 'class="active"'; ?>>Übersicht</a></li>
        <li><a href="/broker-vergleich/" <?php if(strpos($uri,'/broker-vergleich/')!==false) echo 'class="active"'; ?>>Broker vergleichen</a></li>
      </ul>
    </div>

    <a href="/steuern/" class="nav-link <?php if(strpos($uri,'/steuern/')!==false) echo 'active'; ?>">Steuern</a>

    <div class="nav-mobile-actions">
      <a href="https://app.monvesto.de" class="nav-login">Anmelden</a>
      <a href="https://app.monvesto.de" class="nav-cta">Kostenlos starten →</a>
    </div>
  </div>

  <div class="nav-actions">
    <a href="https://app.monvesto.de" class="nav-login">Anmelden</a>
    <a href="https://app.monvesto.de" class="nav-cta">Kostenlos starten →</a>
  </div>
</nav>