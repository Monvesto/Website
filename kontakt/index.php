<?php
$title     = "Kontakt – Schreib uns | Monvesto";
$meta      = "Kontaktiere das Monvesto-Team. Allgemeine Anfragen, Support oder Presse – wir antworten schnell.";
$canonical = "https://monvesto.de/kontakt/";
$schema    = "";
require_once $_SERVER["DOCUMENT_ROOT"] . "/includes/head.php";
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/includes/nav.php"; ?>

<?php
$status = $_GET['status'] ?? '';
$msg    = $_GET['msg']    ?? '';
$errors = $msg ? explode('|', urldecode($msg)) : [];
?>

<section class="hero hero-bg-green">
  <div class="hero-badge">Kontakt</div>
  <h1>Wir sind für<br><span class="highlight">dich da</span></h1>
  <p class="hero-sub">Fragen, Feedback oder Kooperationsanfragen – schreib uns und wir melden uns schnell zurück.</p>
</section>

<div class="trust-bar"><div class="trust-item">
  <span class='trust-check'>✓</span> Kostenlos bis 2 Konten</div>
  <div class="trust-item"><span class='trust-check'>✓</span> PSD2-konform</div>
  <div class="trust-item"><span class='trust-check'>✓</span> Bank-API Sync</div>
  <div class="trust-item"><span class='trust-check'>✓</span> DSGVO-konform</div>
</div>

