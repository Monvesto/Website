<?php
// ════════════════════════════════════════════════
// tasks.php – Aufgaben mit Bulk-Edit + Person-Filter
// Neue Aufgabe als tfoot in der Tabelle
// ════════════════════════════════════════════════
$db     = get_db();
$person = $_GET['person'] ?? 'Marcel';
if (!in_array($person, ['Marcel','Kim','Beide'], true)) $person = 'Marcel';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';
    $pf  = $_POST['person_filter'] ?? $person;

    if ($act === 'save') {
        $task = trim($_POST['task'] ?? '');
        $cat  = trim($_POST['category'] ?? '');
        $prio = $_POST['priority'] ?? 'Mittel';
        $resp = $_POST['responsible'] ?? 'Marcel';
        $it   = $_POST['interval_type'] ?? '';
        $due  = ($_POST['due_date'] ?? '') !== '' ? $_POST['due_date'] : null;
        $no   = trim($_POST['notes'] ?? '');
        $id   = (int)($_POST['edit_id'] ?? 0);
        if ($id > 0) {
            $db->prepare("UPDATE tasks SET task=?,category=?,priority=?,responsible=?,interval_type=?,due_date=?,notes=?,person=? WHERE id=?")->execute([$task,$cat,$prio,$resp,$it,$due,$no,$resp,$id]);
        } else {
            $db->prepare("INSERT INTO tasks (task,category,priority,responsible,interval_type,due_date,notes,person,status) VALUES (?,?,?,?,?,?,?,?,'Offen')")->execute([$task,$cat,$prio,$resp,$it,$due,$no,$resp]);
        }
        header("Location: ?page=tasks&person=$pf&msg=saved"); exit;
    }

    if ($act === 'bulk_save') {
        foreach ($_POST['ids'] ?? [] as $id) {
            $id   = (int)$id;
            $row  = $_POST['rows'][$id] ?? [];
            $task = trim($row['task'] ?? '');
            $cat  = trim($row['category'] ?? '');
            $prio = $row['priority'] ?? 'Mittel';
            $resp = $row['responsible'] ?? 'Marcel';
            $it   = $row['interval_type'] ?? '';
            $due  = ($row['due_date'] ?? '') !== '' ? $row['due_date'] : null;
            $no   = trim($row['notes'] ?? '');
            if ($task === '') continue;
            $db->prepare("UPDATE tasks SET task=?,category=?,priority=?,responsible=?,interval_type=?,due_date=?,notes=?,person=? WHERE id=?")->execute([$task,$cat,$prio,$resp,$it,$due,$no,$resp,$id]);
        }
        header("Location: ?page=tasks&person=$pf&msg=saved"); exit;
    }

    if ($act === 'set_status') {
        $db->prepare("UPDATE tasks SET status=? WHERE id=?")->execute([$_POST['status'], (int)$_POST['id']]);
        header("Location: ?page=tasks&person=$pf&msg=saved"); exit;
    }

    if ($act === 'delete') {
        $db->prepare("DELETE FROM tasks WHERE id=?")->execute([(int)$_POST['id']]);
        header("Location: ?page=tasks&person=$pf&msg=saved"); exit;
    }
}
if (defined('HANDLE_POST_ONLY')) return;

function get_tasks(PDO $db, string $status, string $person, int $limit = 0): array {
    $lim = $limit > 0 ? "LIMIT $limit" : '';
    $ord = $status === 'Offen' ? 'due_date ASC' : 'updated_at DESC';
    if ($person === 'Beide') {
        return $db->query("SELECT * FROM tasks WHERE status='$status' ORDER BY $ord $lim")->fetchAll();
    }
    $s = $db->prepare("SELECT * FROM tasks WHERE status=? AND (person=? OR person='Beide') ORDER BY $ord $lim");
    $s->execute([$status, $person]);
    return $s->fetchAll();
}

$offen    = get_tasks($db, 'Offen', $person);
$erledigt = get_tasks($db, 'Erledigt', $person, 20);

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

$personen     = ['Marcel','Kim','Beide'];
$prioritaeten = ['Hoch','Mittel','Niedrig'];
$def_person   = $person === 'Beide' ? 'Marcel' : $person;
?>

