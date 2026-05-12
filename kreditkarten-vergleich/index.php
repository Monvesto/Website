<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Kreditkarten Vergleich 2026 – Kostenlos, Cashback & Reisevorteile | Monvesto</title>
  <meta name="description" content="Finde die beste Kreditkarte für deinen Alltag – kostenlose Basiskarte, Cashback-Karte oder Premium-Reisekarte mit Lounge-Zugang." />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://monvesto.de/kreditkarten-vergleich/" />
  <link rel="stylesheet" href="../assets/style.css" />
  <link rel="stylesheet" href="../assets/vergleich-components.css" />
  <script type="application/ld+json">{"@context":"https://schema.org","@type":"FAQPage","mainEntity":[{"@type":"Question","name":"Welche Kreditkarte ist wirklich kostenlos?","acceptedAnswer":{"@type":"Answer","text":"Dauerhaft kostenlose Kreditkarten ohne Jahresgebühr sind z. B. die Barclays Visa und die DKB Visa."}},{"@type":"Question","name":"Lohnt sich eine Kreditkarte mit Jahresgebühr?","acceptedAnswer":{"@type":"Answer","text":"Wer viel reist oder häufig einkauft, kann mit Cashback, Meilen oder Versicherungsleistungen die Jahresgebühr schnell reinholen."}}]}</script>
</head>
<body>
<?php
$kreditkartenAnbieter = require $_SERVER['DOCUMENT_ROOT'] . '/anbieter/kreditkarten-anbieter.php';

usort($kreditkartenAnbieter, function ($a, $b) {
  return ($a['rank'] ?? 999) <=> ($b['rank'] ?? 999);
});

$topKreditkartenAnbieter = array_values(array_filter($kreditkartenAnbieter, function ($anbieter) {
  return !empty($anbieter['show_top']);
}));

$topKreditkartenAnbieter = array_slice($topKreditkartenAnbieter, 0, 3);

