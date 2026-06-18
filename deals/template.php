<?php
/**
 * Monvesto /deals/ — Template für neue Deal-Landingpages
 * -------------------------------------------------------
 * Kopiere diese Datei und benenne sie nach dem Deal:
 *   z.B. c24-girokonto.php, ing-tagesgeld.php, scalable-depot.php
 *
 * Passe nur den KONFIGURATIONS-Block unten an — der Rest bleibt gleich.
 */

// ============================================================
// KONFIGURATION — hier alles anpassen
// ============================================================

$deal = [

    // Seiten-Meta
    'meta_title'       => 'Kostenloses Girokonto | Monvesto',
    'meta_description' => 'Jetzt kostenloses Girokonto eröffnen — dauerhaft ohne Kontoführungsgebühren.',

    // Partner
    'partner_name'     => 'C24 Bank',           // Angezeigter Name
    'partner_logo'     => '',                    // Pfad zu Logo, z.B. '/assets/partners/c24.png'
                                                 // Leer lassen → partner_name wird als Text angezeigt

    // Badge (optional, leer lassen = kein Badge)
    'badge'            => 'Monvesto Empfehlung',

    // Inhalt
    'headline'         => 'Kostenloses Girokonto — dauerhaft 0 €',
    'subline'          => 'Kein Mindestgeldeingang, keine versteckten Gebühren. Einfach ein smartes Konto für den Alltag.',

    // Vorteile (3–4 Punkte, kurz und konkret)
    'benefits'         => [
        'Dauerhaft kostenlose Kontoführung',
        'Kostenlose Visa-Karte inklusive',
        'Gratis Abheben in ganz Europa',
        'Bis zu 100 € Startguthaben möglich',
    ],

    // CTA
    'cta_text'         => 'Jetzt Konto eröffnen →',
    'cta_url'          => '/go/c24',             // Dein bestehender /go/ Redirect

    // Trust-Zeile (HTML erlaubt)
    'trust_text'       => 'Empfohlen von <strong>Monvesto</strong> · Unabhängige Empfehlung · Werbung mit Kennzeichnung',

    // Disclaimer (optional, leer lassen = nicht angezeigt)
    'disclaimer'       => '* Werbung. Die Konditionen können sich ändern. Alle Angaben ohne Gewähr. Stand: ' . date('m/Y') . '.',

    // UTM-Tracking automatisch anhängen? (true/false)
    // Der CTA-URL bekommt dann ?utm_source=qr&utm_medium=flyer&utm_campaign=CAMPAIGN_ID
    'utm_auto'         => true,

];

// UTM-Kampagnen-ID aus URL-Parameter lesen (z.B. ?c=messe-hamburg)
$campaign_id = isset($_GET['c']) ? htmlspecialchars($_GET['c'], ENT_QUOTES) : 'deal';

// CTA-URL mit UTM aufbauen
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

    <!-- Kein Indexieren — Deal-Pages sollen nicht in Google erscheinen -->
    <meta name="robots" content="noindex, nofollow">

    <!-- Monvesto Basis-Styles + Deal-Styles -->
    <link rel="stylesheet" href="/assets/style.css">
    <link rel="stylesheet" href="/deals/deals.css">

    <!-- Favicon -->
    <link rel="icon" href="/assets/favicon.ico">
</head>
<body>

<div class="deal-page">

    <!-- Monvesto Branding -->
    <header class="deal-header">
        <a href="https://monvesto.de" class="logo" rel="noopener">
            <span class="logo-dot"></span>
            Monvesto
        </a>
    </header>

    <!-- Deal Card -->
    <main class="deal-card">

        <!-- Partner -->
        <div class="deal-partner-logo">
            <?php if (!empty($deal['partner_logo'])): ?>
                <img src="<?= htmlspecialchars($deal['partner_logo']) ?>"
                     alt="<?= htmlspecialchars($deal['partner_name']) ?> Logo">
            <?php else: ?>
                <p class="deal-partner-name"><?= htmlspecialchars($deal['partner_name']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Badge -->
        <?php if (!empty($deal['badge'])): ?>
            <span class="deal-badge"><?= htmlspecialchars($deal['badge']) ?></span>
        <?php endif; ?>

        <!-- Headline -->
        <h1 class="deal-headline"><?= htmlspecialchars($deal['headline']) ?></h1>
        <p class="deal-subline"><?= htmlspecialchars($deal['subline']) ?></p>

        <div class="deal-divider"></div>

        <!-- Benefits -->
        <?php if (!empty($deal['benefits'])): ?>
            <ul class="deal-benefits">
                <?php foreach ($deal['benefits'] as $benefit): ?>
                    <li><?= htmlspecialchars($benefit) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- CTA -->
        <a href="<?= htmlspecialchars($cta_url) ?>"
           class="deal-cta"
           target="_blank"
           rel="noopener sponsored">
            <?= htmlspecialchars($deal['cta_text']) ?>
        </a>

        <!-- Trust -->
        <?php if (!empty($deal['trust_text'])): ?>
            <p class="deal-trust"><?= $deal['trust_text'] ?></p>
        <?php endif; ?>

    </main>

    <!-- Disclaimer -->
    <?php if (!empty($deal['disclaimer'])): ?>
        <p class="deal-footer-note"><?= htmlspecialchars($deal['disclaimer']) ?></p>
    <?php endif; ?>

</div>

</body>
</html>