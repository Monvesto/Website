<?php
// ════════════════════════════════════════════════
// config/bootstrap.php – Initialisierung & Migrationen
//
// Wird als erstes geladen. Führt folgendes aus:
//   1. Abhängigkeiten laden (config, db, auth, csrf)
//   2. Session starten
//   3. Globale Hilfsfunktionen definieren
//   4. Datenbankmigrationen ausführen (einmalig)
//
// MIGRATIONS-KONZEPT:
//   Jede Migration prüft ob sie bereits ausgeführt wurde
//   und überspringt sich selbst bei erneutem Aufruf.
//   Neue Tabellen/Spalten können hier einfach ergänzt werden.
// ════════════════════════════════════════════════

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
start_secure_session();

// ════════════════════════════════════════════════
// GLOBALE HILFSFUNKTIONEN
// ════════════════════════════════════════════════

/**
 * Betragsstring sauber zu Float-String konvertieren.
 * Entfernt Tausenderpunkte, ersetzt Komma durch Punkt.
 * Beispiel: "5.000,99" → "5000.99"
 */
function parse_betrag(string $v): string {
    return str_replace(',', '.', str_replace('.', '', trim($v)));
}

// ════════════════════════════════════════════════
// MIGRATION 1 – User-System
// Legt users und user_profiles Tabellen an.
// ════════════════════════════════════════════════
(function() {
    $db = get_db();
    try {
        // ── users Tabelle ──
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id           INT AUTO_INCREMENT PRIMARY KEY,
            username     VARCHAR(50)  NOT NULL UNIQUE,
            email        VARCHAR(150) NOT NULL UNIQUE,
            password     VARCHAR(255) NOT NULL,
            display_name VARCHAR(100) NOT NULL DEFAULT '',
            role         ENUM('admin','user') NOT NULL DEFAULT 'user',
            aktiv        TINYINT(1) NOT NULL DEFAULT 1,
            created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // ── user_profiles Tabelle ──
        // Jeder User kann mehrere Profile anlegen (= Person-Switcher Einträge)
        $db->exec("CREATE TABLE IF NOT EXISTS user_profiles (
            id           INT AUTO_INCREMENT PRIMARY KEY,
            user_id      INT NOT NULL,
            profile_name VARCHAR(50) NOT NULL,  -- z.B. 'Marcel', 'Kim'
            is_default   TINYINT(1) NOT NULL DEFAULT 0,
            sort_order   INT NOT NULL DEFAULT 0,
            created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_user_profile (user_id, profile_name),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");

    } catch (PDOException $e) {
        error_log('Migration users: ' . $e->getMessage());
    }
})();

// ════════════════════════════════════════════════
// MIGRATION 2 – user_id zu allen Datentabellen
// Fügt user_id Spalte hinzu falls noch nicht vorhanden.
// Bestehende Einträge werden dem ersten Admin zugewiesen.
// ════════════════════════════════════════════════
(function() {
    $db = get_db();

    // Tabellen die user_id bekommen sollen
    $tables = [
        'einnahmen', 'ausgaben', 'verbindlichkeiten',
        'investments', 'ziele', 'tasks', 'maintenance',
        'immobilien', 'checkliste_zahlungen', 'mieten_checkliste',
        'abos', 'versicherungen',
    ];

    // Ersten Admin-User als Fallback für bestehende Daten
    $admin_id = null;
    try {
        $admin_id = $db->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetchColumn();
    } catch (PDOException $e) {}

    foreach ($tables as $table) {
        try {
            $exists = $db->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
            if (!$exists) continue;

            // user_id Spalte hinzufügen falls nicht vorhanden
            $has_col = $db->query("SHOW COLUMNS FROM `$table` LIKE 'user_id'")->rowCount() > 0;
            if (!$has_col) {
                $db->exec("ALTER TABLE `$table` ADD COLUMN user_id INT NOT NULL DEFAULT 0 AFTER id");
                // Index für Performance
                $db->exec("ALTER TABLE `$table` ADD INDEX idx_user_id (user_id)");
                // Bestehende Daten dem Admin zuweisen
                if ($admin_id) {
                    $db->exec("UPDATE `$table` SET user_id=$admin_id WHERE user_id=0");
                }
            }
        } catch (PDOException $e) {
            error_log("Migration user_id ($table): " . $e->getMessage());
        }
    }
})();

// ════════════════════════════════════════════════
// MIGRATION 3 – Position-Spalte
// ════════════════════════════════════════════════
(function() {
    $db = get_db();
    $tables = ['einnahmen', 'ausgaben', 'verbindlichkeiten', 'tasks', 'maintenance', 'ziele', 'checkliste'];
    foreach ($tables as $table) {
        try {
            $exists = $db->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
            if (!$exists) continue;
            $cols = $db->query("SHOW COLUMNS FROM `$table` LIKE 'position'")->rowCount();
            if ($cols === 0) {
                $db->exec("ALTER TABLE `$table` ADD COLUMN `position` INT NOT NULL DEFAULT 0");
                $db->exec("SET @pos = 0; UPDATE `$table` SET position = (@pos := @pos + 1) ORDER BY id");
            }
        } catch (PDOException $e) {
            error_log('Migration position (' . $table . '): ' . $e->getMessage());
        }
    }
})();

// ════════════════════════════════════════════════
// MIGRATION 4 – Person-Spalte für Checkliste
// ════════════════════════════════════════════════
(function() {
    $db = get_db();
    $tables = ['checkliste_zahlungen', 'mieten_checkliste'];
    foreach ($tables as $table) {
        try {
            $exists = $db->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
            if (!$exists) continue;
            $cols = $db->query("SHOW COLUMNS FROM `$table` LIKE 'person'")->rowCount();
            if ($cols === 0) {
                $db->exec("ALTER TABLE `$table` ADD COLUMN `person` VARCHAR(20) NOT NULL DEFAULT 'Beide'");
            }
        } catch (PDOException $e) {
            error_log('Migration person Checkliste (' . $table . '): ' . $e->getMessage());
        }
    }
})();

// ════════════════════════════════════════════════
// MIGRATION 5 – Person-Spalte für weitere Tabellen
// ════════════════════════════════════════════════
(function() {
    $db = get_db();
    $tables = ['verbindlichkeiten', 'investments', 'ziele', 'tasks', 'maintenance', 'immobilien'];
    foreach ($tables as $table) {
        try {
            $exists = $db->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
            if (!$exists) continue;
            $cols = $db->query("SHOW COLUMNS FROM `$table` LIKE 'person'")->rowCount();
            if ($cols === 0) {
                $db->exec("ALTER TABLE `$table` ADD COLUMN `person` VARCHAR(20) NOT NULL DEFAULT 'Beide'");
            }
        } catch (PDOException $e) {
            error_log('Migration person (' . $table . '): ' . $e->getMessage());
        }
    }
})();

/**
 * Gibt user_id des eingeloggten Users zurück.
 * Kurzform für current_user_id().
 */
function uid(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}

// ════════════════════════════════════════════════
// GLOBALE AUTH-HILFSFUNKTIONEN
// ════════════════════════════════════════════════

function is_partner(): bool {
    if (!isset($_SESSION['user_id'])) return false;
    $db   = get_db();
    $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row && in_array($row['role'], ['admin', 'partner']);
}

function get_current_role(): string {
    if (!isset($_SESSION['user_id'])) return 'guest';
    $db   = get_db();
    $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['role'] ?? 'user';
}

// ════════════════════════════════════════════════
// MIGRATION 6 – Geburtsdatum in users
// ════════════════════════════════════════════════
(function() {
    $db = get_db();
    try {
        $has_col = $db->query("SHOW COLUMNS FROM `users` LIKE 'geburtsdatum'")->rowCount() > 0;
        if (!$has_col) {
            $db->exec("ALTER TABLE `users` ADD COLUMN geburtsdatum DATE NULL DEFAULT NULL");
        }
    } catch (PDOException $e) {
        error_log('Migration geburtsdatum: ' . $e->getMessage());
    }
})();

// ════════════════════════════════════════════════
// MIGRATION 7 – Verified-Spalte in users
// ════════════════════════════════════════════════
(function() {
    $db = get_db();
    try {
        $has_col = $db->query("SHOW COLUMNS FROM `users` LIKE 'verified'")->rowCount() > 0;
        if (!$has_col) {
            $db->exec("ALTER TABLE `users` ADD COLUMN verified TINYINT(1) NOT NULL DEFAULT 0");
        }
    } catch (PDOException $e) {
        error_log('Migration verified: ' . $e->getMessage());
    }
})();

// ════════════════════════════════════════════════
// MIGRATION 8 – Trading Daily Updates
// ════════════════════════════════════════════════
(function() {
    $db = get_db();
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS `trading_daily_updates` (
            `id`                        INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `entry_date`                DATE NOT NULL,
            `trading_day`               SMALLINT UNSIGNED NOT NULL,
            `main_account_return`       DECIMAL(8,4) DEFAULT NULL,
            `ea_account_return`         DECIMAL(8,4) DEFAULT NULL,
            `challenge_account_return`  DECIMAL(8,4) DEFAULT NULL,
            `created_at`                DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`                DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_entry_date` (`entry_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (PDOException $e) {
        error_log('Migration trading_daily_updates: ' . $e->getMessage());
    }
})();

// ════════════════════════════════════════════════
// MIGRATION 9 – Trading: neue Felder + Startkontostände
// Diesen Block ans Ende der config/bootstrap.php anhängen.
// ════════════════════════════════════════════════
(function() {
    $db = get_db();
    try {
        // ── Neue Spalten in trading_daily_updates ──────────────────────────
        // Gewinn in € pro Konto
        foreach ([
            'main_account_profit'      => "DECIMAL(12,2) DEFAULT NULL AFTER `main_account_return`",
            'ea_account_profit'        => "DECIMAL(12,2) DEFAULT NULL AFTER `ea_account_return`",
            'challenge_account_profit' => "DECIMAL(12,2) DEFAULT NULL AFTER `challenge_account_return`",
            // Aktueller Kontostand (aus MyFxBook oder manuell)
            'main_account_balance'      => "DECIMAL(12,2) DEFAULT NULL AFTER `main_account_profit`",
            'ea_account_balance'        => "DECIMAL(12,2) DEFAULT NULL AFTER `ea_account_profit`",
            'challenge_account_balance' => "DECIMAL(12,2) DEFAULT NULL AFTER `challenge_account_profit`",
            // Offene Positionen (JSON-String aus MyFxBook)
            'main_open_positions'      => "TEXT DEFAULT NULL",
            'ea_open_positions'        => "TEXT DEFAULT NULL",
            'challenge_open_positions' => "TEXT DEFAULT NULL",
            // MyFxBook Session-Cache (damit nicht bei jedem Aufruf neu eingeloggt wird)
            'myfxbook_synced_at'       => "DATETIME DEFAULT NULL",
        ] as $col => $def) {
            $has = $db->query("SHOW COLUMNS FROM `trading_daily_updates` LIKE '$col'")->rowCount() > 0;
            if (!$has) {
                $db->exec("ALTER TABLE `trading_daily_updates` ADD COLUMN `$col` $def");
            }
        }

        // ── Tabelle: trading_account_settings ─────────────────────────────
        // Speichert Ausgangskontostand + MyFxBook Account-ID pro Konto
        $db->exec("CREATE TABLE IF NOT EXISTS `trading_account_settings` (
            `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `account_key`     VARCHAR(20) NOT NULL,   -- 'main' | 'ea' | 'challenge'
            `label`           VARCHAR(50) NOT NULL,   -- Anzeigename
            `start_balance`   DECIMAL(12,2) DEFAULT NULL,  -- Ausgangskontostand €
            `currency`        VARCHAR(5) NOT NULL DEFAULT 'USD',
            `myfxbook_id`     VARCHAR(20) DEFAULT NULL,    -- MyFxBook Account-ID
            `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_account_key` (`account_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Standard-Einträge anlegen falls noch nicht vorhanden
        $defaults = [
            ['main',      'Main Account',   'USD'],
            ['ea',        'Monvesto EA',    'USD'],
            ['challenge', 'Road to 100k',  'USD'],
        ];
        $stmtIns = $db->prepare("
            INSERT IGNORE INTO trading_account_settings (account_key, label, currency)
            VALUES (?, ?, ?)
        ");
        foreach ($defaults as $row) {
            $stmtIns->execute($row);
        }

        // ── Tabelle: trading_myfxbook_session ─────────────────────────────
        // Cacht den MyFxBook Session-Token (max. 1 Token gleichzeitig)
        $db->exec("CREATE TABLE IF NOT EXISTS `trading_myfxbook_session` (
            `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `session`    VARCHAR(100) NOT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    } catch (PDOException $e) {
        error_log('Migration 9 trading: ' . $e->getMessage());
    }
})();

// ════════════════════════════════════════════════
// MIGRATION 10 – calc_basis in trading_account_settings
// ════════════════════════════════════════════════
(function() {
    $db = get_db();
    try {
        $has = $db->query("SHOW COLUMNS FROM `trading_account_settings` LIKE 'calc_basis'")->rowCount() > 0;
        if (!$has) {
            $db->exec("ALTER TABLE `trading_account_settings` ADD COLUMN `calc_basis` DECIMAL(12,2) DEFAULT NULL AFTER `start_balance`");
        }
    } catch (PDOException $e) {
        error_log('Migration 10 calc_basis: ' . $e->getMessage());
    }
})();

// ════════════════════════════════════════════════
// MIGRATION 11 – start_date in trading_account_settings
// ════════════════════════════════════════════════
(function() {
    $db = get_db();
    try {
        $has = $db->query("SHOW COLUMNS FROM `trading_account_settings` LIKE 'start_date'")->rowCount() > 0;
        if (!$has) {
            $db->exec("ALTER TABLE `trading_account_settings` ADD COLUMN `start_date` DATE DEFAULT NULL AFTER `start_balance`");
        }
    } catch (PDOException $e) {
        error_log('Migration 11 start_date: ' . $e->getMessage());
    }
})();

// ════════════════════════════════════════════════
// MIGRATION 12 – RoboForex Partner-Konten
// ════════════════════════════════════════════════
(function() {
    $db = get_db();
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS `roboforex_accounts` (
            `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `account_id`  VARCHAR(20)  NOT NULL,
            `label`       VARCHAR(50)  NOT NULL DEFAULT '',
            `api_key`     VARCHAR(100) NOT NULL,
            `sort_order`  INT NOT NULL DEFAULT 0,
            `active`      TINYINT(1) NOT NULL DEFAULT 1,
            `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_account_id` (`account_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Bestehendes Konto aus config.php migrieren falls vorhanden
        if (defined('ROBOFOREX_PARTNER_ACCOUNT_ID') && ROBOFOREX_PARTNER_ACCOUNT_ID
            && defined('ROBOFOREX_API_KEY') && ROBOFOREX_API_KEY) {
            $db->prepare("INSERT IGNORE INTO roboforex_accounts (account_id, label, api_key, sort_order)
                          VALUES (?, 'Hauptkonto', ?, 0)")
               ->execute([ROBOFOREX_PARTNER_ACCOUNT_ID, ROBOFOREX_API_KEY]);
        }
    } catch (PDOException $e) {
        error_log('Migration 12 roboforex_accounts: ' . $e->getMessage());
    }
})();

// ════════════════════════════════════════════════
// MIGRATION 13 – RoboForex Cache-Tabellen
// ════════════════════════════════════════════════
(function() {
    $db = get_db();
    try {
        // Gecachte Clients
        $db->exec("CREATE TABLE IF NOT EXISTS `roboforex_clients` (
            `id`                              INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `rf_account_id`                   VARCHAR(20) NOT NULL,  -- Partner-Konto-ID
            `client_account_id`               VARCHAR(20) NOT NULL,  -- Referral-Konto-ID
            `account_type`                    VARCHAR(50) DEFAULT NULL,
            `reg_date`                        DATETIME DEFAULT NULL,
            `has_reached_deposit_threshold`   TINYINT(1) DEFAULT 0,
            `is_active_accrual_of_commission` TINYINT(1) DEFAULT 0,
            `synced_at`                       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_rf_client` (`rf_account_id`, `client_account_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Affiliate-Baum
        $db->exec("CREATE TABLE IF NOT EXISTS `roboforex_tree` (
            `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `rf_account_id`   VARCHAR(20) NOT NULL,
            `parent_id`       VARCHAR(20) NOT NULL,
            `child_id`        VARCHAR(20) NOT NULL,
            `depth`           TINYINT UNSIGNED NOT NULL DEFAULT 1,
            `synced_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_rf_tree` (`rf_account_id`, `parent_id`, `child_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Labels/Namen für Konto-IDs
        $db->exec("CREATE TABLE IF NOT EXISTS `roboforex_client_labels` (
            `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `client_account_id` VARCHAR(20) NOT NULL,
            `label`             VARCHAR(100) NOT NULL DEFAULT '',
            `notes`             TEXT DEFAULT NULL,
            `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_client_label` (`client_account_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Sync-Status pro Partner-Konto
        $db->exec("CREATE TABLE IF NOT EXISTS `roboforex_sync_log` (
            `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `rf_account_id` VARCHAR(20) NOT NULL,
            `sync_type`     VARCHAR(20) NOT NULL,  -- 'clients' | 'tree'
            `synced_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `records`       INT UNSIGNED DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_sync` (`rf_account_id`, `sync_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    } catch (PDOException $e) {
        error_log('Migration 13 roboforex_cache: ' . $e->getMessage());
    }
})();

// ════════════════════════════════════════════════
// MIGRATION 14 – RoboForex Provisions-Cache
// ════════════════════════════════════════════════
(function() {
    $db = get_db();
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS `roboforex_commission_cache` (
            `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `rf_account_id` VARCHAR(20) NOT NULL,
            `cache_key`     VARCHAR(50) NOT NULL,  -- 'today','tomorrow','week','month','total'
            `amount`        DECIMAL(12,4) NOT NULL DEFAULT 0,
            `date_from`     DATE NOT NULL,
            `date_to`       DATE NOT NULL,
            `synced_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_commission_cache` (`rf_account_id`, `cache_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (PDOException $e) {
        error_log('Migration 14: ' . $e->getMessage());
    }
})();

// ════════════════════════════════════════════════
// MIGRATION 15 – Partner-Rolle + user_id in roboforex_accounts
// ════════════════════════════════════════════════
(function() {
    $db = get_db();
    try {
        // user_id Spalte zu roboforex_accounts hinzufügen
        $cols = $db->query("SHOW COLUMNS FROM roboforex_accounts")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('user_id', $cols)) {
            $db->exec("ALTER TABLE roboforex_accounts ADD COLUMN `user_id` INT UNSIGNED DEFAULT NULL AFTER `id`");
            $db->exec("ALTER TABLE roboforex_accounts ADD KEY `idx_user_id` (`user_id`)");
        }
        // Bestehende Konten ohne user_id bleiben Admin-Konten (user_id = NULL = alle Admins sehen sie)
    } catch (PDOException $e) {
        error_log('Migration 15: ' . $e->getMessage());
    }
})();

// ════════════════════════════════════════════════
// MIGRATION 16 – Partner-Rolle in users.role ENUM
// ════════════════════════════════════════════════
(function() {
    $db = get_db();
    try {
        $db->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin','user','partner') NOT NULL DEFAULT 'user'");
    } catch (PDOException $e) {
        error_log('Migration 16 partner role: ' . $e->getMessage());
    }
})();