<div class="finance-topbar">
    <div class="tab-bar"></div>
    <div class="person-switcher">
        <?php foreach (['Marcel','Kim','Beide'] as $p): ?>
        <a href="?page=tasks&person=<?= $p ?>" class="person-btn <?= $person===$p?'active':'' ?>"><?= $p ?></a>
        <?php endforeach; ?>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">Gespeichert.</div><?php endif; ?>

<div class="card mt-4" id="card-tasks">
    <div class="card-head">
        <div class="card-head-left">
            <h2 class="card-title">✅ Offene Aufgaben<?= $person!=='Beide'?' – '.$person:'' ?></h2>
            <div class="badge-row">
                <span class="badge badge-neutral"><?= count($offen) ?> offen</span>
                <?php if (count($overdue)): ?><span class="badge badge-danger"><?= count($overdue) ?> überfällig</span><?php endif; ?>
            </div>
        </div>
        <div class="bulk-bar">
            <button type="button" class="btn btn-ghost btn-sm" id="btn-edit-t">✏ Bearbeiten</button>
            <button type="submit" form="frm-t-bulk" class="btn btn-primary btn-sm btn-hidden" id="btn-save-t">✓ Speichern</button>
        </div>
    </div>
    <form id="frm-t-bulk" method="POST" action="?page=tasks">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="bulk_save">
        <input type="hidden" name="person_filter" value="<?= he_t($person) ?>">
        <?php foreach ($offen as $t): ?>
        <input type="hidden" name="ids[]" value="<?= $t['id'] ?>">
        <?php endforeach; ?>
    </form>
    <div class="table-wrap"><table class="data-table">
        <thead><tr>
            <th>Aufgabe</th><th>Kategorie</th><th>Priorität</th><th>Zuständig</th><th>Fällig</th><th></th>
        </tr></thead>
        <tbody>
        <?php foreach ($offen as $t): $tid = $t['id']; ?>
        <tr>
            <td>
                <span class="ft-bulk"><?= he_t($t['task']) ?></span>
                <input class="inline-input fi-bulk" form="frm-t-bulk" name="rows[<?= $tid ?>][task]" value="<?= he_t($t['task']) ?>" required>
            </td>
            <td>
                <span class="ft-bulk"><?= he_t($t['category']??'–') ?></span>
                <input class="inline-input fi-bulk" form="frm-t-bulk" name="rows[<?= $tid ?>][category]" value="<?= he_t($t['category']??'') ?>">
            </td>
            <td>
                <span class="ft-bulk"><?= prio_badge_t($t['priority']) ?></span>
                <select class="inline-input fi-bulk" form="frm-t-bulk" name="rows[<?= $tid ?>][priority]">
                    <?php foreach ($prioritaeten as $p): ?>
                    <option value="<?= $p ?>"<?= $p===$t['priority']?' selected':'' ?>><?= $p ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <span class="ft-bulk"><?= he_t($t['responsible']??'–') ?></span>
                <select class="inline-input fi-bulk" form="frm-t-bulk" name="rows[<?= $tid ?>][responsible]">
                    <?php foreach ($personen as $p): ?>
                    <option value="<?= $p ?>"<?= $p===$t['responsible']?' selected':'' ?>><?= $p ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <span class="ft-bulk"><?= fmt_due_t($t['due_date']) ?></span>
                <input class="inline-input fi-bulk" type="date" form="frm-t-bulk" name="rows[<?= $tid ?>][due_date]" value="<?= he_t($t['due_date']??'') ?>">
            </td>
            <td class="col-actions">
                <form method="POST" action="?page=tasks" class="form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="set_status">
                    <input type="hidden" name="id" value="<?= $tid ?>">
                    <input type="hidden" name="status" value="Erledigt">
                    <input type="hidden" name="person_filter" value="<?= he_t($person) ?>">
                    <button type="submit" class="btn btn-ok btn-xs">✓</button>
                </form>
                <form id="frm-t-del-<?= $tid ?>" method="POST" action="?page=tasks" hidden>
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="delete">
                    <input type="hidden" name="id" value="<?= $tid ?>">
                    <input type="hidden" name="person_filter" value="<?= he_t($person) ?>">
                </form>
                <button type="submit" form="frm-t-del-<?= $tid ?>" class="btn btn-danger btn-xs fi-bulk btn-delete-confirm">✕</button>
            </td>
        </tr>
        <?php if (!empty($t['notes'])): ?>
        <tr class="row-notiz">
            <td colspan="6" class="notiz-cell">
                <span class="ft-bulk">💬 <?= he_t($t['notes']) ?></span>
                <input class="inline-input fi-bulk" form="frm-t-bulk" name="rows[<?= $tid ?>][notes]" value="<?= he_t($t['notes']??'') ?>" placeholder="Notiz">
            </td>
        </tr>
        <?php else: ?>
        <tr class="row-notiz">
            <td colspan="6" class="notiz-cell">
                <span class="ft-bulk"></span>
                <input class="inline-input fi-bulk" form="frm-t-bulk" name="rows[<?= $tid ?>][notes]" value="" placeholder="Notiz">
            </td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
        <?php if (empty($offen)): ?>
        <tr><td colspan="6" class="empty-state">Keine offenen Aufgaben.</td></tr>
        <?php endif; ?>
        </tbody>
    </table></div>
