<?php
/**
 * Monvesto /deals/c24-girokonto.php
 * Deal-Landingpage: C24 Kostenloses Girokonto
 */

$deal = [
    'meta_title'       => 'Kostenloses Girokonto bei C24 | Monvesto',
    'meta_description' => 'Dauerhaft kostenlos — das C24 Girokonto ohne Kontoführungsgebühren, mit gratis Visa-Karte.',

    'partner_name'     => 'C24 Bank',
    'partner_logo'     => '/assets/partners/c24.png',   // Logo hier ablegen, sonst wird der Name angezeigt

    'badge'            => 'Monvesto Empfehlung',

    'headline'         => 'Kostenloses Girokonto — dauerhaft 0 €',
    'subline'          => 'Kein Mindestgeldeingang, keine versteckten Gebühren. Einfach ein smartes Konto für deinen Alltag.',

    'benefits'         => [
        'Dauerhaft 0 € Kontoführungsgebühren',
        'Kostenlose Visa-Debitkarte inklusive',
        'Gratis Bargeld abheben in ganz Europa',
        'Schnelle Online-Eröffnung in unter 10 Minuten',
    ],

    'cta_text'         => 'Jetzt Konto eröffnen →',
    'cta_url'          => '/go/c24',

    'trust_text'       => 'Empfohlen von <strong>Monvesto</strong> · Werbung mit Affiliate-Link',
    'disclaimer'       => '* Werbung. Alle Angaben ohne Gewähr. Konditionen können sich ändern. Stand: ' . date('m/Y') . '.',

    'utm_auto'         => true,
];

$campaign_id = isset($_GET['c']) ? htmlspecialchars($_GET['c'], ENT_QUOTES) : 'c24-girokonto';

$cta_url = $deal['cta_url'];
if ($deal['utm_auto']) {
    $separator = (strpos($cta_url, '?') !== false) ? '&' : '?';
    $cta_url .= $separator . 'utm_source=qr&utm_medium=flyer&utm_campaign=' . urlencode($campaign_id);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($deal['meta_title']) ?></title>
    <meta name="description" content="<?= htmlspecialchars($deal['meta_description']) ?>">
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="/assets/style.css">
    <link rel="stylesheet" href="/deals/deals.css">
    <link rel="icon" href="/assets/favicon.ico">
</head>
<body>

<div class="deal-page">

    <header class="deal-header">
        <a href="https://monvesto.de" class="logo" rel="noopener">
            <span class="logo-dot"></span>
            Monvesto
        </a>
    </header>

    <main class="deal-card">

        <div class="deal-partner-logo">
            <?php if (!empty($deal['partner_logo']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $deal['partner_logo'])): ?>
                <img src="<?= htmlspecialchars($deal['partner_logo']) ?>"
                     alt="<?= htmlspecialchars($deal['partner_name']) ?> Logo">
            <?php else: ?>
                <p class="deal-partner-name"><?= htmlspecialchars($deal['partner_name']) ?></p>
            <?php endif; ?>
        </div>

        <?php if (!empty($deal['badge'])): ?>
            <span class="deal-badge"><?= htmlspecialchars($deal['badge']) ?></span>
        <?php endif; ?>

        <h1 class="deal-headline"><?= htmlspecialchars($deal['headline']) ?></h1>
        <p class="deal-subline"><?= htmlspecialchars($deal['subline']) ?></p>

        <div class="deal-divider"></div>

        <ul class="deal-benefits">
            <?php foreach ($deal['benefits'] as $benefit): ?>
                <li><?= htmlspecialchars($benefit) ?></li>
            <?php endforeach; ?>
        </ul>

        <a href="<?= htmlspecialchars($cta_url) ?>"
           class="deal-cta"
           target="_blank"
           rel="noopener sponsored">
            <?= htmlspecialchars($deal['cta_text']) ?>
        </a>

        <p class="deal-trust"><?= $deal['trust_text'] ?></p>

    </main>

    <p class="deal-footer-note"><?= htmlspecialchars($deal['disclaimer']) ?></p>

</div>

</body>
</html>