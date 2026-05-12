<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tagesgeld Vergleich 2026 – Die besten Zinsen im Überblick | Monvesto</title>
  <meta name="description" content="Vergleiche aktuelle Tagesgeldkonten und finde den Anbieter mit den höchsten Zinsen – flexibel, sicher und täglich verfügbar." />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://monvesto.de/tagesgeld-vergleich/" />
  <link rel="stylesheet" href="../assets/style.css" />
  <link rel="stylesheet" href="../assets/vergleich-components.css" />
  <script type="application/ld+json">{"@context":"https://schema.org","@type":"FAQPage","mainEntity":[{"@type":"Question","name":"Was ist Tagesgeld genau?","acceptedAnswer":{"@type":"Answer","text":"Tagesgeld ist ein Sparkonto mit variablem Zinssatz, bei dem du täglich über dein Geld verfügen kannst."}},{"@type":"Question","name":"Ist mein Geld auf Tagesgeldkonten sicher?","acceptedAnswer":{"@type":"Answer","text":"Ja. Alle EU-Banken sind gesetzlich verpflichtet, Einlagen bis 100.000 € pro Person und Bank abzusichern."}}]}</script>
</head>
<body>
<?php $tagesgeldAnbieter = require $_SERVER['DOCUMENT_ROOT'] . '/anbieter/tagesgeld-anbieter.php';

usort($tagesgeldAnbieter, function ($a, $b) {
  return ($a['rank'] ?? 999) <=> ($b['rank'] ?? 999);
});

$topTagesgeldAnbieter = array_values(array_filter($tagesgeldAnbieter, function ($anbieter) {
  return !empty($anbieter['show_top']);
}));

$topTagesgeldAnbieter = array_slice($topTagesgeldAnbieter, 0, 3);

function e($value) {
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
} ?>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/nav.php'; ?>

<section class="hero hero-bg-green">
  <div class="hero-badge">Zinsen aktuell – 2026</div>
  <h1>Tagesgeld Vergleich 2026 –<br><span class="highlight">die besten Zinsen für dein Erspartes</span></h1>
  <p class="hero-sub">Vergleiche aktuelle Tagesgeldkonten und finde den Anbieter mit den höchsten Zinsen – flexibel, sicher und täglich verfügbar.</p>
  <div class="hero-actions">
    <a href="#vergleich" class="btn btn-primary btn-lg">Zinsen vergleichen</a>
    <a href="#alle-anbieter" class="btn btn-secondary btn-lg">Alle Anbieter ansehen</a>
  </div>
</section>

<div class="trust-bar">
  <div class="trust-item"><span class="trust-check">✓</span> Täglich verfügbar</div>
  <div class="trust-item"><span class="trust-check">✓</span> Einlagensicherung bis 100k €</div>
  <div class="trust-item"><span class="trust-check">✓</span> Aktuelle Tagesgeldzinsen</div>
  <div class="trust-item"><span class="trust-check">✓</span> Flexibel kündbar</div>
</div>

<!-- ── ZINSRECHNER ── -->
<section class="section" style="background:var(--bg);">
  <div class="section-label">Zinsrechner</div>
  <h2 class="section-title">So viel kannst du verdienen</h2>
  <p class="section-intro">Berechne deine Zinseinnahmen auf Basis von Betrag, Zinssatz und Laufzeit.</p>

  <div class="calculator-box">
    <div class="calculator-title">Zinsrechner – Tagesgeld 2026</div>
    <div class="calculator-row">
      <label for="calc-amount">Betrag</label>
      <input type="range" id="calc-amount" min="1000" max="100000" step="1000" value="10000">
      <span class="calculator-value" id="calc-amount-out">10.000 €</span>
    </div>
    <div class="calculator-row">
      <label for="calc-rate">Zinssatz p.a.</label>
      <input type="range" id="calc-rate" min="0.5" max="4.5" step="0.1" value="3.5">
      <span class="calculator-value" id="calc-rate-out">3,5 %</span>
    </div>
    <div class="calculator-row">
      <label for="calc-months">Laufzeit</label>
      <input type="range" id="calc-months" min="1" max="36" step="1" value="12">
      <span class="calculator-value" id="calc-months-out">12 Monate</span>
    </div>
    <div class="calculator-result">
      <div>
        <div class="calculator-result-label">Zinsen nach Laufzeit</div>
        <div class="calculator-result-note" id="calc-note">bei 10.000 € · 3,5 % · 12 Mon.</div>
      </div>
      <div class="calculator-result-right">
        <div class="calculator-result-value" id="calc-out">350 €</div>
        <div class="calculator-result-note" id="calc-net">nach Abgeltungssteuer: ~258 €</div>
      </div>
    </div>
  </div>
