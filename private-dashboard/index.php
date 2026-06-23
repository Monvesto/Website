<?php
// ════════════════════════════════════════════════
// index.php – Haupt-Router des Monvesto Privat Dashboards
// Lädt je nach ?page= Parameter die entsprechende Seite
// POST-Requests werden zuerst verarbeitet (vor HTML-Ausgabe)
// ════════════════════════════════════════════════

require_once __DIR__ . '/config/bootstrap.php';
require_login();

// ── Seite bestimmen und validieren ──
$page = $_GET['page'] ?? 'dashboard';
$allowed = [
    'dashboard',
    'finanzen',
    'checkliste',
    'tasks',
    'maintenance',
    'ziele',
    'investments',
    'immobilien',
    'trading',
    'abos',
    'versicherungen',
    'banking',
    'admin',
    'profil'
];
if (!in_array($page, $allowed, true)) $page = 'dashboard';
$page_file = __DIR__ . '/pages/' . $page . '.php';

// ── Profil-Check ──
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !in_array($page, ['profil', 'admin'], true)) {
    if (empty(get_person_options())) {
        die('<p style="font-family:sans-serif;padding:2rem">Dein Account hat kein Profil. Bitte wende dich an den Administrator.<br><br><a href="logout.php">Abmelden</a></p>');
    }
}

// ── POST zuerst verarbeiten – bevor HTML ausgegeben wird ──
// Jede Page-Datei prüft selbst ob POST vorliegt und redirectet.
// HANDLE_POST_ONLY verhindert dass die Seite HTML ausgibt.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    define('HANDLE_POST_ONLY', true);
    require $page_file;
    exit;
}

// ── Seiten-Titel ──
$titles = [
    'dashboard'      => 'Dashboard',
    'finanzen'       => 'Finanzen',
    'checkliste'     => 'Checklisten',
    'tasks'          => 'Aufgaben',
    'maintenance'    => 'Wartungen',
    'ziele'          => 'Ziele',
    'investments'    => 'Investments',
    'immobilien'     => 'Immobilien',
    'trading'        => 'Trading',
    'abos'           => 'Abos',
    'versicherungen' => 'Versicherungen',
    'banking'        => 'Banking',
    'admin'          => 'Admin',
    'profil'         => 'Profil'
];
$page_title = $titles[$page] ?? 'Dashboard';

// ── Navigation – Icon (SVG-Pfad) + Label ──
$nav = [
    'dashboard' => [
        'icon'  => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'label' => 'Dashboard',
    ],
    'finanzen' => [
        'icon'  => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        'label' => 'Finanzen',
    ],
    'checkliste' => [
        'icon'  => '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
        'label' => 'Checklisten',
    ],
    'tasks' => [
        'icon'  => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 12h8M8 8h8M8 16h5"/>',
        'label' => 'Aufgaben',
    ],
    'maintenance' => [
        'icon'  => '<circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M4.93 19.07l1.41-1.41M19.07 19.07l-1.41-1.41M12 2v2M12 20v2M2 12h2M20 12h2"/>',
        'label' => 'Wartungen',
    ],
    'ziele' => [
        'icon'  => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
        'label' => 'Ziele',
    ],
    'investments' => [
        'icon'  => '<polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>',
        'label' => 'Investments',
    ],
    'immobilien' => [
        'icon'  => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'label' => 'Immobilien',
    ],
    'abos' => [
        'icon'  => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 3H8a2 2 0 0 0-2 2v2h12V5a2 2 0 0 0-2-2z"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/>',
        'label' => 'Abos',
    ],
    'versicherungen' => [
        'icon'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'label' => 'Versicherungen',
    ],
    'banking' => [
        'icon'  => '<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>',
        'label' => 'Banking',
    ],
    'trading' => [
        'icon'  => '<polyline points="2 20 7 10 12 15 17 5 22 10"/>',
        'label' => 'Trading',
    ],
    'profil' => [
        'icon'  => '<circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>',
        'label' => 'Mein Profil',
    ],
    ];
    // Admin-Link nur für Admins hinzufügen
    if (is_admin()) {
        $nav['admin'] = [
            'icon'  => '<circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/><circle cx="19" cy="18" r="3"/>',
            'label' => 'Admin',
        ];
    }
    ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($page_title) ?> – Monvesto Privat</title>
    <link rel="stylesheet" href="assets/privat.css">
</head>
<body>
<div class="app-layout">

    <!-- ── Sidebar Navigation ── -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span class="logo-mark">M</span>
            <span class="logo-text">monvesto <em>privat</em></span>
        </div>
        <ul class="nav-links">
            <?php foreach ($nav as $key => $item): ?>
            <li>
                <a href="?page=<?= $key ?>" class="nav-link <?= $page === $key ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <?= $item['icon'] ?>
                    </svg>
                    <?= $item['label'] ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <div class="sidebar-footer">
            <span class="sidebar-user">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="14" height="14">
                    <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                </svg>
                <?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?>
            </span>
            <form method="POST" action="logout.php" class="form-hidden-margin">
                <?= csrf_field() ?>
                <button type="submit" class="btn-logout" title="Abmelden">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="14" height="14">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Abmelden
                </button>
            </form>
        </div>
    </nav>

    <!-- ── Hauptinhalt ── -->
    <main class="main-content">
        <div class="topbar">
            <button class="menu-toggle" id="menuToggle" aria-label="Menü">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>
            <h1 class="page-title"><?= htmlspecialchars($page_title) ?></h1>
        </div>
        <div class="content-inner">
            <?php require $page_file; ?>
        </div>
    </main>

</div>
<script src="assets/app.js"></script>
<script src="assets/finanzen.js"></script>
</body>
</html>