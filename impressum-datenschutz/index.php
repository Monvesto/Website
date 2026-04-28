<?php
$title="Impressum & Datenschutz | Monvesto";
$meta="Impressum und Datenschutzerklärung von Monvesto gemäß § 5 TMG und DSGVO.";
$canonical="https://monvesto.de/impressum/";
$schema="";
require_once $_SERVER["DOCUMENT_ROOT"] . "/includes/head.php";
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/includes/nav.php"; ?>
<div class="page-header"><h1>Impressum & Datenschutz</h1><p>Rechtliche Informationen gemäß § 5 TMG und DSGVO</p></div>
<div class="legal-content">
  <style>.tabs{display:flex;gap:8px;margin-bottom:40px;flex-wrap:wrap}.tab{padding:10px 20px;border-radius:8px;font-weight:600;font-size:14px;cursor:pointer;border:1.5px solid var(--border);background:white;color:var(--text-muted)}.tab.active{background:var(--green);color:white;border-color:var(--green)}.tab-content{display:none}.tab-content.active{display:block}</style>
  <div class="tabs">
    <div class="tab active" onclick="showTab(event,'impressum')">Impressum</div>
    <div class="tab" onclick="showTab(event,'datenschutz')">Datenschutz</div>
  </div>
  <div class="tab-content active" id="tab-impressum">
    <div class="notice notice-yellow">⚠️ Bitte mit deinen vollständigen Kontaktdaten ergänzen.</div>
    <h2>Angaben gemäß § 5 TMG</h2>
    <p><strong>[Dein Vor- und Nachname]</strong><br>[Straße Hausnummer]<br>[PLZ] [Stadt]<br>Deutschland</p>
    <h2>Kontakt</h2>
    <p>E-Mail: <a href="mailto:info@monvesto.de">info@monvesto.de</a></p>
    <h2>Haftungsausschluss</h2>
    <p>Alle Inhalte dieser Website dienen der allgemeinen Information und stellen keine Anlage-, Steuer- oder Rechtsberatung dar.</p>
    <h2>Urheberrecht</h2>
    <p>Die erstellten Inhalte unterliegen dem deutschen Urheberrecht. Vervielfältigung bedarf schriftlicher Zustimmung.</p>
  </div>
  <div class="tab-content" id="tab-datenschutz">
    <h2>1. Verantwortlicher</h2>
    <p>[Dein Name und Adresse aus dem Impressum]</p>
    <h2>2. Hosting</h2>
    <p>Marketing-Website: HostEurope (Deutschland). App: Vercel Inc. (AVV vorhanden). Datenbank: Supabase Frankfurt/EU.</p>
    <h2>3. Daten in der App</h2>
    <p>Gespeichert werden: E-Mail, Passwort (verschlüsselt), Profilangaben, manuell eingetragene Finanzdaten. Keine Datenweitergabe, keine Werbung.</p>
    <h2>4. Deine Rechte</h2>
    <p>Auskunft, Berichtigung, Löschung, Einschränkung, Übertragbarkeit, Widerspruch gemäß DSGVO. Kontakt: <a href="mailto:info@monvesto.de">info@monvesto.de</a></p>
    <p style="font-size:13px;color:var(--text-muted);margin-top:32px;">Stand: April 2026</p>
  </div>
</div>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/includes/footer.php"; ?>
<script>
function showTab(e,name){
  document.querySelectorAll(".tab").forEach(t=>t.classList.remove("active"));
  document.querySelectorAll(".tab-content").forEach(t=>t.classList.remove("active"));
  document.getElementById("tab-"+name).classList.add("active");
  e.target.classList.add("active");
}
</script>
</body></html>