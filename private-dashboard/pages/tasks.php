<?php
$db      = get_db();
$action  = $_GET['action'] ?? 'list';
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';

    if ($act === 'done' && isset($_POST['id'])) {
        $stmt = $db->prepare("UPDATE tasks SET status='Erledigt', last_done=CURDATE() WHERE id=?");
        $stmt->execute([(int)$_POST['id']]);
        header('Location: ?page=tasks&msg=done');
        exit;
    }

    if ($act === 'delete' && isset($_POST['id'])) {
        $stmt = $db->prepare('DELETE FROM tasks WHERE id=?');
        $stmt->execute([(int)$_POST['id']]);
        header('Location: ?page=tasks&msg=deleted');
        exit;
    }

    if (in_array($act, ['create', 'update'], true)) {
        $task_val    = trim($_POST['task']          ?? '');
        $category    = trim($_POST['category']      ?? '');
        $priority    = $_POST['priority']            ?? 'Mittel';
        $responsible = trim($_POST['responsible']   ?? '');
        $interval    = trim($_POST['interval_type'] ?? '');
        $last_done   = $_POST['last_done']           ?: null;
        $due_date    = $_POST['due_date']            ?: null;
        $status      = $_POST['status']              ?? 'Offen';
        $notes       = trim($_POST['notes']         ?? '');

        if ($task_val === '') $errors[] = 'Aufgabe darf nicht leer sein.';
        if (!in_array($priority, ['Hoch','Mittel','Niedrig'], true)) $errors[] = 'Ungültige Priorität.';
        if (!in_array($status,   ['Offen','Erledigt'],         true)) $errors[] = 'Ungültiger Status.';

        if (empty($errors)) {
            if ($act === 'create') {
                $stmt = $db->prepare(
                    'INSERT INTO tasks (task,category,priority,responsible,interval_type,last_done,due_date,status,notes)
                     VALUES (?,?,?,?,?,?,?,?,?)'
                );
                $stmt->execute([$task_val,$category,$priority,$responsible,$interval,$last_done,$due_date,$status,$notes]);
            } else {
                $stmt = $db->prepare(
                    'UPDATE tasks SET task=?,category=?,priority=?,responsible=?,interval_type=?,
                     last_done=?,due_date=?,status=?,notes=? WHERE id=?'
                );
                $stmt->execute([$task_val,$category,$priority,$responsible,$interval,$last_done,$due_date,$status,$notes,(int)$_POST['edit_id']]);
            }
            header('Location: ?page=tasks&msg=saved');
            exit;
        }
        $action = ($act === 'create') ? 'new' : 'edit';
    }
}

$msgs = ['saved' => 'Aufgabe gespeichert.', 'done' => 'Als erledigt markiert.', 'deleted' => 'Aufgabe gelöscht.'];
if (isset($_GET['msg'], $msgs[$_GET['msg']])) {
    $success = $msgs[$_GET['msg']];
}

$edit_row = null;
if ($action === 'edit' && $task_id) {
    $stmt     = $db->prepare('SELECT * FROM tasks WHERE id=?');
    $stmt->execute([$task_id]);
    $edit_row = $stmt->fetch();
    if (!$edit_row) { $action = 'list'; }
}

$filter_status = $_GET['filter'] ?? 'Offen';
$allowed_filters = ['Offen','Erledigt','alle'];
if (!in_array($filter_status, $allowed_filters, true)) $filter_status = 'Offen';

if ($filter_status === 'alle') {
    $tasks = $db->query('SELECT * FROM tasks ORDER BY due_date ASC, priority ASC')->fetchAll();
} else {
    $stmt = $db->prepare('SELECT * FROM tasks WHERE status=? ORDER BY due_date ASC');
    $stmt->execute([$filter_status]);
    $tasks = $stmt->fetchAll();
}