</section>

<hr class="divider" />

<!-- ── TOP PICKS ── -->
<section class="section" id="vergleich">
  <div class="section-label">Unsere Empfehlungen</div>
  <h2 class="section-title">Die besten Tagesgeldkonten 2026</h2>
  <p class="section-intro">Bewertet nach aktuellem Zinssatz, Einlagensicherung, Flexibilität und Konditionen.</p>

  <div class="pick-list mt-32">
  <?php foreach ($topTagesgeldAnbieter as $anbieter): ?>
    <div class="pick-card<?= !empty($anbieter['featured']) ? ' pick-card--featured' : '' ?>">
      <div class="pick-rank">#<?= e($anbieter['rank']) ?></div>

      <div class="pick-info">
        <?php if (!empty($anbieter['badge'])): ?>
          <span class="best-badge"><?= e($anbieter['badge']) ?></span>
        <?php endif; ?>

        <div class="pick-name"><?= e($anbieter['name']) ?></div>

        <?php if (!empty($anbieter['description'])): ?>
          <p class="pick-desc"><?= e($anbieter['description']) ?></p>
        <?php endif; ?>

        <?php if (!empty($anbieter['tags'])): ?>
          <div class="tag-group">
            <?php foreach ($anbieter['tags'] as $tag): ?>
              <span class="tag <?= e($tag['class'] ?? '') ?>"><?= e($tag['text'] ?? '') ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="pick-rate-badge">
        <div class="pick-rate-num"><?= e($anbieter['rate_num']) ?></div>
        <div class="pick-rate-unit">p.a.</div>
      </div>

      <div class="pick-actions">
        <a href="<?= e($anbieter['url']) ?>" target="_blank" rel="nofollow sponsored" class="btn-affiliate">
          <?= e($anbieter['button'] ?? 'Jetzt eröffnen →') ?>
        </a>

        <?php if (!empty($anbieter['detail_anchor']) && $anbieter['detail_anchor'] !== '#'): ?>
          <a href="<?= e($anbieter['detail_anchor']) ?>" class="btn-outline-sm">Details ansehen</a>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

  <div class="infobox infobox--green mt-24">
    <i class="ti ti-shield-check"></i>
    <span>Alle genannten Anbieter unterliegen der europäischen Einlagensicherung bis 100.000 € pro Person und Bank. Dein Geld ist gesetzlich geschützt.</span>
  </div>

  <div class="affiliate-disclosure">
    <i class="ti ti-info-circle"></i>
    <span><strong>Hinweis:</strong> Diese Seite enthält Affiliate-Links. Zinssätze können sich jederzeit ändern – bitte die aktuellen Konditionen beim jeweiligen Anbieter prüfen. Unsere Bewertungen sind redaktionell unabhängig.</span>
  </div>
</section>

<hr class="divider" />

<!-- ── VERGLEICHSTABELLE ── -->
<section class="section" style="background:var(--bg);" id="alle-anbieter">
  <div class="section-label">Vergleich</div>
  <h2 class="section-title">Alle Anbieter im direkten Vergleich</h2>

  <div class="table-responsive">
    <table class="compare-table mt-32">
      <thead>
        <tr>
          <th>Anbieter</th>
          <th>Zinssatz</th>
          <th>Aktionszins</th>
          <th>Verfügbarkeit</th>
          <th>Einlagensicherung</th>
          <th>Mindestanlage</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
  <?php foreach ($tagesgeldAnbieter as $anbieter): ?>
    <?php
      $promo = trim($anbieter['promo'] ?? '–');
      $hasPromo = ($promo !== '' && $promo !== '–');
    ?>

    <tr>
      <td>
        <strong><?= e($anbieter['table_name'] ?? $anbieter['name']) ?></strong><br>
        <small><?= e($anbieter['type']) ?></small>
      </td>

      <td>
        <span class="rate-highlight"><?= e($anbieter['rate']) ?></span>
      </td>

      <td>
        <?php if ($hasPromo): ?>
          <span class="tag tag-amber"><?= e($promo) ?></span>
        <?php else: ?>
          <span class="dash">–</span>
        <?php endif; ?>
      </td>

      <td>
        <span class="check">✓</span> <?= e($anbieter['availability']) ?>
      </td>

      <td>
        <span class="tag <?= e($anbieter['deposit_class']) ?>">
          <?= e($anbieter['deposit_protection']) ?>
        </span>
      </td>

      <td>
        <span class="tag tag-green"><?= e($anbieter['minimum']) ?></span>
      </td>

      <td>
        <a
          href="<?= e($anbieter['url']) ?>"
          target="_blank"
          rel="nofollow sponsored"
          class="btn-affiliate"
          style="font-size:12px;padding:7px 12px;"
        >
          <?= e($anbieter['table_button'] ?? 'Öffnen') ?>
        </a>
      </td>
    </tr>
  <?php endforeach; ?>
