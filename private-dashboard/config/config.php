<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('DB_HOST', 'localhost');
define('DB_NAME', 'private_dashboard');
define('DB_USER', 'dashboard_user');
define('DB_PASS', 'Deluxeer123!');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'Monvesto Privat');
define('APP_URL',  'https://www.monvesto.de/private-dashboard');

define('SESSION_NAME', 'mvp_sess');

date_default_timezone_set('Europe/Berlin');