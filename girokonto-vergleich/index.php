<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Girokonto Vergleich 2026 – Kostenlos, mit Zinsen & Jugendkonten | Monvesto</title>
  <meta name="description" content="Finde das beste Girokonto für deinen Alltag – ob kostenloses Konto, Konto mit Zinsen oder das erste Konto für Jugendliche." />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://monvesto.de/girokonto-vergleich/" />
  <link rel="stylesheet" href="../assets/style.css" />
  <link rel="stylesheet" href="../assets/vergleich-components.css" />
  <script type="application/ld+json">{"@context":"https://schema.org","@type":"FAQPage","mainEntity":[{"@type":"Question","name":"Welches Girokonto ist wirklich dauerhaft kostenlos?","acceptedAnswer":{"@type":"Answer","text":"DKB und ING bieten dauerhaft kostenlose Girokonten ohne Mindestgeldeingang."}},{"@type":"Question","name":"Ab welchem Alter kann man ein Jugendkonto eröffnen?","acceptedAnswer":{"@type":"Answer","text":"Die Sparkasse bietet Konten bereits ab 7 Jahren an, DKB und viele andere Direktbanken ab 14 Jahren."}}]}</script>
</head>
<body>

<?php
$girokontoAnbieter = require $_SERVER['DOCUMENT_ROOT'] . '/anbieter/girokonto-anbieter.php';

usort($girokontoAnbieter, function ($a, $b) {
  return ($a['rank'] ?? 999) <=> ($b['rank'] ?? 999);
});

$topGirokontoLimit = 3;
$jugendkontoLimit = 3;

$topGirokontoAnbieter = array_values(array_filter($girokontoAnbieter, function ($anbieter) {
  return !empty($anbieter['show_top']) && ($anbieter['category'] ?? 'girokonto') === 'girokonto';
}));

$topGirokontoAnbieter = array_slice($topGirokontoAnbieter, 0, $topGirokontoLimit);

$jugendkontoAnbieter = array_values(array_filter($girokontoAnbieter, function ($anbieter) {
  return ($anbieter['category'] ?? '') === 'jugendkonto';
}));

usort($jugendkontoAnbieter, function ($a, $b) {
  return ($a['youth_rank'] ?? $a['rank'] ?? 999) <=> ($b['youth_rank'] ?? $b['rank'] ?? 999);
});

$jugendkontoAnbieter = array_slice($jugendkontoAnbieter, 0, $jugendkontoLimit);