</tbody>
    </table>
  </div>
</section>

<hr class="divider" />

<section class="cta-banner">
  <h2>Tagesgeld & Investments. Ein Überblick. Mit Monvesto.</h2>
  <p>Verbinde dein Tagesgeldkonto und alle anderen Konten – Monvesto zeigt deinen Gesamtstand in Echtzeit.</p>
  <!--
  <a href="https://app.monvesto.de" class="btn btn-white btn-lg">Jetzt kostenlos starten →</a>
  -->
</section>

<!-- ── FAQ ── -->
<section class="section-sm" style="padding:80px 32px; max-width:760px; margin:0 auto;">
  <div class="section-label">Häufige Fragen</div>
  <h2 class="section-title">Deine Fragen beantwortet</h2>
  <div class="faq-list">
    <div class="faq-item">
      <div class="faq-q">Was ist Tagesgeld genau? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Tagesgeld ist ein Sparkonto mit variablem Zinssatz, bei dem du täglich über dein Geld verfügen kannst. Im Gegensatz zu Festgeld gibt es keine feste Laufzeit – du kannst jederzeit einzahlen und abheben.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Ist mein Geld auf Tagesgeldkonten sicher? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Ja. Alle EU-Banken sind gesetzlich verpflichtet, Einlagen bis 100.000 € pro Person und Bank abzusichern. Deutsche Banken bieten oft zusätzlichen Schutz über den deutschen Einlagensicherungsfonds.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Was bedeutet Aktionszins und worauf sollte ich achten? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Viele Banken bieten attraktive Zinssätze nur für Neukunden und nur für einige Monate. Danach sinkt der Zins auf ein niedrigeres Niveau. Es lohnt sich, nach Ablauf des Aktionszinses zu prüfen, ob ein Wechsel sinnvoll ist.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Muss ich Zinsen versteuern? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Ja. Zinserträge unterliegen der Abgeltungssteuer von 25 % plus Solidaritätszuschlag. Der Sparerpauschbetrag beträgt 1.000 € pro Person – bis zu diesem Betrag sind Zinsen steuerfrei.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Kann ich mehrere Tagesgeldkonten gleichzeitig haben? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Ja, und das ist oft empfehlenswert. Durch mehrere Konten kannst du Neukunden-Aktionszinsen bei verschiedenen Banken nutzen und Beträge über 100.000 € verteilen, um die volle Einlagensicherung zu nutzen.</div>
    </div>
  </div>
</section>

<script>
(function () {
  function calc() {
  var a  = parseInt(document.getElementById('calc-amount').value);
  var r  = parseFloat(document.getElementById('calc-rate').value);
  var mo = parseInt(document.getElementById('calc-months').value);

  document.getElementById('calc-amount-out').textContent = a.toLocaleString('de-DE') + ' €';
  document.getElementById('calc-rate-out').textContent   = r.toFixed(1).replace('.', ',') + ' %';
  document.getElementById('calc-months-out').textContent = mo + ' Monat' + (mo === 1 ? '' : 'e');

  // Zinseszins: Endkapital = Kapital × (1 + Jahreszins)^(Monate/12)
  var endkapital = a * Math.pow(1 + (r / 100), mo / 12);
  var gross = Math.round(endkapital - a);
  var net   = Math.round(gross * 0.7375);

  document.getElementById('calc-out').textContent  = gross.toLocaleString('de-DE') + ' €';
  document.getElementById('calc-net').textContent  = 'nach Abgeltungssteuer: ~' + net.toLocaleString('de-DE') + ' €';
  document.getElementById('calc-note').textContent = 'bei ' + a.toLocaleString('de-DE') + ' € · ' + r.toFixed(1).replace('.', ',') + ' % · ' + mo + ' Mon.';
}
  document.getElementById('calc-amount').addEventListener('input', calc);
  document.getElementById('calc-rate').addEventListener('input', calc);
  document.getElementById('calc-months').addEventListener('input', calc);
})();
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>