<?php
$db       = get_db();
$action   = $_GET['action'] ?? 'list';
$maint_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors   = [];
$success  = '';

$db->exec("
    UPDATE maintenance SET status = CASE
        WHEN next_due < CURDATE()                                                       THEN 'Überfällig'
        WHEN next_due >= CURDATE() AND next_due <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Bald fällig'
        ELSE 'OK'
    END
    WHERE next_due IS NOT NULL
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';

    if ($act === 'done' && isset($_POST['id'])) {
        $stmt = $db->prepare("UPDATE maintenance SET last_done=CURDATE(), status='OK' WHERE id=?");
        $stmt->execute([(int)$_POST['id']]);
        header('Location: ?page=maintenance&msg=done');
        exit;
    }

    if ($act === 'delete' && isset($_POST['id'])) {
        $stmt = $db->prepare('DELETE FROM maintenance WHERE id=?');
        $stmt->execute([(int)$_POST['id']]);
        header('Location: ?page=maintenance&msg=deleted');
        exit;
    }

    if (in_array($act, ['create', 'update'], true)) {
        $object  = trim($_POST['object_name']   ?? '');
        $task    = trim($_POST['task']          ?? '');
        $interval= trim($_POST['interval_type'] ?? '');
        $last    = $_POST['last_done']           ?: null;
        $next    = $_POST['next_due']            ?: null;
        $status  = $_POST['status']              ?? 'OK';
        $notes   = trim($_POST['notes']         ?? '');

        if ($object === '') $errors[] = 'Objekt darf nicht leer sein.';
        if ($task   === '') $errors[] = 'Aufgabe darf nicht leer sein.';
        if (!in_array($status, ['OK','Bald fällig','Überfällig'], true)) $errors[] = 'Ungültiger Status.';

        if (empty($errors)) {
            if ($act === 'create') {
                $stmt = $db->prepare(
                    'INSERT INTO maintenance (object_name,task,interval_type,last_done,next_due,status,notes)
                     VALUES (?,?,?,?,?,?,?)'
                );
                $stmt->execute([$object,$task,$interval,$last,$next,$status,$notes]);
            } else {
                $stmt = $db->prepare(
                    'UPDATE maintenance SET object_name=?,task=?,interval_type=?,last_done=?,
                     next_due=?,status=?,notes=? WHERE id=?'
                );
                $stmt->execute([$object,$task,$interval,$last,$next,$status,$notes,(int)$_POST['edit_id']]);
            }
            header('Location: ?page=maintenance&msg=saved');
            exit;
        }
        $action = ($act === 'create') ? 'new' : 'edit';
    }

    if (defined('HANDLE_POST_ONLY')) return;
}

$msgs = ['saved' => 'Wartung gespeichert.', 'done' => 'Als erledigt markiert.', 'deleted' => 'Wartung gelöscht.'];
if (isset($_GET['msg'], $msgs[$_GET['msg']])) {
    $success = $msgs[$_GET['msg']];
}

$edit_row = null;
if ($action === 'edit' && $maint_id) {
    $stmt     = $db->prepare('SELECT * FROM maintenance WHERE id=?');
    $stmt->execute([$maint_id]);
    $edit_row = $stmt->fetch();
    if (!$edit_row) { $action = 'list'; }
}

$filter = $_GET['filter'] ?? 'alle';
$allowed_f = ['alle','OK','Bald fällig','Überfällig'];
if (!in_array($filter, $allowed_f, true)) $filter = 'alle';

if ($filter === 'alle') {
    $rows = $db->query('SELECT * FROM maintenance ORDER BY next_due ASC')->fetchAll();
} else {
    $stmt = $db->prepare('SELECT * FROM maintenance WHERE status=? ORDER BY next_due ASC');
    $stmt->execute([$filter]);
    $rows = $stmt->fetchAll();
}

