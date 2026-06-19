<?php
require_once 'config/bootstrap.php';
$db = get_db();
$cols = $db->query("SHOW COLUMNS FROM mieten_checkliste")->fetchAll(PDO::FETCH_COLUMN);
echo implode(', ', $cols);