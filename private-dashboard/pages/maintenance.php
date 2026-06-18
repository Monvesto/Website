<?php
$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';

    if ($act === 'save') {
        $on = $_POST['object_name'] ?? '';
        $ta = $_POST['task'] ?? '';
        $it = $_POST['interval_type'] ?? '';
        $ld = $_POST['last_done'] ?: null;
        $nd = $_POST['next_due'] ?: null;
        $no = trim($_POST['notes'] ?? '');
        $id = (int)($_POST['edit_id'] ?? 0);
        if ($id > 0) {
            $db->prepare("UPDATE maintenance SET object_name=?,task=?,interval_type=?,last_done=?,next_due=?,notes=? WHERE id=?")->execute([$on,$ta,$it,$ld,$nd,$no,$id]);
        } else {
            $db->prepare("INSERT INTO maintenance (object_name,task,interval_type,last_done,next_due,notes) VALUES (?,?,?,?,?,?)")->execute([$on,$ta,$it,$ld,$nd,$no]);
        }
        header("Location: ?page=maintenance&msg=saved"); exit;
    }

    if ($act === 'done') {
        $id = (int)$_POST['id'];
        $interval = $_POST['interval_type'] ?? '';
        $today = date('Y-m-d');
        $next_map = [
            'monatlich'     => date('Y-m-d', strtotime('+1 month')),
            'quartalsweise' => date('Y-m-d', strtotime('+3 months')),
            'halbjährlich'  => date('Y-m-d', strtotime('+6 months')),
            'jährlich'      => date('Y-m-d', strtotime('+1 year')),
        ];
        $next = $next_map[$interval] ?? null;
        $db->prepare("UPDATE maintenance SET last_done=?, next_due=?, status='OK' WHERE id=?")->execute([$today, $next, $id]);
        header("Location: ?page=maintenance&msg=saved"); exit;
    }

    if ($act === 'delete') {
        $db->prepare("DELETE FROM maintenance WHERE id=?")->execute([(int)$_POST['id']]);
        header("Location: ?page=maintenance&msg=saved"); exit;
    }
}
if (defined('HANDLE_POST_ONLY')) return;

$db->exec("UPDATE maintenance SET status = CASE
    WHEN next_due < CURDATE() THEN 'Überfällig'
    WHEN next_due >= CURDATE() AND next_due <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Bald fällig'
    ELSE 'OK' END WHERE next_due IS NOT NULL");

$items = $db->query("SELECT * FROM maintenance ORDER BY next_due ASC")->fetchAll();
function he_m(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
$intervalle = ['einmalig','monatlich','quartalsweise','halbjährlich','jährlich'];
?>

<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">Gespeichert.</div><?php endif; ?>

<div class="card">
    <div class="card-head">
        <h2 class="card-title">🔧 Wartungen</h2>
        <?php $ue=[]; foreach($items as $i) if($i['status']==='Überfällig') $ue[]=$i; ?>
        <?php if (count($ue)): ?><span class="badge badge-danger"><?= count($ue) ?> überfällig</span><?php endif; ?>
    </div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Objekt</th><th>Aufgabe</th><th>Intervall</th><th>Zuletzt</th><th>Nächste</th><th>Status</th><th>Notiz</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($items as $i): ?>
        <tr>
            <td><?= he_m($i['object_name']) ?></td>
            <td><?= he_m($i['task']) ?></td>
            <td><?= he_m($i['interval_type'] ?? '–') ?></td>
            <td><?= $i['last_done'] ? date('d.m.Y', strtotime($i['last_done'])) : '–' ?></td>
            <td><?= $i['next_due']  ? date('d.m.Y', strtotime($i['next_due']))  : '–' ?></td>
            <td><?php
                $cls_map = ['Überfällig' => 'badge-danger', 'Bald fällig' => 'badge-warning'];
                $cls = $cls_map[$i['status']] ?? 'badge-ok';
                echo '<span class="badge '.$cls.'">'.he_m($i['status']).'</span>';
            ?></td>
            <td><?= he_m($i['notes'] ?? '–') ?></td>
            <td class="col-actions">
                <form method="POST" action="?page=maintenance" class="form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="done">
                    <input type="hidden" name="id" value="<?= $i['id'] ?>">
                    <input type="hidden" name="interval_type" value="<?= he_m($i['interval_type'] ?? '') ?>">
                    <button type="submit" class="btn btn-ok btn-xs">✓ Erledigt</button>
                </form>
                <form method="POST" action="?page=maintenance" class="form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="delete">
                    <input type="hidden" name="id" value="<?= $i['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-xs btn-delete-confirm">✕</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>

<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Neue Wartung</h2></div>
    <form method="POST" action="?page=maintenance">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="save">
        <input type="hidden" name="edit_id" value="0">
        <div class="form-grid">
            <div class="form-group"><label>Objekt</label><input type="text" name="object_name" required></div>
            <div class="form-group"><label>Aufgabe</label><input type="text" name="task" required></div>
            <div class="form-group"><label>Intervall</label>
                <select name="interval_type">
                    <?php foreach ($intervalle as $iv): ?><option><?= $iv ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Zuletzt erledigt</label><input type="date" name="last_done"></div>
            <div class="form-group"><label>Nächste Fälligkeit</label><input type="date" name="next_due"></div>
            <div class="form-group fg-wide"><label>Notiz</label><input type="text" name="notes"></div>
        </div>
        <div class="form-actions form-actions--pad">
            <button type="submit" class="btn btn-primary">Speichern</button>
        </div>
    </form>
</div>