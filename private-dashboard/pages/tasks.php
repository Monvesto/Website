<?php
$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';

    if ($act === 'save') {
        $task = trim($_POST['task'] ?? '');
        $cat  = trim($_POST['category'] ?? '');
        $prio = $_POST['priority'] ?? 'Mittel';
        $resp = trim($_POST['responsible'] ?? '');
        $it   = $_POST['interval_type'] ?? '';
        $due  = $_POST['due_date'] !== '' ? $_POST['due_date'] : null;
        $no   = trim($_POST['notes'] ?? '');
        $id   = (int)($_POST['edit_id'] ?? 0);
        if ($id > 0) {
            $db->prepare("UPDATE tasks SET task=?,category=?,priority=?,responsible=?,interval_type=?,due_date=?,notes=? WHERE id=?")->execute([$task,$cat,$prio,$resp,$it,$due,$no,$id]);
        } else {
            $db->prepare("INSERT INTO tasks (task,category,priority,responsible,interval_type,due_date,notes,status) VALUES (?,?,?,?,?,?,?,'Offen')")->execute([$task,$cat,$prio,$resp,$it,$due,$no]);
        }
        header("Location: ?page=tasks&msg=saved"); exit;
    }

    if ($act === 'set_status') {
        $db->prepare("UPDATE tasks SET status=? WHERE id=?")->execute([$_POST['status'], (int)$_POST['id']]);
        header("Location: ?page=tasks&msg=saved"); exit;
    }

    if ($act === 'delete') {
        $db->prepare("DELETE FROM tasks WHERE id=?")->execute([(int)$_POST['id']]);
        header("Location: ?page=tasks&msg=saved"); exit;
    }
}
if (defined('HANDLE_POST_ONLY')) return;

$offen    = $db->query("SELECT * FROM tasks WHERE status='Offen' ORDER BY due_date ASC")->fetchAll();
$erledigt = $db->query("SELECT * FROM tasks WHERE status='Erledigt' ORDER BY updated_at DESC LIMIT 20")->fetchAll();

$overdue = [];
foreach ($offen as $t) {
    if ($t['due_date'] && $t['due_date'] < date('Y-m-d')) $overdue[] = $t;
}

function he_t(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function prio_badge_t(string $p): string {
    $map = ['Hoch'=>'badge-danger','Mittel'=>'badge-warning','Niedrig'=>'badge-neutral'];
    return '<span class="badge '.($map[$p]??'badge-neutral').'">'.he_t($p).'</span>';
}
function fmt_due_t(?string $d): string {
    if (!$d) return '<span class="text-muted">–</span>';
    $diff = (strtotime($d) - strtotime('today')) / 86400;
    $fmt  = date('d.m.Y', strtotime($d));
    if ($diff < 0)  return '<span class="date-overdue">'.$fmt.'</span>';
    if ($diff <= 7) return '<span class="date-soon">'.$fmt.'</span>';
    return $fmt;
}
?>

<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">Gespeichert.</div><?php endif; ?>

<!-- Offene Aufgaben -->
<div class="card">
    <div class="card-head">
        <h2 class="card-title">✅ Offene Aufgaben</h2>
        <div class="bulk-bar">
            <span class="badge badge-neutral"><?= count($offen) ?> offen</span>
            <?php if (count($overdue)): ?><span class="badge badge-danger"><?= count($overdue) ?> überfällig</span><?php endif; ?>
        </div>
    </div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Aufgabe</th><th>Kategorie</th><th>Priorität</th><th>Zuständig</th><th>Fällig</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($offen as $t): ?>
        <tr>
            <td><?= he_t($t['task']) ?></td>
            <td><?= he_t($t['category'] ?? '–') ?></td>
            <td><?= prio_badge_t($t['priority']) ?></td>
            <td><?= he_t($t['responsible'] ?? '–') ?></td>
            <td><?= fmt_due_t($t['due_date']) ?></td>
            <td class="col-actions">
                <form method="POST" action="?page=tasks" class="form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="set_status">
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <input type="hidden" name="status" value="Erledigt">
                    <button type="submit" class="btn btn-ok btn-xs">✓ Erledigt</button>
                </form>
                <form method="POST" action="?page=tasks" class="form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="delete">
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-xs btn-delete-confirm">✕</button>
                </form>
            </td>
        </tr>
        <?php if (!empty($t['notes'])): ?>
        <tr class="row-notiz">
            <td colspan="6" class="notiz-cell">💬 <?= he_t($t['notes']) ?></td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
        <?php if (empty($offen)): ?>
        <tr><td colspan="7" class="empty-state">Keine offenen Aufgaben.</td></tr>
        <?php endif; ?>
        </tbody>
    </table></div>
</div>

<!-- Neue Aufgabe -->
<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Neue Aufgabe</h2></div>
    <form method="POST" action="?page=tasks">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="save">
        <input type="hidden" name="edit_id" value="0">
        <div class="form-grid">
            <div class="form-group fg-wide"><label>Aufgabe</label><input type="text" name="task" required></div>
            <div class="form-group"><label>Kategorie</label><input type="text" name="category" placeholder="z.B. Haushalt"></div>
            <div class="form-group"><label>Priorität</label>
                <select name="priority"><option>Hoch</option><option selected>Mittel</option><option>Niedrig</option></select>
            </div>
            <div class="form-group"><label>Zuständig</label><input type="text" name="responsible"></div>
            <div class="form-group"><label>Fällig am</label><input type="date" name="due_date"></div>
            <div class="form-group"><label>Intervall</label>
                <select name="interval_type">
                    <option value="">einmalig</option>
                    <option>monatlich</option>
                    <option>quartalsweise</option>
                    <option>jährlich</option>
                </select>
            </div>
            <div class="form-group fg-wide"><label>Notiz</label><textarea name="notes" rows="2"></textarea></div>
        </div>
        <div class="form-actions form-actions--pad">
            <button type="submit" class="btn btn-primary">Aufgabe anlegen</button>
        </div>
    </form>
</div>

<!-- Erledigte Aufgaben -->
<?php if (!empty($erledigt)): ?>
<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Erledigte Aufgaben</h2><span class="badge badge-ok">Letzte 20</span></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Aufgabe</th><th>Kategorie</th><th>Priorität</th><th>Erledigt am</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($erledigt as $t): ?>
        <tr class="row-done">
            <td><?= he_t($t['task']) ?></td>
            <td><?= he_t($t['category'] ?? '–') ?></td>
            <td><?= prio_badge_t($t['priority']) ?></td>
            <td><?= $t['updated_at'] ? date('d.m.Y', strtotime($t['updated_at'])) : '–' ?></td>
            <td class="col-actions">
                <form method="POST" action="?page=tasks" class="form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="set_status">
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <input type="hidden" name="status" value="Offen">
                    <button type="submit" class="btn btn-ghost btn-xs">↩ Offen</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>