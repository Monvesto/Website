//Gibt aus, welche tabellen in der Datenbank existieren und welche Spalten sie haben. Nützlich für die Entwicklung

<?php
require_once 'config/bootstrap.php';
$db = get_db();
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    echo "<b>$t</b>: ";
    $cols = $db->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_COLUMN);
    echo implode(', ', $cols) . "<br>";
}