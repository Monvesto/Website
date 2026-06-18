<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
start_secure_session();

// ── Automatische Migration: position-Spalte ──
(function() {
    $db = get_db();
    $tables = ['einnahmen', 'ausgaben', 'verbindlichkeiten', 'tasks', 'maintenance', 'ziele', 'checkliste'];
    foreach ($tables as $table) {
        try {
            // Prüfen ob Tabelle existiert
            $exists = $db->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
            if (!$exists) continue;
            // Prüfen ob position-Spalte bereits existiert
            $cols = $db->query("SHOW COLUMNS FROM `$table` LIKE 'position'")->rowCount();
            if ($cols === 0) {
                $db->exec("ALTER TABLE `$table` ADD COLUMN `position` INT NOT NULL DEFAULT 0");
                // Bestehende Einträge mit aufsteigender Position initialisieren
                $db->exec("SET @pos = 0; UPDATE `$table` SET position = (@pos := @pos + 1) ORDER BY id");
            }
        } catch (PDOException $e) {
            error_log('Migration position-Spalte Fehler (' . $table . '): ' . $e->getMessage());
        }
    }
})();