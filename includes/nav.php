<nav>
  <a href="/" class="nav-logo">
    <div class="nav-logo-mark">M</div>
    <span class="nav-logo-text">Monvesto</span>
  </a>
  <div class="nav-links">
    <a href="/konten-kreditkarten/" class="nav-link <?php if(strpos($_SERVER['REQUEST_URI'],'/konten-kreditkarten/')!==false) echo 'active'; ?>">Konten</a>
    <a href="/portfolio/" class="nav-link <?php if(strpos($_SERVER['REQUEST_URI'],'/portfolio/')!==false) echo 'active'; ?>">Portfolio</a>
    <a href="/kryptowaehrungen/" class="nav-link <?php if(strpos($_SERVER['REQUEST_URI'],'/kryptowaehrungen/')!==false) echo 'active'; ?>">Krypto</a>
    <a href="/sparplaene/" class="nav-link <?php if(strpos($_SERVER['REQUEST_URI'],'/sparplaene/')!==false) echo 'active'; ?>">Sparpläne</a>
    <a href="/p2p-kredite/" class="nav-link <?php if(strpos($_SERVER['REQUEST_URI'],'/p2p-kredite/')!==false) echo 'active'; ?>">P2P</a>
    <a href="/module/" class="nav-link <?php if(strpos($_SERVER['REQUEST_URI'],'/module/')!==false) echo 'active'; ?>">Alle Module</a>
  </div>
  <div class="nav-actions">
    <a href="https://app.monvesto.de" class="nav-login">Anmelden</a>
    <a href="https://app.monvesto.de" class="nav-cta">Kostenlos starten →</a>
  </div>
</nav>