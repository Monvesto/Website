<?php
require_once __DIR__ . '/config/bootstrap.php';
require_login();

$page = $_GET['page'] ?? 'dashboard';
$allowed = ['dashboard', 'tasks', 'maintenance'];
if (!in_array($page, $allowed, true)) {
    $page = 'dashboard';
}

$page_file = __DIR__ . '/pages/' . $page . '.php';
$page_title = match($page) {
    'dashboard'   => 'Dashboard',
    'tasks'       => 'Aufgaben',
    'maintenance' => 'Wartungen',
    default       => 'Dashboard',
};
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($page_title) ?> – <?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/privat.css">
</head>
<body>

<div class="app-layout">
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span class="logo-mark">M</span>
            <span class="logo-text">monvesto <em>privat</em></span>
        </div>

        <ul class="nav-links">
            <li>
                <a href="?page=dashboard" class="nav-link <?= $page === 'dashboard' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="?page=tasks" class="nav-link <?= $page === 'tasks' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    Aufgaben
                </a>
            </li>
            <li>
                <a href="?page=maintenance" class="nav-link <?= $page === 'maintenance' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M4.93 19.07l1.41-1.41M19.07 19.07l-1.41-1.41M12 2v2M12 20v2M2 12h2M20 12h2"/>
                    </svg>
                    Wartungen
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <span class="sidebar-user">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16">
                    <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                </svg>
                <?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?>
            </span>
            <form method="POST" action="logout.php" style="margin:0">
                <?= csrf_field() ?>
                <button type="submit" class="btn-logout" title="Abmelden">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Abmelden
                </button>
            </form>
        </div>
    </nav>

    <main class="main-content">
        <div class="topbar">
            <button class="menu-toggle" id="menuToggle" aria-label="Menü öffnen">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
                    <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
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
</body>
</html>