</div>

<!-- Neue Aufgabe Card -->
<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Neue Aufgabe</h2></div>
    <form id="frm-t-new" method="POST" action="?page=tasks">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="save">
        <input type="hidden" name="edit_id" value="0">
        <input type="hidden" name="person_filter" value="<?= he_t($person) ?>">
        <input type="hidden" name="notes" value="">
        <input type="hidden" name="interval_type" value="">
    </form>
    <div class="table-wrap"><table class="data-table">
        <thead><tr>
            <th>Aufgabe</th><th>Kategorie</th><th>Priorität</th><th>Zuständig</th><th>Fällig</th><th></th>
        </tr></thead>
        <tbody><tr>
            <td><input class="inline-input new-input" form="frm-t-new" name="task" placeholder="Aufgabe" required></td>
            <td><input class="inline-input new-input" form="frm-t-new" name="category" placeholder="Kategorie"></td>
            <td><select class="inline-input new-input" form="frm-t-new" name="priority">
                <?php foreach ($prioritaeten as $p): ?><option<?= $p==='Mittel'?' selected':'' ?>><?= $p ?></option><?php endforeach; ?>
            </select></td>
            <td><select class="inline-input new-input" form="frm-t-new" name="responsible">
                <?php foreach ($personen as $p): ?><option value="<?= $p ?>"<?= $p===$def_person?' selected':'' ?>><?= $p ?></option><?php endforeach; ?>
            </select></td>
            <td><input class="inline-input new-input" type="date" form="frm-t-new" name="due_date"></td>
            <td class="col-actions">
                <button type="button" class="btn btn-primary btn-xs" id="btn-new-t">+ Hinzufügen</button>
            </td>
        </tr></tbody>
    </table></div>
</div>

<?php if (!empty($erledigt)): ?>
<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Erledigte Aufgaben</h2><span class="badge badge-ok">Letzte 20</span></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Aufgabe</th><th>Kategorie</th><th>Priorität</th><th>Zuständig</th><th>Erledigt am</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($erledigt as $t): ?>
        <tr class="row-done">
            <td><?= he_t($t['task']) ?></td>
            <td><?= he_t($t['category'] ?? '–') ?></td>
            <td><?= prio_badge_t($t['priority']) ?></td>
            <td><?= he_t($t['responsible']??'–') ?></td>
            <td><?= $t['updated_at'] ? date('d.m.Y', strtotime($t['updated_at'])) : '–' ?></td>
            <td class="col-actions">
                <form method="POST" action="?page=tasks" class="form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="set_status">
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <input type="hidden" name="status" value="Offen">
                    <input type="hidden" name="person_filter" value="<?= he_t($person) ?>">
                    <button type="submit" class="btn btn-ghost btn-xs">↩ Offen</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>