<section class="section" style="max-width:1140px; margin-bottom: 40px;">
  <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:60px;align-items:start;">

    <!-- LINKS: Info -->
    <div>
      <div class="section-label">So erreichst du uns</div>
      <h2 class="section-title" style="font-size:clamp(20px,3vw,28px);">Schnell &amp; unkompliziert</h2>
      <p class="section-intro" style="font-size:15px;">Wir sind ein kleines Team und antworten in der Regel innerhalb von 24–48 Stunden.</p>

      <div style="display:flex;flex-direction:column;gap:20px;margin-top:32px;">
        <div style="display:flex;gap:16px;align-items:flex-start;">
          <div style="width:44px;height:44px;border-radius:12px;background:var(--green-light);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">💬</div>
          <div>
            <div style="font-size:15px;font-weight:700;margin-bottom:4px;">Allgemeine Anfragen</div>
            <p class="text-muted" style="font-size:14px;line-height:1.6;">Fragen zu Monvesto, Feedback oder Verbesserungsvorschläge.</p>
          </div>
        </div>
        <div style="display:flex;gap:16px;align-items:flex-start;">
          <div style="width:44px;height:44px;border-radius:12px;background:var(--green-light);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">🛠️</div>
          <div>
            <div style="font-size:15px;font-weight:700;margin-bottom:4px;">Support &amp; Hilfe</div>
            <p class="text-muted" style="font-size:14px;line-height:1.6;">Technische Probleme, Fragen zur App oder zu deinem Konto.</p>
          </div>
        </div>
        <div style="display:flex;gap:16px;align-items:flex-start;">
          <div style="width:44px;height:44px;border-radius:12px;background:var(--green-light);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">📰</div>
          <div>
            <div style="font-size:15px;font-weight:700;margin-bottom:4px;">Presse &amp; Kooperationen</div>
            <p class="text-muted" style="font-size:14px;line-height:1.6;">Medienanfragen, Partnerschaften oder Affiliate-Programm.</p>
          </div>
        </div>
      </div>

      <div style="margin-top:40px;padding:20px;background:var(--bg);border-radius:var(--radius);border:0.5px solid var(--border);">
        <div style="font-size:13px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px;">E-Mail</div>
        <a href="mailto:info@monvesto.de" style="font-size:16px;font-weight:700;color:var(--green);text-decoration:none;">info@monvesto.de</a>
        <p class="text-muted" style="font-size:13px;margin-top:6px;">Antwort in der Regel innerhalb von 24–48 Stunden</p>
      </div>
    </div>

    <!-- RECHTS: Formular -->
    <div style="padding: 20px;border: 1px solid var(--green-mid);background: var(--bg);border-radius: 18px;">
      <?php if ($status === 'success'): ?>
        <div style="background:var(--green-light);border:0.5px solid var(--green-mid);border-radius:var(--radius-lg);padding:40px;text-align:center;">
          <div style="font-size:48px;margin-bottom:16px;">✅</div>
          <h3 style="font-size:20px;font-weight:700;margin-bottom:8px;">Nachricht erhalten!</h3>
          <p class="text-muted" style="margin-bottom:24px;">Wir melden uns so schnell wie möglich bei dir.</p>
          <a href="/" class="btn btn-primary">Zurück zur Startseite</a>
        </div>

      <?php else: ?>

        <?php if (!empty($errors)): ?>
          <div style="background:#FEF2F2;border:0.5px solid #FECACA;border-radius:var(--radius);padding:16px 20px;margin-bottom:24px;">
            <?php foreach ($errors as $e): ?>
              <div style="font-size:14px;color:#991B1B;display:flex;gap:8px;align-items:center;margin-bottom:4px;">⚠️ <?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form action="/kontakt/send.php" method="POST" style="display:flex;flex-direction:column;gap:20px;">

          <!-- Honeypot -->
          <input type="text" name="website" style="display:none;" tabindex="-1" autocomplete="off" />

          <!-- Anfragetyp -->
          <div>
            <label style="display:block;font-size:14px;font-weight:600;margin-bottom:8px;">Art der Anfrage <span style="color:#EF4444;">*</span></label>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
              <label style="display:flex;align-items:center;gap:8px;padding:10px 16px;border:1.5px solid var(--border);border-radius:8px;cursor:pointer;font-size:14px;font-weight:500;transition:all 0.15s;" class="typ-label">
                <input type="radio" name="typ" value="allgemein" style="accent-color:var(--green);" required> Allgemein
              </label>
              <label style="display:flex;align-items:center;gap:8px;padding:10px 16px;border:1.5px solid var(--border);border-radius:8px;cursor:pointer;font-size:14px;font-weight:500;transition:all 0.15s;" class="typ-label">
                <input type="radio" name="typ" value="support" style="accent-color:var(--green);"> Support
              </label>
              <label style="display:flex;align-items:center;gap:8px;padding:10px 16px;border:1.5px solid var(--border);border-radius:8px;cursor:pointer;font-size:14px;font-weight:500;transition:all 0.15s;" class="typ-label">
                <input type="radio" name="typ" value="presse" style="accent-color:var(--green);"> Presse
              </label>
            </div>
          </div>

          <!-- Name -->
          <div>
            <label for="name" style="display:block;font-size:14px;font-weight:600;margin-bottom:8px;">Name <span style="color:#EF4444;">*</span></label>
            <input type="text" id="name" name="name" placeholder="Max Mustermann" required
              style="width:100%;padding:12px 16px;border:1.5px solid var(--border);border-radius:10px;font-size:15px;font-family:inherit;outline:none;transition:border-color 0.15s;"
              onfocus="this.style.borderColor='var(--green)'" onblur="this.style.borderColor='var(--border)'"
              value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" />
          </div>

          <!-- E-Mail -->
          <div>
            <label for="email" style="display:block;font-size:14px;font-weight:600;margin-bottom:8px;">E-Mail <span style="color:#EF4444;">*</span></label>
            <input type="email" id="email" name="email" placeholder="max@beispiel.de" required
              style="width:100%;padding:12px 16px;border:1.5px solid var(--border);border-radius:10px;font-size:15px;font-family:inherit;outline:none;transition:border-color 0.15s;"
              onfocus="this.style.borderColor='var(--green)'" onblur="this.style.borderColor='var(--border)'"
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
          </div>

          <!-- Betreff -->
          <div>
            <label for="betreff" style="display:block;font-size:14px;font-weight:600;margin-bottom:8px;">Betreff</label>
            <input type="text" id="betreff" name="betreff" placeholder="Worum geht es?"
              style="width:100%;padding:12px 16px;border:1.5px solid var(--border);border-radius:10px;font-size:15px;font-family:inherit;outline:none;transition:border-color 0.15s;"
              onfocus="this.style.borderColor='var(--green)'" onblur="this.style.borderColor='var(--border)'"
              value="<?= htmlspecialchars($_POST['betreff'] ?? '') ?>" />
          </div>

          <!-- Nachricht -->
          <div>
            <label for="nachricht" style="display:block;font-size:14px;font-weight:600;margin-bottom:8px;">Nachricht <span style="color:#EF4444;">*</span></label>
            <textarea id="nachricht" name="nachricht" placeholder="Deine Nachricht..." required rows="5"
              style="width:100%;padding:12px 16px;border:1.5px solid var(--border);border-radius:10px;font-size:15px;font-family:inherit;outline:none;transition:border-color 0.15s;resize:vertical;"
              onfocus="this.style.borderColor='var(--green)'" onblur="this.style.borderColor='var(--border)'"><?= htmlspecialchars($_POST['nachricht'] ?? '') ?></textarea>
          </div>

          <!-- Datenschutz -->
          <label style="display:flex;gap:12px;align-items:flex-start;cursor:pointer;">
            <input type="checkbox" required style="accent-color:var(--green);margin-top:3px;flex-shrink:0;" />
            <span style="font-size:13px;color:var(--text-muted);line-height:1.6;">
              Ich habe die <a href="/datenschutz/" style="color:var(--green);">Datenschutzerklärung</a> gelesen und stimme der Verarbeitung meiner Daten zum Zweck der Kontaktaufnahme zu.
            </span>
          </label>

          <button type="submit" class="btn btn-primary btn-lg" style="cursor:pointer;border:none;">
            Nachricht senden →
          </button>

        </form>
      <?php endif; ?>
    </div>

  </div>
</section>

<section class="cta-banner">
  <h2>Lieber direkt in die App?</h2>
  <p>Starte kostenlos und entdecke alle Funktionen.</p>
  <a href="https://app.monvesto.de" class="btn btn-white btn-lg">Kostenlos starten →</a>
</section>

<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/includes/footer.php"; ?>
</body>
</html>