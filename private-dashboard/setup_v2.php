<?php
require_once __DIR__ . '/config/bootstrap.php';

$db = get_db();
$log = [];

$tables = [
'einnahmen' => "CREATE TABLE IF NOT EXISTS `einnahmen` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `bezeichnung` VARCHAR(150) NOT NULL,
    `betrag`      DECIMAL(10,2) NOT NULL DEFAULT 0,
    `person`      ENUM('Marcel','Kim','Beide') NOT NULL DEFAULT 'Marcel',
    `kategorie`   VARCHAR(100) DEFAULT NULL,
    `turnus`      ENUM('Monatlich','Jährlich','Einmalig') NOT NULL DEFAULT 'Monatlich',
    `aktiv`       TINYINT(1) NOT NULL DEFAULT 1,
    `notiz`       TEXT DEFAULT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'ausgaben' => "CREATE TABLE IF NOT EXISTS `ausgaben` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `bezeichnung` VARCHAR(150) NOT NULL,
    `betrag`      DECIMAL(10,2) NOT NULL DEFAULT 0,
    `person`      ENUM('Marcel','Kim','Beide') NOT NULL DEFAULT 'Marcel',
    `kategorie`   VARCHAR(100) DEFAULT NULL,
    `turnus`      ENUM('Monatlich','Jährlich','Einmalig') NOT NULL DEFAULT 'Monatlich',
    `aktiv`       TINYINT(1) NOT NULL DEFAULT 1,
    `notiz`       TEXT DEFAULT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'verbindlichkeiten' => "CREATE TABLE IF NOT EXISTS `verbindlichkeiten` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `glaeubiger` VARCHAR(150) NOT NULL,
    `startsumme` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `restsumme`  DECIMAL(10,2) NOT NULL DEFAULT 0,
    `rate`       DECIMAL(10,2) NOT NULL DEFAULT 0,
    `notiz`      TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'ziele' => "CREATE TABLE IF NOT EXISTS `ziele` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ziel`           VARCHAR(200) NOT NULL,
    `kategorie`      VARCHAR(100) DEFAULT NULL,
    `startwert`      DECIMAL(10,2) NOT NULL DEFAULT 0,
    `zielwert`       DECIMAL(10,2) NOT NULL DEFAULT 0,
    `aktueller_wert` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `zieltermin`     DATE DEFAULT NULL,
    `kommentar`      TEXT DEFAULT NULL,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'investments' => "CREATE TABLE IF NOT EXISTS `investments` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `datum`       DATE NOT NULL,
    `bereich`     VARCHAR(100) NOT NULL,
    `einnahmeart` VARCHAR(100) DEFAULT NULL,
    `betrag`      DECIMAL(10,2) NOT NULL DEFAULT 0,
    `notiz`       TEXT DEFAULT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'immobilien' => "CREATE TABLE IF NOT EXISTS `immobilien` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `objekt_name`  VARCHAR(200) NOT NULL,
    `einzugsdatum` DATE DEFAULT NULL,
    `kaltmiete`    DECIMAL(10,2) NOT NULL DEFAULT 0,
    `nebenkosten`  DECIMAL(10,2) NOT NULL DEFAULT 0,
    `fixkosten`    DECIMAL(10,2) NOT NULL DEFAULT 0,
    `kreditkosten` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `kaution`      DECIMAL(10,2) NOT NULL DEFAULT 0,
    `mieter`       VARCHAR(150) DEFAULT NULL,
    `bemerkung`    TEXT DEFAULT NULL,
    `aktiv`        TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'checkliste_zahlungen' => "CREATE TABLE IF NOT EXISTS `checkliste_zahlungen` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `bezeichnung` VARCHAR(150) NOT NULL,
    `betrag`      DECIMAL(10,2) NOT NULL DEFAULT 0,
    `person`      ENUM('Marcel','Kim','Beide') NOT NULL DEFAULT 'Marcel',
    `kategorie`   VARCHAR(100) DEFAULT NULL,
    `turnus`      ENUM('Monatlich','Quartalsweise','Jährlich') NOT NULL DEFAULT 'Monatlich',
    `sort_order`  INT NOT NULL DEFAULT 0,
    `aktiv`       TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'checkliste_status' => "CREATE TABLE IF NOT EXISTS `checkliste_status` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `zahlung_id` INT UNSIGNED NOT NULL,
    `monat`      DATE NOT NULL,
    `status`     TINYINT(1) NOT NULL DEFAULT 0,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `zahlung_monat` (`zahlung_id`, `monat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'mieten_checkliste' => "CREATE TABLE IF NOT EXISTS `mieten_checkliste` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `bezeichnung`   VARCHAR(200) NOT NULL,
    `immobilien_id` INT UNSIGNED DEFAULT NULL,
    `typ`           ENUM('Kaltmiete','Nebenkosten','Verwaltung') NOT NULL DEFAULT 'Kaltmiete',
    `sort_order`    INT NOT NULL DEFAULT 0,
    `aktiv`         TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'mieten_status' => "CREATE TABLE IF NOT EXISTS `mieten_status` (
    `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `miete_id`  INT UNSIGNED NOT NULL,
    `monat`     DATE NOT NULL,
    `status`    TINYINT(1) NOT NULL DEFAULT 0,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `miete_monat` (`miete_id`, `monat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

// Tabellen anlegen
foreach ($tables as $name => $sql) {
    try {
        $db->exec($sql);
        $log[] = ['ok', "Tabelle <b>$name</b> OK"];
    } catch (PDOException $e) {
        $log[] = ['err', "Fehler bei <b>$name</b>: " . htmlspecialchars($e->getMessage())];
    }
}

// Beispieldaten nur einfügen wenn Tabellen leer sind
$inserts = [];

if ((int)$db->query('SELECT COUNT(*) FROM immobilien')->fetchColumn() === 0) {
    $db->exec("INSERT INTO `immobilien` (objekt_name, einzugsdatum, kaltmiete, nebenkosten, fixkosten, kreditkosten, kaution, mieter) VALUES
        ('Friedrichshagener Str. 13 - WHG 9', '2023-12-01', 450, 200, 200, 913.50, 800, 'Claudia Waber')");
    $inserts[] = 'Immobilien-Beispieldaten eingefügt';
}

if ((int)$db->query('SELECT COUNT(*) FROM ziele')->fetchColumn() === 0) {
    $db->exec("INSERT INTO `ziele` (ziel, kategorie, startwert, zielwert, aktueller_wert, zieltermin, kommentar) VALUES
        ('Vermögen 100k€', 'Finanzen', 0, 100000, 0, '2027-09-01', 'Monatlich tracken'),
        ('Gewicht 65kg', 'Gesundheit', 84, 65, 72, '2026-09-01', 'Mehr Sport & Ernährung'),
        ('Monvesto: 500€/Monat passiv', 'Investments', 0, 500, 180, '2026-03-01', 'Grid EA + Affiliate'),
        ('Immobilien: 2 Objekte', 'Immobilien', 0, 2, 1, '2027-09-01', '2. Objekt suchen'),
        ('100 Abonnenten Roboforex', 'Business', 0, 100, 1, '2026-02-01', 'Content-Strategie'),
        ('Notgroschen 3 Monate', 'Finanzen', 0, 9000, 6200, '2024-09-01', '3x monatl. Ausgaben'),
        ('Krypto Portfolio 5k€', 'Investments', 500, 5000, 2100, '2026-03-01', 'DCA Strategie')");
    $inserts[] = 'Ziele-Beispieldaten eingefügt';
}

if ((int)$db->query('SELECT COUNT(*) FROM einnahmen')->fetchColumn() === 0) {
    $db->exec("INSERT INTO `einnahmen` (bezeichnung, betrag, person, kategorie, turnus) VALUES
        ('Gehalt Marcel', 2115.00, 'Marcel', 'Gehalt', 'Monatlich'),
        ('Gehalt Marcel Nebenjob', 400.00, 'Marcel', 'Gehalt', 'Monatlich'),
        ('Rasenmähen Hundeschule', 100.00, 'Marcel', 'Nebeneinkommen', 'Monatlich'),
        ('Kaltmiete Hemeringen', 450.00, 'Marcel', 'Immobilien', 'Monatlich'),
        ('Gehalt Kim Rewe', 2050.00, 'Kim', 'Gehalt', 'Monatlich'),
        ('Taschengeld Kim', 100.00, 'Kim', 'Sonstiges', 'Monatlich')");
    $inserts[] = 'Einnahmen-Beispieldaten eingefügt';
}

if ((int)$db->query('SELECT COUNT(*) FROM ausgaben')->fetchColumn() === 0) {
    $db->exec("INSERT INTO `ausgaben` (bezeichnung, betrag, person, kategorie, turnus) VALUES
        ('Wohnung', 600.00, 'Marcel', 'Wohnen', 'Monatlich'),
        ('Strom EON', 46.00, 'Marcel', 'Wohnen', 'Monatlich'),
        ('Internet HTP', 39.95, 'Marcel', 'Wohnen', 'Monatlich'),
        ('Vodafone Marcel', 16.99, 'Marcel', 'Kommunikation', 'Monatlich'),
        ('GEZ Marcel', 18.36, 'Marcel', 'Sonstiges', 'Monatlich'),
        ('Katzenfutter + Streu', 60.00, 'Marcel', 'Haustiere', 'Monatlich'),
        ('Netflix', 11.99, 'Marcel', 'Unterhaltung', 'Monatlich'),
        ('Xbox Gamepass Ultimate', 18.99, 'Marcel', 'Unterhaltung', 'Monatlich'),
        ('Spotify Marcel', 11.99, 'Marcel', 'Unterhaltung', 'Monatlich'),
        ('Amazon Prime', 11.99, 'Marcel', 'Unterhaltung', 'Monatlich'),
        ('Axa Haftpflicht/Hausrat', 19.10, 'Marcel', 'Versicherung', 'Monatlich'),
        ('Rechtschutz Roland', 30.34, 'Marcel', 'Versicherung', 'Monatlich'),
        ('KFZ Versicherung Marcel', 48.80, 'Marcel', 'KFZ', 'Monatlich'),
        ('KFZ Steuer Marcel', 7.86, 'Marcel', 'KFZ', 'Monatlich'),
        ('Tanken', 60.00, 'Marcel', 'KFZ', 'Monatlich'),
        ('Domainkosten', 1.98, 'Marcel', 'Business', 'Monatlich'),
        ('Webhosting', 7.99, 'Marcel', 'Business', 'Monatlich'),
        ('Kontoführung Marcel', 1.90, 'Marcel', 'Bank', 'Monatlich'),
        ('VL ETF Sparplan', 25.00, 'Marcel', 'Investments', 'Monatlich'),
        ('Rückstand Consors Finanz', 150.00, 'Marcel', 'Schulden', 'Monatlich'),
        ('Abtrag Wohnung (inkl. Vers.)', 913.88, 'Marcel', 'Immobilien', 'Monatlich'),
        ('NK + Verwaltung Hemeringen', 200.00, 'Marcel', 'Immobilien', 'Monatlich'),
        ('Grundsteuer B Hemeringen', 4.93, 'Marcel', 'Immobilien', 'Monatlich'),
        ('Kreditrate Sigma', 381.00, 'Marcel', 'Schulden', 'Monatlich'),
        ('Gitti & Frank', 500.00, 'Marcel', 'Schulden', 'Monatlich'),
        ('Miete Kim', 435.00, 'Kim', 'Wohnen', 'Monatlich'),
        ('Verpflegung Arbeit', 60.00, 'Kim', 'Lebensmittel', 'Monatlich'),
        ('Vodafone Kim', 25.00, 'Kim', 'Kommunikation', 'Monatlich'),
        ('Spotify Kim', 10.99, 'Kim', 'Unterhaltung', 'Monatlich'),
        ('Nägelmachen', 40.00, 'Kim', 'Pflege', 'Monatlich'),
        ('Kontaktlinsen', 9.50, 'Kim', 'Gesundheit', 'Monatlich'),
        ('Pille', 6.00, 'Kim', 'Gesundheit', 'Monatlich'),
        ('KFZ Versicherung Kim', 105.00, 'Kim', 'KFZ', 'Monatlich'),
        ('KFZ Steuer Kim', 17.20, 'Kim', 'KFZ', 'Monatlich'),
        ('Tarobank Kredit', 235.60, 'Kim', 'Schulden', 'Monatlich'),
        ('Autokredit Kim', 320.00, 'Kim', 'Schulden', 'Monatlich'),
        ('Kontoführung Kim', 2.00, 'Kim', 'Bank', 'Monatlich'),
        ('Zigaretten', 204.00, 'Kim', 'Sonstiges', 'Monatlich')");
    $inserts[] = 'Ausgaben-Beispieldaten eingefügt';
}

if ((int)$db->query('SELECT COUNT(*) FROM verbindlichkeiten')->fetchColumn() === 0) {
    $db->exec("INSERT INTO `verbindlichkeiten` (glaeubiger, startsumme, restsumme, rate, notiz) VALUES
        ('Ferratum', 1800.00, 2181.00, 100.00, NULL),
        ('SigmaBank', 6422.00, 2100.00, 380.00, NULL),
        ('Robo Verluste', 0.00, 47593.00, 0.00, 'Trading-Verluste'),
        ('Emma Matratze', 380.00, 380.00, 50.00, NULL),
        ('Rene', 6000.00, 6000.00, 500.00, NULL),
        ('Mutter', 12000.00, 11900.00, 100.00, NULL),
        ('Gitti & Frank', 5300.00, 5300.00, 500.00, NULL),
        ('Consors Finanz', 900.00, 900.00, 150.00, NULL)");
    $inserts[] = 'Verbindlichkeiten-Beispieldaten eingefügt';
}

if ((int)$db->query('SELECT COUNT(*) FROM investments')->fetchColumn() === 0) {
    $db->exec("INSERT INTO `investments` (datum, bereich, einnahmeart, betrag, notiz) VALUES
        ('2024-01-01', 'Grid EA', 'Trading-Gewinn', 320.00, 'Monvesto Grid EA V1.2'),
        ('2024-02-01', 'Grid EA', 'Trading-Gewinn', 285.00, 'Monvesto Grid EA V1.2'),
        ('2024-03-01', 'P2P', 'Zinsen', 48.00, 'Bondora Go & Grow'),
        ('2024-03-01', 'Grid EA', 'Trading-Gewinn', 410.00, 'Monvesto Grid EA V1.3'),
        ('2024-04-01', 'Affiliate', 'Provision', 95.00, 'Monvesto Affiliate'),
        ('2024-04-01', 'Tagesgeld', 'Zinsen', 32.00, 'Tagesgeld 3,75% p.a.'),
        ('2024-04-01', 'Grid EA', 'Trading-Gewinn', 375.00, 'Monvesto Grid EA V1.3'),
        ('2024-05-01', 'Krypto', 'Kursgewinn', 180.00, 'BTC Teilverkauf'),
        ('2024-05-01', 'P2P', 'Zinsen', 52.00, 'Bondora + Mintos'),
        ('2024-05-01', 'Affiliate', 'Provision', 145.00, 'Monvesto Affiliate'),
        ('2024-05-01', 'Grid EA', 'Trading-Gewinn', 430.00, 'Monvesto Grid EA V1.3'),
        ('2024-06-01', 'Affiliate', 'Provision', 180.00, 'Monvesto Affiliate'),
        ('2024-06-01', 'Tagesgeld', 'Zinsen', 35.00, 'Tagesgeld'),
        ('2024-06-01', 'Grid EA', 'Trading-Gewinn', 210.00, 'Monvesto Grid EA V1.3')");
    $inserts[] = 'Investment-Beispieldaten eingefügt';
}

if ((int)$db->query('SELECT COUNT(*) FROM checkliste_zahlungen')->fetchColumn() === 0) {
    $db->exec("INSERT INTO `checkliste_zahlungen` (bezeichnung, betrag, person, kategorie, turnus, sort_order) VALUES
        ('Miete Buchmeiersweg 6', 600.00, 'Marcel', 'Wohnen', 'Monatlich', 1),
        ('KFZ Versicherung Marcel', 48.80, 'Marcel', 'KFZ', 'Monatlich', 2),
        ('HTP Internet Marcel', 39.95, 'Marcel', 'Kommunikation', 'Monatlich', 3),
        ('Strom EON Marcel', 46.00, 'Marcel', 'Wohnen', 'Monatlich', 4),
        ('Roland Rechtsschutzversicherung', 30.34, 'Marcel', 'Versicherung', 'Quartalsweise', 5),
        ('GEZ Rundfunkbeitrag Marcel', 18.36, 'Marcel', 'Sonstiges', 'Quartalsweise', 6),
        ('Vodafone Marcel', 16.99, 'Marcel', 'Kommunikation', 'Monatlich', 7),
        ('Amazon Prime', 11.99, 'Marcel', 'Unterhaltung', 'Monatlich', 8),
        ('Spotify', 11.99, 'Marcel', 'Unterhaltung', 'Monatlich', 9),
        ('Xbox Gamepass Ultimate', 18.99, 'Marcel', 'Unterhaltung', 'Monatlich', 10),
        ('Netflix', 11.99, 'Marcel', 'Unterhaltung', 'Monatlich', 11),
        ('Axa Haftpflicht und Hausrat', 19.10, 'Marcel', 'Versicherung', 'Monatlich', 12),
        ('Kreditrate Hemeringen', 913.88, 'Marcel', 'Immobilien', 'Monatlich', 13),
        ('Verwaltung WHG9 Hemeringen', 200.00, 'Marcel', 'Immobilien', 'Monatlich', 14),
        ('Kreditrate Sigma', 381.00, 'Marcel', 'Schulden', 'Monatlich', 15),
        ('KFZ Steuer HM-MC-15', 94.35, 'Marcel', 'KFZ', 'Jährlich', 16),
        ('Grundsteuer B WHG9 Hemeringen', 14.77, 'Marcel', 'Immobilien', 'Jährlich', 17)");
    $inserts[] = 'Checkliste-Zahlungen eingefügt';
}

if ((int)$db->query('SELECT COUNT(*) FROM mieten_checkliste')->fetchColumn() === 0) {
    $db->exec("INSERT INTO `mieten_checkliste` (bezeichnung, immobilien_id, typ, sort_order) VALUES
        ('Friedrichshagener Str. 13 - WHG 9 - Claudia Waber', 1, 'Kaltmiete', 1),
        ('Nebenkosten WHG 9', 1, 'Nebenkosten', 2),
        ('Verwaltung WHG 9', 1, 'Verwaltung', 3)");
    $inserts[] = 'Mieten-Checkliste eingefügt';
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Setup V2</title>
    <link rel="stylesheet" href="assets/privat.css">
</head>
<body class="login-body">
<div class="login-wrap" style="max-width:600px">
    <div class="login-logo">
        <span class="logo-mark">M</span>
        <span class="logo-text">monvesto <em>setup v2</em></span>
    </div>
    <div style="background:var(--white);border:0.5px solid var(--border);border-radius:var(--radius-lg);padding:28px;box-shadow:var(--shadow-md)">
        <h2 style="font-size:16px;font-weight:700;margin-bottom:16px">Ergebnis</h2>
        <?php foreach ($log as [$type, $msg]): ?>
        <div style="padding:8px 12px;margin-bottom:6px;border-radius:6px;font-size:14px;
            background:<?= $type==='ok'?'var(--green-light)':'#FEF2F2' ?>;
            color:<?= $type==='ok'?'var(--green-dark)':'#991B1B' ?>;
            border:0.5px solid <?= $type==='ok'?'var(--green-mid)':'#FECACA' ?>">
            <?= $type==='ok'?'✓':'✕' ?> <?= $msg ?>
        </div>
        <?php endforeach; ?>
        <?php if (!empty($inserts)): ?>
        <div style="margin-top:16px;padding-top:16px;border-top:0.5px solid var(--border)">
            <?php foreach ($inserts as $i): ?>
            <div style="padding:6px 12px;margin-bottom:4px;border-radius:6px;font-size:13px;background:var(--bg);color:var(--text-muted)">
                ℹ️ <?= $i ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div style="margin-top:20px;padding-top:16px;border-top:0.5px solid var(--border);display:flex;gap:10px">
            <a href="index.php" class="btn btn-primary">Zum Dashboard →</a>
        </div>
        <p style="margin-top:16px;font-size:12px;color:var(--text-muted)">
            <strong>Wichtig:</strong> Diese Datei (setup_v2.php) danach bitte löschen.
        </p>
    </div>
</div>
</body>
</html>