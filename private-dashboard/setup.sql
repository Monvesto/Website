-- =====================================================
-- Monvesto Privat Dashboard – Datenbankstruktur
-- =====================================================

CREATE TABLE IF NOT EXISTS `users` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `username`      VARCHAR(80)     NOT NULL UNIQUE,
    `password_hash` VARCHAR(255)    NOT NULL,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tasks` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `task`          VARCHAR(255)    NOT NULL,
    `category`      VARCHAR(100)    DEFAULT NULL,
    `priority`      ENUM('Hoch','Mittel','Niedrig') NOT NULL DEFAULT 'Mittel',
    `responsible`   VARCHAR(100)    DEFAULT NULL,
    `interval_type` VARCHAR(100)    DEFAULT NULL,
    `last_done`     DATE            DEFAULT NULL,
    `due_date`      DATE            DEFAULT NULL,
    `status`        ENUM('Offen','Erledigt') NOT NULL DEFAULT 'Offen',
    `notes`         TEXT            DEFAULT NULL,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `maintenance` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `object_name`   VARCHAR(150)    NOT NULL,
    `task`          VARCHAR(255)    NOT NULL,
    `interval_type` VARCHAR(100)    DEFAULT NULL,
    `last_done`     DATE            DEFAULT NULL,
    `next_due`      DATE            DEFAULT NULL,
    `status`        ENUM('OK','Bald fällig','Überfällig') NOT NULL DEFAULT 'OK',
    `notes`         TEXT            DEFAULT NULL,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;