function val(array $row, string $k, string $default = ''): string {
    $v = isset($_POST[$k]) ? $_POST[$k] : ($row[$k] ?? $default);
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if ($errors): ?>
    <div class="alert alert-error"><?= implode('<br>', array_map(fn($e) => htmlspecialchars($e, ENT_QUOTES, 'UTF-8'), $errors)) ?></div>
<?php endif; ?>

<?php if ($action === 'new' || $action === 'edit'): ?>
<div class="card">
    <div class="card-head">
        <h2 class="card-title"><?= $action === 'new' ? 'Neue Aufgabe' : 'Aufgabe bearbeiten' ?></h2>
        <a href="?page=tasks" class="link-subtle">← Zurück</a>
    </div>
    <form method="POST" action="?page=tasks" class="form-grid">
        <?= csrf_field() ?>
        <input type="hidden" name="act"     value="<?= $action === 'new' ? 'create' : 'update' ?>">
        <input type="hidden" name="edit_id" value="<?= $task_id ?>">

        <div class="form-group fg-wide">
            <label>Aufgabe *</label>
            <input type="text" name="task" value="<?= val($edit_row ?? [], 'task') ?>" required>
        </div>
        <div class="form-group">
            <label>Kategorie</label>
            <input type="text" name="category" value="<?= val($edit_row ?? [], 'category') ?>">
        </div>
        <div class="form-group">
            <label>Priorität</label>
            <select name="priority">
                <?php foreach (['Hoch','Mittel','Niedrig'] as $p): ?>
                <option value="<?= $p ?>" <?= val($edit_row ?? [], 'priority', 'Mittel') === $p ? 'selected' : '' ?>><?= $p ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Verantwortlich</label>
            <input type="text" name="responsible" value="<?= val($edit_row ?? [], 'responsible') ?>">
        </div>
        <div class="form-group">
            <label>Intervall</label>
            <input type="text" name="interval_type" placeholder="z.B. monatlich" value="<?= val($edit_row ?? [], 'interval_type') ?>">
        </div>
        <div class="form-group">
            <label>Fällig am</label>
            <input type="date" name="due_date" value="<?= val($edit_row ?? [], 'due_date') ?>">
        </div>
        <div class="form-group">
            <label>Zuletzt erledigt</label>
            <input type="date" name="last_done" value="<?= val($edit_row ?? [], 'last_done') ?>">
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <?php foreach (['Offen','Erledigt'] as $s): ?>
                <option value="<?= $s ?>" <?= val($edit_row ?? [], 'status', 'Offen') === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group fg-wide">
            <label>Notizen</label>
            <textarea name="notes" rows="3"><?= val($edit_row ?? [], 'notes') ?></textarea>
        </div>
        <div class="form-actions fg-wide">
            <button type="submit" class="btn btn-primary">Speichern</button>
            <a href="?page=tasks" class="btn btn-ghost">Abbrechen</a>
        </div>
    </form>
</div>

<?php else: ?>
<div class="card">
    <div class="card-head">
        <div style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap">
            <h2 class="card-title" style="margin:0">Aufgaben</h2>
            <div class="filter-tabs">
                <?php foreach (['Offen','Erledigt','alle'] as $f): ?>
                <a href="?page=tasks&filter=<?= $f ?>" class="filter-tab <?= $filter_status === $f ? 'active' : '' ?>">
                    <?= $f === 'alle' ? 'Alle' : $f ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <a href="?page=tasks&action=new" class="btn btn-primary btn-sm">+ Neue Aufgabe</a>
    </div>

    <?php if (empty($tasks)): ?>
        <p class="empty-state">Keine Aufgaben vorhanden.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Aufgabe</th><th>Kategorie</th><th>Priorität</th><th>Fällig</th><th>Status</th><th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $t): ?>
                <tr class="<?= $t['status'] === 'Erledigt' ? 'row-done' : '' ?>">
                    <td>
                        <?= htmlspecialchars($t['task'], ENT_QUOTES, 'UTF-8') ?>
                        <?php if ($t['notes']): ?>
                            <span class="has-notes" title="<?= htmlspecialchars($t['notes'], ENT_QUOTES, 'UTF-8') ?>">●</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($t['category'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= priority_badge($t['priority']) ?></td>
                    <td><?= format_date($t['due_date']) ?></td>
                    <td><?= htmlspecialchars($t['status'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="actions-cell">
                        <a href="?page=tasks&action=edit&id=<?= $t['id'] ?>" class="btn btn-ghost btn-xs">Bearbeiten</a>
                        <?php if ($t['status'] === 'Offen'): ?>
                        <form method="POST" action="?page=tasks" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="act" value="done">
                            <input type="hidden" name="id"  value="<?= $t['id'] ?>">
                            <button type="submit" class="btn btn-ok btn-xs">✓</button>
                        </form>
                        <?php endif; ?>
                        <form method="POST" action="?page=tasks" style="display:inline"
                              onsubmit="return confirm('Aufgabe wirklich löschen?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="act" value="delete">
                            <input type="hidden" name="id"  value="<?= $t['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-xs">✕</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>