<?php
// head.php – wird auf jeder Seite eingebunden
// Variablen die vor dem include gesetzt werden müssen:
// $title    – Seitentitel
// $meta     – Meta-Description
// $canonical – Canonical URL
// $schema   – JSON-LD Schema (optional)
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($title); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($meta); ?>" />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="<?php echo htmlspecialchars($canonical); ?>" />
  <link rel="stylesheet" href="/assets/style.css" />
  <?php if (!empty($schema)): ?>
  <script type="application/ld+json"><?php echo $schema; ?></script>
  <?php endif; ?>
</head>
<body>