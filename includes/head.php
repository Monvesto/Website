<?php
// Charset ZUERST senden – vor jeder HTML-Ausgabe
header('Content-Type: text/html; charset=utf-8');

// Sicherer Include-Pfad für HostEurope
define('ROOT', $_SERVER['DOCUMENT_ROOT']);
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo isset($title) ? htmlspecialchars($title, ENT_QUOTES, 'UTF-8') : 'Monvesto'; ?></title>
  <meta name="description" content="<?php echo isset($meta) ? htmlspecialchars($meta, ENT_QUOTES, 'UTF-8') : ''; ?>" />
  <meta name="robots" content="index, follow" />
  <?php if (!empty($canonical)): ?>
  <link rel="canonical" href="<?php echo htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8'); ?>" />
  <?php endif; ?>
  <link rel="stylesheet" href="/assets/style.css" />
  <link rel="stylesheet" href="/assets/vergleich-components.css">
  <?php if (!empty($schema)): ?>
  <script type="application/ld+json"><?php echo $schema; ?></script>
  <?php endif; ?>
</head>
<body>