function e($value) {
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/nav.php'; ?>

<section class="hero hero-bg-green">
  <div class="hero-badge">Girokonto Vergleich 2026</div>
  <h1>Die besten Girokonten –<br><span class="highlight">kostenlos, mit Zinsen & für Jugendliche</span></h1>
  <p class="hero-sub">Finde das beste Girokonto für deinen Alltag – ob kostenloses Konto, Konto mit Zinsen oder das erste Konto für Jugendliche.</p>
  <div class="hero-actions">
    <a href="#vergleich" class="btn btn-primary btn-lg">Girokonten vergleichen</a>
    <a href="#jugendkonten" class="btn btn-secondary btn-lg">Jugendkonten ansehen</a>
  </div>
</section>

<div class="trust-bar">
  <div class="trust-item"><span class="trust-check">✓</span> Kostenlose Konten</div>
  <div class="trust-item"><span class="trust-check">✓</span> Konten mit Zinsen</div>
  <div class="trust-item"><span class="trust-check">✓</span> Jugendkonten ab 7 Jahren</div>
  <div class="trust-item"><span class="trust-check">✓</span> Inkl. Kreditkarte</div>
</div>

<!-- ── TOP PICKS GIROKONTEN ── -->
<section class="section" id="vergleich">
  <div class="section-label">Unsere Empfehlungen</div>
  <h2 class="section-title">Die besten Girokonten 2026</h2>
  <p class="section-intro">Bewertet nach Kontoführungsgebühr, Zinsen, Kreditkarte, App-Qualität und Gesamtpaket.</p>

  <div class="pick-list mt-32">
  <?php foreach ($topGirokontoAnbieter as $anbieter): ?>
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

  <!-- ── JUGENDKONTEN ── -->
  <div class="section-divider" id="jugendkonten">
    <span class="section-divider-label">
      <i class="ti ti-users"></i> Jugendkonten im Vergleich
    </span>
  </div>

  <p style="font-size:16px;color:var(--text-muted);line-height:1.7;margin-bottom:24px;">Das erste eigene Konto ist ein wichtiger Schritt. Die besten Jugendkonten sind kostenlos, einfach zu bedienen und helfen beim Erlernen von finanziellem Verantwortungsbewusstsein.</p>

  <div class="pick-list">
  <?php foreach ($jugendkontoAnbieter as $anbieter): ?>
    <div class="pick-card<?= !empty($anbieter['featured']) ? ' pick-card--featured' : '' ?>">
      <div class="pick-rank">#<?= e($anbieter['youth_rank'] ?? $anbieter['rank']) ?></div>

      <div class="pick-info">
        <?php if (!empty($anbieter['badge'])): ?>
          <span class="best-badge best-badge--amber"><?= e($anbieter['badge']) ?></span>
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
    <span><strong>Hinweis:</strong> Diese Seite enthält Affiliate-Links. Bei Kontoeröffnung über unsere Links erhalten wir eine Provision – für dich entstehen keine Mehrkosten. Unsere Bewertungen sind redaktionell unabhängig.</span>
  </div>
</section>

<hr class="divider" />

<!-- ── NUTZERTYPEN ── -->
<section class="section">
  <div class="section-label">Entscheidungshilfe</div>
  <h2 class="section-title">Welches Konto passt zu dir?</h2>

  <div class="type-grid mt-32">
    <div class="type-card type-card--featured">
      <span class="type-tag type-tag--green">Bestes Gesamtpaket</span>
      <h3>Alles inklusive</h3>
      <p>Kostenloses Konto, Kreditkarte und weltweites Abheben ohne Gebühren.</p>
      <ul><li>0 € Kontoführung</li><li>Mastercard inklusive</li><li>Weltweit abheben</li></ul>
      <a href="/go/check24/" target="_blank" rel="nofollow sponsored" class="btn-affiliate">Zum C24 Konto →</a>
    </div>
    <div class="type-card">
      <span class="type-tag type-tag--green">Zinsen + Konto</span>
      <h3>Konto mit Zinsen</h3>
      <p>Girokonto und bis zu 3,75 % Zinsen auf Guthaben in einer App.</p>
      <ul><li>3,75 % p.a.</li><li>Broker integriert</li><li>Visa Debit</li></ul>
      <a href="https://ref.trade.re/monvesto" target="_blank" rel="nofollow sponsored" class="btn-affiliate">Zu Trade Republic →</a>
    </div>
    <div class="type-card">
      <span class="type-tag type-tag--blue">Digital First</span>
      <h3>Moderne Neobank</h3>
      <p>Schlichtes Design, Unterkonten und Premium-Features auf Wunsch.</p>
      <ul><li>Spaces / Unterkonten</li><li>Echtzeit-Push</li><li>Upgrade möglich</li></ul>
      <a href="https://n26.com/de-de/?ref=monvesto" target="_blank" rel="nofollow sponsored" class="btn-affiliate">Zu N26 →</a>
    </div>
    <div class="type-card">
      <span class="type-tag type-tag--amber">Jugendkonto</span>
      <h3>Erstes Konto</h3>
      <p>Kostenloses Konto für Schüler ab 14 Jahren mit Visa-Karte.</p>
      <ul><li>Ab 14 Jahren</li><li>0 € dauerhaft</li><li>Visa inklusive</li></ul>
      <a href="https://www.dkb.de/jugendkonto/?ref=monvesto" target="_blank" rel="nofollow sponsored" class="btn-affiliate">Zum DKB Jugendkonto →</a>
    </div>
    <div class="type-card">
      <span class="type-tag">Filiale</span>
      <h3>Persönliche Beratung</h3>
      <p>Klassische Großbank mit Filialnetz und umfassendem Produktangebot.</p>
      <ul><li>Filialnetz vorhanden</li><li>Persönliche Beratung</li><li>Komplettpaket</li></ul>
      <a href="https://www.commerzbank.de/girokonto/?ref=monvesto" target="_blank" rel="nofollow sponsored" class="btn-affiliate">Zur Commerzbank →</a>
    </div>
  </div>
</section>

<hr class="divider" />

<!-- ── VERGLEICHSTABELLE ── -->
<section class="section" style="background:var(--bg);">
  <div class="section-label">Vergleich</div>
  <h2 class="section-title">Alle Girokonten im direkten Überblick</h2>

  <div class="table-responsive">
    <table class="compare-table mt-32">
      <thead>
        <tr>
          <th>Anbieter</th>       
          <th>Kontoart</th>
          <th>Gebühr</th>
          <th>Zinsen</th>
          <th>Kreditkarte</th>
          <th>Abheben</th>
          <th>Geeignet für</th>
          <th>App</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
  <?php foreach ($girokontoAnbieter as $anbieter): ?>
    <tr>
      <td>
        <strong><?= e($anbieter['table_name']) ?></strong><br>
        <small><?= e($anbieter['type']) ?></small>
      </td>

      <td>
        <?php if (($anbieter['category'] ?? 'girokonto') === 'jugendkonto'): ?>
          <span class="tag tag-amber">Jugendkonto</span>
        <?php else: ?>
          <span class="tag tag-green">Girokonto</span>
        <?php endif; ?>
      </td>

      <td><span class="tag <?= e($anbieter['fee_class']) ?>"><?= e($anbieter['fee']) ?></span></td>

      <td>
        <?php if (!empty($anbieter['interest']) && $anbieter['interest'] !== '–'): ?>
          <span class="tag tag-green"><?= e($anbieter['interest']) ?></span>
        <?php else: ?>
          <span class="dash">–</span>
        <?php endif; ?>
      </td>

      <td><span class="check">✓</span> <?= e($anbieter['card']) ?></td>
      <td><span class="tag <?= e($anbieter['withdraw_class']) ?>"><?= e($anbieter['withdraw']) ?></span></td>
      <td><span class="tag"><?= e($anbieter['suitable_for']) ?></span></td>
      <td><span class="tag <?= e($anbieter['app_class']) ?>"><?= e($anbieter['app']) ?></span></td>

      <td>
        <a href="<?= e($anbieter['url']) ?>" target="_blank" rel="nofollow sponsored" class="btn-affiliate" style="font-size:12px;padding:7px 12px;">
          <?= e($anbieter['table_button']) ?>
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
  <h2>Alle Konten. Ein Überblick. Mit Monvesto.</h2>
  <p>Verbinde deine Girokonten und behalte Guthaben, Ausgaben und Gesamtvermögen jederzeit im Blick.</p>
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
      <div class="faq-q">Welches Girokonto ist wirklich dauerhaft kostenlos? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">DKB und ING bieten dauerhaft kostenlose Girokonten ohne Mindestgeldeingang. N26 Standard ist ebenfalls kostenlos. Bei Commerzbank entfallen Gebühren nur mit monatlichem Mindesteingang von 700 €. Achte stets auf das Kleingedruckte.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Ab welchem Alter kann man ein Jugendkonto eröffnen? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Die Sparkasse bietet Konten bereits ab 7 Jahren an, DKB und viele andere Direktbanken ab 14 Jahren. Die meisten Neobanken setzen die Altersgrenze bei 18 Jahren an. Bei Minderjährigen ist meist die Zustimmung der Eltern erforderlich.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Was ist der Unterschied zwischen einer Debitkarte und einer Kreditkarte? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Eine Debitkarte bucht Zahlungen direkt vom Girokonto ab – kein Kreditrahmen, kein Schuldenrisiko. Eine klassische Kreditkarte ermöglicht das Zahlen auf Kredit, der monatlich abgerechnet wird. Für Jugendliche sind Debitkarten daher besser geeignet.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Kann ich mein Girokonto mit einem Broker oder Tagesgeld kombinieren? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Ja – das ist sogar empfehlenswert. Trade Republic kombiniert Girokonto, Tagesgeld (3,75 % Zinsen) und Broker in einer App. DKB und ING lassen sich einfach mit einem separaten Tagesgeldkonto verknüpfen.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Lohnt sich ein Konto mit Zinsen auf dem Girokonto? <span class="faq-arrow">▾</span></div>
      <div class="faq-a">Wenn du regelmäßig größere Beträge auf dem Girokonto liegen hast, kann sich ein verzinstes Konto wie Trade Republic lohnen. Bei 10.000 € Guthaben und 3,75 % p.a. sind das rund 375 € Zinsen vor Steuern im Jahr – für ein kostenloses Konto ein echter Mehrwert.</div>
    </div>
  </div>
</section>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>