function e($value) {
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/nav.php'; ?>

<section class="hero hero-bg-green">
  <div class="hero-badge">Kreditkarten Vergleich 2026</div>
  <h1>Die besten Kreditkarten –<br><span class="highlight">kostenlos, Cashback & Reise</span></h1>
  <p class="hero-sub">Finde die beste Kreditkarte für deinen Alltag – ob kostenlose Basiskarte, Cashback-Karte oder Premium-Reisekarte mit Lounge-Zugang.</p>
  <div class="hero-actions">
    <a href="#vergleich" class="btn btn-primary btn-lg">Kreditkarten vergleichen</a>
    <a href="#alle-karten" class="btn btn-secondary btn-lg">Alle Karten ansehen</a>
  </div>
</section>

<div class="trust-bar">
  <div class="trust-item"><span class="trust-check">✓</span> Kostenlose Karten</div>
  <div class="trust-item"><span class="trust-check">✓</span> Cashback & Punkte</div>
  <div class="trust-item"><span class="trust-check">✓</span> Reisevorteile</div>
  <div class="trust-item"><span class="trust-check">✓</span> Versicherungen inklusive</div>
</div>

<!-- ── TOP PICKS ── -->
<section class="section" id="vergleich">
  <div class="section-label">Unsere Empfehlungen</div>
  <h2 class="section-title">Die besten Kreditkarten 2026</h2>
  <p class="section-intro">Bewertet nach Jahresgebühr, Cashback, Reisevorteilen, Fremdwährungsgebühren und App-Qualität.</p>

  <div class="pick-list mt-32">
  <?php foreach ($topKreditkartenAnbieter as $anbieter): ?>
    <div class="pick-card<?= !empty($anbieter['featured']) ? ' pick-card--featured' : '' ?>">
      <div class="pick-rank">#<?= e($anbieter['rank']) ?></div>

      <div class="pick-info">
        <?php if (!empty($anbieter['badge'])): ?>
          <span class="best-badge"><?= e($anbieter['badge']) ?></span>
        <?php endif; ?>

        <div class="pick-name"><?= e($anbieter['name']) ?></div>
        <p class="pick-desc"><?= e($anbieter['description']) ?></p>

        <div class="tag-group">
          <?php foreach ($anbieter['tags'] as $tag): ?>
            <span class="tag <?= e($tag['class'] ?? '') ?>"><?= e($tag['text']) ?></span>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="pick-rating">
        <span class="pick-stars"><?= e($anbieter['stars']) ?></span>
        <span class="pick-score"><?= e($anbieter['score']) ?></span>
        <span class="pick-score-label">/ 5,0</span>
      </div>

      <div class="pick-actions">
        <a href="<?= e($anbieter['url']) ?>" target="_blank" rel="nofollow sponsored" class="btn-affiliate">
          <?= e($anbieter['button']) ?>
        </a>

        <?php if (!empty($anbieter['detail_anchor']) && $anbieter['detail_anchor'] !== '#'): ?>
          <a href="<?= e($anbieter['detail_anchor']) ?>" class="btn-outline-sm">Details ansehen</a>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

  <div class="affiliate-disclosure">
    <i class="ti ti-info-circle"></i>
    <span><strong>Hinweis:</strong> Diese Seite enthält Affiliate-Links. Bei Kartenbeantragung über unsere Links erhalten wir eine Provision – für dich entstehen keine Mehrkosten. Unsere Bewertungen sind redaktionell unabhängig.</span>
  </div>
</section>

<hr class="divider" />

<!-- ── NUTZERTYPEN ── -->
<section class="section">
  <div class="section-label">Entscheidungshilfe</div>
  <h2 class="section-title">Welche Kreditkarte passt zu dir?</h2>

  <div class="type-grid mt-32">
    <div class="type-card type-card--featured">
      <span class="type-tag type-tag--green">Kostenlos</span>
      <h3>Für Einsteiger & Reisende</h3>
      <p>Keine Jahresgebühr, weltweit einsetzbar, keine versteckten Kosten.</p>
      <ul><li>0 € Jahresgebühr</li><li>Keine FX-Gebühren</li><li>Einfache Beantragung</li></ul>
      <a href="https://www.barclays.de/?ref=monvesto" target="_blank" rel="nofollow sponsored" class="btn-affiliate">Zur Barclays Visa →</a>
    </div>
    <div class="type-card">
      <span class="type-tag type-tag--amber">Cashback</span>
      <h3>Für Vielkäufer</h3>
      <p>Mit jedem Einkauf Punkte oder Cashback sammeln und profitieren.</p>
      <ul><li>Punkte auf jeden Kauf</li><li>Partner-Boni</li><li>Flexible Einlösung</li></ul>
      <a href="https://www.americanexpress.com/de/?ref=monvesto" target="_blank" rel="nofollow sponsored" class="btn-affiliate">Zur Amex Gold →</a>
    </div>
    <div class="type-card">
      <span class="type-tag type-tag--blue">Reise</span>
      <h3>Für Vielflieger</h3>
      <p>Meilen sammeln, Lounge-Zugang und umfassende Reiseversicherungen.</p>
      <ul><li>Meilen auf Flüge</li><li>Lounge-Zugang</li><li>Reiseversicherung</li></ul>
      <a href="https://www.miles-and-more.com/kreditkarte/?ref=monvesto" target="_blank" rel="nofollow sponsored" class="btn-affiliate">Zur Miles & More →</a>
    </div>
    <div class="type-card">
      <span class="type-tag type-tag--purple">Studenten</span>
      <h3>Für Studenten</h3>
      <p>Kostenloses Girokonto + Kreditkarte, weltweit günstig abheben.</p>
      <ul><li>Kostenloses Konto</li><li>Weltweit abheben</li><li>Einfache App</li></ul>
      <a href="https://www.dkb.de/?ref=monvesto" target="_blank" rel="nofollow sponsored" class="btn-affiliate">Zur DKB Visa →</a>
    </div>
    <div class="type-card">
      <span class="type-tag">Alltag</span>
      <h3>Für den Alltag</h3>
      <p>Payback-Punkte bei REWE, dm und Co. – die unsichtbare Ersparnis.</p>
      <ul><li>Payback-Punkte</li><li>Partner-Vorteile</li><li>0 € Jahresgebühr</li></ul>
      <a href="https://www.payback.de/karte/?ref=monvesto" target="_blank" rel="nofollow sponsored" class="btn-affiliate">Zur Payback Visa →</a>
    </div>
  </div>
</section>

<hr class="divider" />

<!-- ── VERGLEICHSTABELLE ── -->
<section class="section" style="background:var(--bg);" id="alle-karten">
  <div class="section-label">Vergleich</div>
  <h2 class="section-title">Alle Kreditkarten im Überblick</h2>

  <div class="table-responsive">
    <table class="compare-table mt-32">
      <thead>
        <tr>
          <th>Karte</th>
          <th>Jahresgebühr</th>
          <th>Cashback</th>
          <th>FX-frei</th>
          <th>Versicherung</th>
          <th>Geeignet für</th>
          <th></th>
        </tr>
      </thead>
      <div class="pick-list mt-32">
  <tbody>
  <?php foreach ($kreditkartenAnbieter as $anbieter): ?>
    <tr>
      <td>
        <strong><?= e($anbieter['table_name']) ?></strong><br>
        <small><?= e($anbieter['type']) ?></small>
      </td>

      <td>
        <span class="tag <?= e($anbieter['annual_fee_class']) ?>">
          <?= e($anbieter['annual_fee']) ?>
        </span>
      </td>

      <td>
        <?php if (($anbieter['table_cashback_class'] ?? '') === 'check'): ?>
          <span class="check">✓</span>
        <?php elseif (($anbieter['table_cashback_class'] ?? '') === 'dash'): ?>
          <span class="dash">–</span>
        <?php else: ?>
          <span class="tag <?= e($anbieter['table_cashback_class'] ?? '') ?>">
            <?= e($anbieter['table_cashback'] ?? '–') ?>
          </span>
        <?php endif; ?>
      </td>

      <td>
        <?php if (($anbieter['table_fx_class'] ?? '') === 'check'): ?>
          <span class="check">✓</span>
        <?php elseif (($anbieter['table_fx_class'] ?? '') === 'dash'): ?>
          <span class="dash">–</span>
        <?php else: ?>
          <span class="tag <?= e($anbieter['table_fx_class'] ?? '') ?>">
            <?= e($anbieter['table_fx'] ?? '–') ?>
          </span>
        <?php endif; ?>
      </td>

      <td>
        <?php if (($anbieter['table_insurance_class'] ?? '') === 'check'): ?>
          <span class="check">✓</span>
        <?php elseif (($anbieter['table_insurance_class'] ?? '') === 'dash'): ?>
          <span class="dash">–</span>
        <?php else: ?>
          <span class="tag <?= e($anbieter['table_insurance_class'] ?? '') ?>">
            <?= e($anbieter['table_insurance'] ?? '–') ?>
          </span>
        <?php endif; ?>
      </td>

      <td>
        <span class="tag"><?= e($anbieter['suitable_for']) ?></span>
      </td>

      <td>
        <a
          href="<?= e($anbieter['url']) ?>"
          target="_blank"
          rel="nofollow sponsored"
          class="btn-affiliate"
          style="font-size:12px;padding:7px 12px;"
        >
          <?= e($anbieter['table_button']) ?>
        </a>
      </td>
    </tr>
  <?php endforeach; ?>
</tbody>
</div>
    </table>
  </div>
</section>

<hr class="divider" />

<section class="cta-banner">
  <h2>Alle Kreditkarten. Ein Überblick. Mit Monvesto.</h2>
  <p>Verbinde deine Kreditkarten und behalte Ausgaben, Limits und Cashback jederzeit im Blick.</p>
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
      <div class="faq-q">Welche Kreditkarte ist wirklich kostenlos? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Dauerhaft kostenlose Kreditkarten ohne Jahresgebühr sind z. B. die Barclays Visa und die DKB Visa. Wichtig: Manche Karten sind nur kostenlos bei Einhaltung bestimmter Bedingungen wie einem Mindesteinsatz pro Monat. Barclays stellt keine Bedingungen.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Lohnt sich eine Kreditkarte mit Jahresgebühr? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Das hängt vom Nutzungsverhalten ab. Wer viel reist oder häufig einkauft, kann mit Cashback, Meilen oder Versicherungsleistungen die Jahresgebühr schnell wieder reinholen. Für reine Gelegenheitsnutzer lohnt sich meist eine kostenlose Karte.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Was sind Fremdwährungsgebühren und wie vermeide ich sie? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Fremdwährungsgebühren fallen an, wenn du im Ausland oder in einer Fremdwährung zahlst – meist 1,5 bis 2 % des Betrags. Karten wie die Barclays Visa oder DKB Visa erheben diese Gebühren nicht, was bei häufigen Auslandsreisen erheblich Geld spart.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Kann ich mehrere Kreditkarten gleichzeitig besitzen? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Ja, das ist völlig üblich. Viele Nutzer kombinieren z. B. eine kostenlose Reisekarte (Barclays) mit einer Cashback-Karte (Amex Gold) für den Alltag. Das maximiert die Vorteile beider Karten ohne Mehrkosten.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Wie lange dauert die Beantragung einer Kreditkarte? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Online-Anträge sind in wenigen Minuten ausgefüllt. Die Identifizierung erfolgt per Video-Ident oder PostIdent. Die physische Karte kommt meist innerhalb von 5–10 Werktagen. Digitale Karten stehen oft sofort nach Genehmigung zur Verfügung.</div>
    </div>
  </div>
</section>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>