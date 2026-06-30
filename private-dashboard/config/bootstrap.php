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

        $db->exec("CREATE TABLE IF NOT EXISTS user_profiles (
            id           INT AUTO_INCREMENT PRIMARY KEY,
            user_id      INT NOT NULL,
            profile_name VARCHAR(50) NOT NULL,
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
// ════════════════════════════════════════════════
(function() {
    $db = get_db();
    $tables = [
        'einnahmen', 'ausgaben', 'verbindlichkeiten',
        'investments', 'ziele', 'tasks', 'maintenance',
        'immobilien', 'checkliste_zahlungen', 'mieten_checkliste',
        'abos', 'versicherungen',
    ];
    $admin_id = null;
    try {
        $admin_id = $db->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetchColumn();
    } catch (PDOException $e) {}

    foreach ($tables as $table) {
        try {
            $exists = $db->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
            if (!$exists) continue;
            $has_col = $db->query("SHOW COLUMNS FROM `$table` LIKE 'user_id'")->rowCount() > 0;
            if (!$has_col) {
                $db->exec("ALTER TABLE `$table` ADD COLUMN user_id INT NOT NULL DEFAULT 0 AFTER id");
                $db->exec("ALTER TABLE `$table` ADD INDEX idx_user_id (user_id)");
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
 */
function uid(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}

// ── API-Key Verschlüsselung ───────────────────────────────────────────────────
function rf_encrypt(string $plaintext): string {
    $iv  = random_bytes(16);
    $enc = openssl_encrypt($plaintext, 'AES-256-CBC', hex2bin(RF_ENCRYPT_KEY), OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $enc);
}

function rf_decrypt(string $ciphertext): string {
    $data = base64_decode($ciphertext);
    $iv   = substr($data, 0, 16);
    $enc  = substr($data, 16);
    return openssl_decrypt($enc, 'AES-256-CBC', hex2bin(RF_ENCRYPT_KEY), OPENSSL_RAW_DATA, $iv);
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
// ════════════════════════════════════════════════
(function() {
    $db = get_db();
    try {
        foreach ([
            'main_account_profit'      => "DECIMAL(12,2) DEFAULT NULL AFTER `main_account_return`",
            'ea_account_profit'        => "DECIMAL(12,2) DEFAULT NULL AFTER `ea_account_return`",
            'challenge_account_profit' => "DECIMAL(12,2) DEFAULT NULL AFTER `challenge_account_return`",
            'main_account_balance'      => "DECIMAL(12,2) DEFAULT NULL AFTER `main_account_profit`",
            'ea_account_balance'        => "DECIMAL(12,2) DEFAULT NULL AFTER `ea_account_profit`",
            'challenge_account_balance' => "DECIMAL(12,2) DEFAULT NULL AFTER `challenge_account_profit`",
            'main_open_positions'      => "TEXT DEFAULT NULL",
            'ea_open_positions'        => "TEXT DEFAULT NULL",
            'challenge_open_positions' => "TEXT DEFAULT NULL",
            'myfxbook_synced_at'       => "DATETIME DEFAULT NULL",
        ] as $col => $def) {
            $has = $db->query("SHOW COLUMNS FROM `trading_daily_updates` LIKE '$col'")->rowCount() > 0;
            if (!$has) {
                $db->exec("ALTER TABLE `trading_daily_updates` ADD COLUMN `$col` $def");
            }
        }

        $db->exec("CREATE TABLE IF NOT EXISTS `trading_account_settings` (
            `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `account_key`     VARCHAR(20) NOT NULL,
            `label`           VARCHAR(50) NOT NULL,
            `start_balance`   DECIMAL(12,2) DEFAULT NULL,
            `currency`        VARCHAR(5) NOT NULL DEFAULT 'USD',
            `myfxbook_id`     VARCHAR(20) DEFAULT NULL,
            `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_account_key` (`account_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

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
        $db->exec("CREATE TABLE IF NOT EXISTS `roboforex_clients` (
            `id`                              INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `rf_account_id`                   VARCHAR(20) NOT NULL,
            `client_account_id`               VARCHAR(20) NOT NULL,
            `account_type`                    VARCHAR(50) DEFAULT NULL,
            `reg_date`                        DATETIME DEFAULT NULL,
            `has_reached_deposit_threshold`   TINYINT(1) DEFAULT 0,
            `is_active_accrual_of_commission` TINYINT(1) DEFAULT 0,
            `synced_at`                       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_rf_client` (`rf_account_id`, `client_account_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

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

        $db->exec("CREATE TABLE IF NOT EXISTS `roboforex_client_labels` (
            `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `client_account_id` VARCHAR(20) NOT NULL,
            `label`             VARCHAR(100) NOT NULL DEFAULT '',
            `notes`             TEXT DEFAULT NULL,
            `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_client_label` (`client_account_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $db->exec("CREATE TABLE IF NOT EXISTS `roboforex_sync_log` (
            `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `rf_account_id` VARCHAR(20) NOT NULL,
            `sync_type`     VARCHAR(20) NOT NULL,
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
            `cache_key`     VARCHAR(50) NOT NULL,
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
        $cols = $db->query("SHOW COLUMNS FROM roboforex_accounts")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('user_id', $cols)) {
            $db->exec("ALTER TABLE roboforex_accounts ADD COLUMN `user_id` INT UNSIGNED DEFAULT NULL AFTER `id`");
            $db->exec("ALTER TABLE roboforex_accounts ADD KEY `idx_user_id` (`user_id`)");
        }
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

// ════════════════════════════════════════════════
// MIGRATION 19 – RoboForex Kontodaten in trading_account_settings
// ════════════════════════════════════════════════
(function() {
    $db = get_db();
    try {
        foreach ([
            'rf_account_type' => "VARCHAR(50) DEFAULT NULL",
            'rf_account_id'   => "VARCHAR(20) DEFAULT NULL",
            'rf_server'       => "VARCHAR(50) DEFAULT NULL",
        ] as $col => $def) {
            $has = $db->query("SHOW COLUMNS FROM `trading_account_settings` LIKE '$col'")->rowCount() > 0;
            if (!$has) {
                $db->exec("ALTER TABLE `trading_account_settings` ADD COLUMN `$col` $def");
            }
        }
    } catch (PDOException $e) {
        error_log('Migration 19: ' . $e->getMessage());
    }
})();

// ════════════════════════════════════════════════
// MIGRATION 20 – rf_leverage in trading_account_settings
// ════════════════════════════════════════════════
(function() {
    $db = get_db();
    try {
        $has = $db->query("SHOW COLUMNS FROM `trading_account_settings` LIKE 'rf_leverage'")->rowCount() > 0;
        if (!$has) {
            $db->exec("ALTER TABLE `trading_account_settings` ADD COLUMN `rf_leverage` VARCHAR(20) DEFAULT NULL");
        }
    } catch (PDOException $e) {
        error_log('Migration 20: ' . $e->getMessage());
    }
})();

// ════════════════════════════════════════════════
// MIGRATION 21 – App-Settings Tabelle + Trading-Startdatum
// ════════════════════════════════════════════════
(function() {
    $db = get_db();
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS `app_settings` (
            `setting_key`   VARCHAR(50) NOT NULL,
            `setting_value` VARCHAR(255) NOT NULL,
            `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $db->prepare("INSERT IGNORE INTO app_settings (setting_key, setting_value) VALUES ('trading_start_date', ?)")
           ->execute([date('Y-m-d')]);
    } catch (PDOException $e) {
        error_log('Migration 21: ' . $e->getMessage());
    }
})();

// ── Trading-Startdatum (zentral konfigurierbar) ───────────────────────────────
function getTradingStartDate(): string {
    $db = get_db();
    $stmt = $db->prepare("SELECT setting_value FROM app_settings WHERE setting_key = 'trading_start_date'");
    $stmt->execute();
    $val = $stmt->fetchColumn();
    return $val ?: date('Y-m-d');
}

function setTradingStartDate(string $date): void {
    $db = get_db();
    $db->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES ('trading_start_date', ?)
                  ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)")
       ->execute([$date]);
}