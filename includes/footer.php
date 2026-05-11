<footer>
  <div class="footer-grid">
    <div class="footer-brand">
      <div class="footer-logo">
        <div class="footer-logo-mark">M</div>
        <span class="footer-logo-text">Monvesto</span>
      </div>
      <p class="footer-desc">Dein persönliches Finanz-Cockpit. Alles über Konten, Kreditkarten, Investments und Kryptowährungen auf einen Blick. Kostenlos, sicher, DSGVO-konform.<br><br>
      <strong>Hinweis:</strong> Monvesto stellt Informationen und technische Auswertungen bereit, jedoch keine Anlage-, Steuer- oder Rechtsberatung. KI-Analysen können unvollständig oder fehlerhaft sein und sollten eigenständig geprüft werden.</p>
    </div>
    <div>
      <div class="footer-col-title">Informationen</div>
      <div class="footer-links">
        <a href="/konten-kreditkarten/" class="footer-link">Konten & Kreditkarten</a>
        <a href="/sparplaene/" class="footer-link">ETF-Sparpläne</a>
        <a href="/kryptowaehrungen/" class="footer-link">Kryptowährungen</a>
        <a href="/p2p-kredite/" class="footer-link">P2P-Kredite</a>
        <a href="/trading/" class="footer-link">Trading</a>
        <a href="/steuern/" class="footer-link">Steuerübersicht</a>
      </div>
    </div>
    <div>
      <div class="footer-col-title">Vergleiche</div>
      <div class="footer-links">
        <a href="/girokonto-vergleich/" class="footer-link">Girokonten vergleichen</a>
        <a href="/kreditkarten-vergleich/" class="footer-link">Kreditkarten vergleichen</a>
        <a href="/tagesgeld-vergleich/" class="footer-link">Tagesgeld vergleichen</a>
        <a href="/krypto-vergleich/" class="footer-link">Kryptobörsen vergleichen</a>
        <a href="/broker-vergleich/" class="footer-link">Broker vergleichen</a>
      </div>
    </div>
    <div>
      <div class="footer-col-title">Rechtliches</div>
      <div class="footer-links">
        <a href="/impressum-datenschutz/" class="footer-link">Impressum</a>
        <a href="/impressum-datenschutz/" class="footer-link">Datenschutz</a>
        <a href="/impressum-datenschutz/" class="footer-link">AGB</a>
        <a href="/kontakt/" class="footer-link">Kontaktformular</a>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <span>© 2026 Monvesto · Alle Rechte vorbehalten</span>
    <span>Keine Anlageberatung · <a href="/impressum-datenschutz/">Impressum & Datenschutz</a></span>
  </div>
</footer>
<script>
document.querySelectorAll('.faq-q').forEach(q => {
  q.addEventListener('click', () => {
    const item = q.closest('.faq-item');
    item.classList.toggle('open');
  });
});
</script>

<script>
  const burger = document.getElementById('navBurger');
  const navLinks = document.getElementById('navLinks');

  burger.addEventListener('click', () => {
    const isOpen = navLinks.classList.toggle('is-open');
    burger.classList.toggle('is-open', isOpen);
    burger.setAttribute('aria-expanded', isOpen);
  });

  document.addEventListener('click', (e) => {
    if (!burger.contains(e.target) && !navLinks.contains(e.target)) {
      navLinks.classList.remove('is-open');
      burger.classList.remove('is-open');
      burger.setAttribute('aria-expanded', false);
    }
  });

  navLinks.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
      navLinks.classList.remove('is-open');
      burger.classList.remove('is-open');
      burger.setAttribute('aria-expanded', false);
    });
  });
</script>

</body>
</html>