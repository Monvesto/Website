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