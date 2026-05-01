<?php 
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<nav>
  <a href="/" class="nav-logo">
    <div class="nav-logo-mark">M</div>
    <span class="nav-logo-text">Monvesto</span>
  </a>
  <div class="nav-links">    
    <a href="/" class="nav-link <?php if($uri === '/') echo 'active'; ?>">Startseite</a>
    <a href="/konten-kreditkarten/" class="nav-link <?php if(strpos($_SERVER['REQUEST_URI'],'/konten-kreditkarten/')!==false) echo 'active'; ?>">Konten & Kreditkarten</a>
    <a href="/sparplaene/" class="nav-link <?php if(strpos($_SERVER['REQUEST_URI'],'/sparplaene/')!==false) echo 'active'; ?>">Sparpläne</a>
    <a href="/kryptowaehrungen/" class="nav-link <?php if(strpos($_SERVER['REQUEST_URI'],'/kryptowaehrungen/')!==false) echo 'active'; ?>">Kryptowährungen</a>
    <a href="/p2p-kredite/" class="nav-link <?php if(strpos($_SERVER['REQUEST_URI'],'/p2p-kredite/')!==false) echo 'active'; ?>">P2P-Kredite</a>
    <a href="/steuern/" class="nav-link <?php if(strpos($_SERVER['REQUEST_URI'],'/steuern/')!==false) echo 'active'; ?>">Steuern</a>
    <a href="/portfolio/" class="nav-link <?php if(strpos($_SERVER['REQUEST_URI'],'/portfolio/')!==false) echo 'active'; ?>">Portfolio</a>
    <a href="/ki-analyse/" class="nav-link <?php if(strpos($_SERVER['REQUEST_URI'],'/ki-analyse/')!==false) echo 'active'; ?>">KI-Analyse</a>
  </div>
  <div class="nav-actions">
    <a href="https://app.monvesto.de" class="nav-login">Anmelden</a>
    <a href="https://app.monvesto.de" class="nav-cta">Kostenlos starten →</a>
  </div>
</nav>