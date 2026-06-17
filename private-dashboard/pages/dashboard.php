<?php
$db = get_db();

$open_tasks     = $db->query("SELECT COUNT(*) FROM tasks WHERE status='Offen'")->fetchColumn();
$overdue_tasks  = $db->query("SELECT COUNT(*) FROM tasks WHERE status='Offen' AND due_date < CURDATE()")->fetchColumn();
$overdue_maint  = $db->query("SELECT COUNT(*) FROM maintenance WHERE status='Überfällig'")->fetchColumn();
$next_maint_row = $db->query("SELECT object_name, task, next_due FROM maintenance WHERE next_due IS NOT NULL AND status != 'Überfällig' ORDER BY next_due ASC LIMIT 1")->fetch();

$next_tasks = $db->query(
    "SELECT task, category, priority, due_date, responsible
     FROM tasks WHERE status='Offen'
     ORDER BY due_date ASC LIMIT 7"
)->fetchAll();

$next_maintenances = $db->query(
    "SELECT object_name, task, next_due, status
     FROM maintenance
     WHERE next_due IS NOT NULL
     ORDER BY next_due ASC LIMIT 7"
)->fetchAll();

function priority_badge(string $p): string {
    $classes = ['Hoch' => 'badge-danger', 'Mittel' => 'badge-warning', 'Niedrig' => 'badge-neutral'];
    $cls = $classes[$p] ?? 'badge-neutral';
    return '<span class="badge ' . $cls . '">' . htmlspecialchars($p, ENT_QUOTES, 'UTF-8') . '</span>';
}

function status_badge_m(string $s): string {
    $classes = ['OK' => 'badge-ok', 'Bald fällig' => 'badge-warning', 'Überfällig' => 'badge-danger'];
    $cls = $classes[$s] ?? 'badge-neutral';
    return '<span class="badge ' . $cls . '">' . htmlspecialchars($s, ENT_QUOTES, 'UTF-8') . '</span>';
}

function format_date(?string $d): string {
    if (!$d) return '<span class="muted">–</span>';
    $ts = strtotime($d);
    $today = strtotime('today');
    $diff  = ($ts - $today) / 86400;
    $fmt   = date('d.m.Y', $ts);
    if ($diff < 0)  return '<span class="date-overdue">' . $fmt . '</span>';
    if ($diff <= 7) return '<span class="date-soon">' . $fmt . '</span>';
    return $fmt;
}
?>

<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-label">Offene Aufgaben</div>
        <div class="kpi-value"><?= (int)$open_tasks ?></div>
    </div>
    <div class="kpi-card kpi-card--alert">
        <div class="kpi-label">Überfällige Aufgaben</div>
        <div class="kpi-value"><?= (int)$overdue_tasks ?></div>
    </div>
    <div class="kpi-card kpi-card--alert">
        <div class="kpi-label">Überfällige Wartungen</div>
        <div class="kpi-value"><?= (int)$overdue_maint ?></div>
    </div>
    <div class="kpi-card kpi-card--info">
        <div class="kpi-label">Nächste Wartung</div>
        <div class="kpi-value kpi-value--sm">
            <?php if ($next_maint_row): ?>
                <?= htmlspecialchars($next_maint_row['object_name'], ENT_QUOTES, 'UTF-8') ?>
                <div class="kpi-sub"><?= format_date($next_maint_row['next_due']) ?></div>
            <?php else: ?>
                <span class="muted">–</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-head">
        <h2 class="card-title">Nächste Aufgaben</h2>
        <a href="?page=tasks" class="link-subtle">Alle ansehen →</a>
    </div>
    <?php if (empty($next_tasks)): ?>
        <p class="empty-state">Keine offenen Aufgaben.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Aufgabe</th><th>Kategorie</th><th>Priorität</th><th>Fällig</th><th>Verantwortlich</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($next_tasks as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['task'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($t['category'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= priority_badge($t['priority']) ?></td>
                    <td><?= format_date($t['due_date']) ?></td>
                    <td><?= htmlspecialchars($t['responsible'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="card mt-4">
    <div class="card-head">
        <h2 class="card-title">Nächste Wartungen</h2>
        <a href="?page=maintenance" class="link-subtle">Alle ansehen →</a>
    </div>
    <?php if (empty($next_maintenances)): ?>
        <p class="empty-state">Keine Wartungen eingetragen.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Objekt</th><th>Aufgabe</th><th>Nächste Fälligkeit</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($next_maintenances as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['object_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($m['task'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= format_date($m['next_due']) ?></td>
                    <td><?= status_badge_m($m['status']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>