<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ── Datenbank ─────────────────────────────────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_NAME',    'private_dashboard');
define('DB_USER',    'dashboard_user');
define('DB_PASS',    'Deluxeer123!');
define('DB_CHARSET', 'utf8mb4');

// ── App ───────────────────────────────────────────────────────────────────────
define('APP_NAME', 'Monvesto Privat');
define('APP_URL',  'https://www.monvesto.de/private-dashboard');
define('SESSION_NAME', 'mvp_sess');

date_default_timezone_set('Europe/Berlin');

// ── Verschlüsselung für API-Keys (AES-256-CBC) ────────────────────────────────
// WICHTIG: Diesen Key niemals ändern nach dem ersten Speichern von API-Keys!
// Sonst müssen alle gespeicherten Keys neu eingetragen werden.
define('RF_ENCRYPT_KEY', '5368ce57bf755d7ce8999014c554881a248f3ed6d5584e83646196e8a980b73b');

// ── GoCardless Open Banking API ───────────────────────────────────────────────
define('GOCARDLESS_SECRET_ID',  '');
define('GOCARDLESS_SECRET_KEY', '');

// ── MyFxBook API ──────────────────────────────────────────────────────────────
define('MYFXBOOK_EMAIL',    'da_bunky@yahoo.de');
define('MYFXBOOK_PASSWORD', 'Deluxeer1');

// ── Telegram Trading Autoposter ───────────────────────────────────────────────
define('TELEGRAM_BOT_TOKEN',  '8542547873:AAGgOrPKiuzjyvhAX-NqNQHkgI0kPymiA4E');
define('TELEGRAM_CHANNEL_ID', '-1002065655634');

// ── RoboForex Partner API (Legacy – wird aus DB gelesen) ──────────────────────
// Diese Konstanten werden nur noch für die initiale Migration 12 genutzt.
// Neue Konten werden verschlüsselt in der DB gespeichert.
//define('ROBOFOREX_PARTNER_ACCOUNT_ID', '7026711');
//define('ROBOFOREX_API_KEY',            '65a10da439dbb417');