function mval(array $row, string $k, string $default = ''): string {
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
        <h2 class="card-title"><?= $action === 'new' ? 'Neue Wartung' : 'Wartung bearbeiten' ?></h2>
        <a href="?page=maintenance" class="link-subtle">← Zurück</a>
    </div>
    <form method="POST" action="?page=maintenance" class="form-grid">
        <?= csrf_field() ?>
        <input type="hidden" name="act"     value="<?= $action === 'new' ? 'create' : 'update' ?>">
        <input type="hidden" name="edit_id" value="<?= $maint_id ?>">

        <div class="form-group">
            <label>Objekt *</label>
            <input type="text" name="object_name" value="<?= mval($edit_row ?? [], 'object_name') ?>" required placeholder="z.B. Heizungsanlage">
        </div>
        <div class="form-group">
            <label>Aufgabe *</label>
            <input type="text" name="task" value="<?= mval($edit_row ?? [], 'task') ?>" required placeholder="z.B. Filterwechsel">
        </div>
        <div class="form-group">
            <label>Intervall</label>
            <input type="text" name="interval_type" placeholder="z.B. jährlich" value="<?= mval($edit_row ?? [], 'interval_type') ?>">
        </div>
        <div class="form-group">
            <label>Zuletzt erledigt</label>
            <input type="date" name="last_done" value="<?= mval($edit_row ?? [], 'last_done') ?>">
        </div>
        <div class="form-group">
            <label>Nächste Fälligkeit</label>
            <input type="date" name="next_due" value="<?= mval($edit_row ?? [], 'next_due') ?>">
        </div>
        <div class="form-group">
            <label>Status (wird automatisch berechnet)</label>
            <select name="status">
                <?php foreach (['OK','Bald fällig','Überfällig'] as $s): ?>
                <option value="<?= $s ?>" <?= mval($edit_row ?? [], 'status', 'OK') === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group fg-wide">
            <label>Notizen</label>
            <textarea name="notes" rows="3"><?= mval($edit_row ?? [], 'notes') ?></textarea>
        </div>
        <div class="form-actions fg-wide">
            <button type="submit" class="btn btn-primary">Speichern</button>
            <a href="?page=maintenance" class="btn btn-ghost">Abbrechen</a>
        </div>
    </form>
</div>

<?php else: ?>
<div class="card">
    <div class="card-head">
        <div style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap">
            <h2 class="card-title" style="margin:0">Wartungen</h2>
            <div class="filter-tabs">
                <?php foreach (['alle','OK','Bald fällig','Überfällig'] as $f): ?>
                <a href="?page=maintenance&filter=<?= urlencode($f) ?>"
                   class="filter-tab <?= $filter === $f ? 'active' : '' ?>">
                    <?= htmlspecialchars($f, ENT_QUOTES, 'UTF-8') ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <a href="?page=maintenance&action=new" class="btn btn-primary btn-sm">+ Neue Wartung</a>
    </div>

    <?php if (empty($rows)): ?>
        <p class="empty-state">Keine Wartungen vorhanden.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Objekt</th><th>Aufgabe</th><th>Intervall</th>
                    <th>Zuletzt erledigt</th><th>Nächste Fälligkeit</th><th>Status</th><th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['object_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?= htmlspecialchars($m['task'], ENT_QUOTES, 'UTF-8') ?>
                        <?php if ($m['notes']): ?>
                            <span class="has-notes" title="<?= htmlspecialchars($m['notes'], ENT_QUOTES, 'UTF-8') ?>">●</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($m['interval_type'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= format_date($m['last_done']) ?></td>
                    <td><?= format_date($m['next_due']) ?></td>
                    <td><?= status_badge_m($m['status']) ?></td>
                    <td class="actions-cell">
                        <a href="?page=maintenance&action=edit&id=<?= $m['id'] ?>" class="btn btn-ghost btn-xs">Bearbeiten</a>
                        <form method="POST" action="?page=maintenance" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="act" value="done">
                            <input type="hidden" name="id"  value="<?= $m['id'] ?>">
                            <button type="submit" class="btn btn-ok btn-xs">✓</button>
                        </form>
                        <form method="POST" action="?page=maintenance" style="display:inline"
                              onsubmit="return confirm('Wartung wirklich löschen?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="act" value="delete">
                            <input type="hidden" name="id"  value="<?= $m['id'] ?>">
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