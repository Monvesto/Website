<?php
// ════════════════════════════════════════════════
// maintenance.php – Wartungen mit Bulk-Edit + Person-Filter
// user_id Filter: alle Queries auf eingeloggten User beschränkt
// ════════════════════════════════════════════════
$db     = get_db();
$uid    = current_user_id();
$person_options = get_person_options();
$person = $_GET['person'] ?? ($person_options[0] ?? 'Marcel');
if (!in_array($person, $person_options, true)) $person = $person_options[0] ?? 'Marcel';
$is_all     = person_is_all($person);
$def_person = $is_all ? ($person_options[0] ?? 'Marcel') : $person;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';
    $pf  = $_POST['person_filter'] ?? $person;

    if ($act === 'save') {
        $on   = trim($_POST['object_name'] ?? '');
        $ta   = trim($_POST['task'] ?? '');
        $it   = $_POST['interval_type'] ?? '';
        $ld   = ($_POST['last_done'] ?? '') ?: null;
        $nd   = ($_POST['next_due'] ?? '') ?: null;
        $no   = trim($_POST['notes'] ?? '');
        $resp = $_POST['responsible'] ?? $def_person;
        $id   = (int)($_POST['edit_id'] ?? 0);
        if ($id > 0) {
            $db->prepare("UPDATE maintenance SET object_name=?,task=?,interval_type=?,last_done=?,next_due=?,notes=?,responsible=?,person=? WHERE id=? AND user_id=?")->execute([$on,$ta,$it,$ld,$nd,$no,$resp,$resp,$id,$uid]);
        } else {
            $db->prepare("INSERT INTO maintenance (user_id,object_name,task,interval_type,last_done,next_due,notes,responsible,person) VALUES (?,?,?,?,?,?,?,?,?)")->execute([$uid,$on,$ta,$it,$ld,$nd,$no,$resp,$resp]);
        }
        header("Location: ?page=maintenance&person=$pf&msg=saved"); exit;
    }

    if ($act === 'bulk_save') {
        foreach ($_POST['ids'] ?? [] as $id) {
            $id   = (int)$id;
            $row  = $_POST['rows'][$id] ?? [];
            $on   = trim($row['object_name'] ?? '');
            $ta   = trim($row['task'] ?? '');
            $it   = $row['interval_type'] ?? '';
            $ld   = ($row['last_done'] ?? '') ?: null;
            $nd   = ($row['next_due'] ?? '') ?: null;
            $no   = trim($row['notes'] ?? '');
            $resp = $row['responsible'] ?? $def_person;
            if ($on === '') continue;
            $db->prepare("UPDATE maintenance SET object_name=?,task=?,interval_type=?,last_done=?,next_due=?,notes=?,responsible=?,person=? WHERE id=? AND user_id=?")->execute([$on,$ta,$it,$ld,$nd,$no,$resp,$resp,$id,$uid]);
        }
        header("Location: ?page=maintenance&person=$pf&msg=saved"); exit;
    }

    if ($act === 'done') {
        $id       = (int)$_POST['id'];
        $interval = $_POST['interval_type'] ?? '';
        $today    = date('Y-m-d');
        $next_map = [
            'monatlich'     => date('Y-m-d', strtotime('+1 month')),
            'quartalsweise' => date('Y-m-d', strtotime('+3 months')),
            'halbjährlich'  => date('Y-m-d', strtotime('+6 months')),
            'jährlich'      => date('Y-m-d', strtotime('+1 year')),
        ];
        $next = $next_map[$interval] ?? null;
        $db->prepare("UPDATE maintenance SET last_done=?, next_due=?, status='OK' WHERE id=? AND user_id=?")->execute([$today, $next, $id, $uid]);
        header("Location: ?page=maintenance&person=$pf&msg=saved"); exit;
    }

    if ($act === 'delete') {
        $db->prepare("DELETE FROM maintenance WHERE id=? AND user_id=?")->execute([(int)$_POST['id'], $uid]);
        header("Location: ?page=maintenance&person=$pf&msg=saved"); exit;
    }
}
if (defined('HANDLE_POST_ONLY')) return;

$db->prepare("UPDATE maintenance SET status = CASE
    WHEN next_due < CURDATE() THEN 'Überfällig'
    WHEN next_due >= CURDATE() AND next_due <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Bald fällig'
    ELSE 'OK' END WHERE next_due IS NOT NULL AND user_id=?")->execute([$uid]);

function get_maintenance(PDO $db, bool $is_all, string $person, int $uid): array {
    if ($is_all) {
        $s = $db->prepare("SELECT * FROM maintenance WHERE user_id=? ORDER BY next_due ASC");
        $s->execute([$uid]);
    } else {
        $s = $db->prepare("SELECT * FROM maintenance WHERE user_id=? AND (person=? OR person='Beide') ORDER BY next_due ASC");
        $s->execute([$uid, $person]);
    }
    return $s->fetchAll();
}

$items = get_maintenance($db, $is_all, $person, $uid);

function he_m(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

$intervalle = ['einmalig','monatlich','quartalsweise','halbjährlich','jährlich'];
?>

<div class="finance-topbar">
    <div class="tab-bar"></div>
    <div class="person-switcher">
        <?php foreach ($person_options as $p): ?>
        <a href="?page=maintenance&person=<?= $p ?>" class="person-btn <?= $person===$p?'active':'' ?>"><?= $p ?></a>
        <?php endforeach; ?>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">Gespeichert.</div><?php endif; ?>

<div class="card mt-4" id="card-maintenance">
    <div class="card-head">
        <div class="card-head-left">
            <h2 class="card-title">🔧 Wartungen<?= !$is_all?' – '.$person:'' ?></h2>
            <?php $ue=[]; foreach($items as $i) if($i['status']==='Überfällig') $ue[]=$i; ?>
            <?php if (count($ue)): ?><span class="badge badge-danger"><?= count($ue) ?> überfällig</span><?php endif; ?>
        </div>
        <div class="bulk-bar">
            <button type="button" class="btn btn-ghost btn-sm" id="btn-edit-m">✏ Bearbeiten</button>
            <button type="submit" form="frm-m-bulk" class="btn btn-primary btn-sm btn-hidden" id="btn-save-m">✓ Speichern</button>
        </div>
    </div>
    <form id="frm-m-bulk" method="POST" action="?page=maintenance">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="bulk_save">
        <input type="hidden" name="person_filter" value="<?= he_m($person) ?>">
        <?php foreach ($items as $i): ?>
        <input type="hidden" name="ids[]" value="<?= $i['id'] ?>">
        <?php endforeach; ?>
    </form>
    <div class="table-wrap"><table class="data-table">
        <thead><tr>
            <th>Objekt</th><th>Aufgabe</th><th>Intervall</th><th>Zuständig</th>
            <th>Zuletzt</th><th>Nächste</th><th>Status</th><th>Notiz</th><th></th>
        </tr></thead>
        <tbody>
        <?php foreach ($items as $i): $iid = $i['id'];
            $cls_map = ['Überfällig' => 'badge-danger', 'Bald fällig' => 'badge-warning'];
            $cls = $cls_map[$i['status']] ?? 'badge-ok';
        ?>
        <tr>
            <td><span class="ft-bulk"><?= he_m($i['object_name']) ?></span><input class="inline-input fi-bulk" form="frm-m-bulk" name="rows[<?= $iid ?>][object_name]" value="<?= he_m($i['object_name']) ?>" required></td>
            <td><span class="ft-bulk"><?= he_m($i['task']) ?></span><input class="inline-input fi-bulk" form="frm-m-bulk" name="rows[<?= $iid ?>][task]" value="<?= he_m($i['task']) ?>" required></td>
            <td>
                <span class="ft-bulk"><?= he_m($i['interval_type']??'–') ?></span>
                <select class="inline-input fi-bulk" form="frm-m-bulk" name="rows[<?= $iid ?>][interval_type]">
                    <?php foreach ($intervalle as $iv): ?><option value="<?= $iv ?>"<?= $iv===$i['interval_type']?' selected':'' ?>><?= $iv ?></option><?php endforeach; ?>
                </select>
            </td>
            <td>
                <span class="ft-bulk"><?= he_m($i['responsible']??$i['person']??'–') ?></span>
                <select class="inline-input fi-bulk" form="frm-m-bulk" name="rows[<?= $iid ?>][responsible]">
                    <?php foreach ($person_options as $p): ?><option value="<?= $p ?>"<?= $p===($i['responsible']??$i['person']??'')?' selected':'' ?>><?= $p ?></option><?php endforeach; ?>
                </select>
            </td>
            <td><span class="ft-bulk"><?= $i['last_done'] ? date('d.m.Y', strtotime($i['last_done'])) : '–' ?></span><input class="inline-input fi-bulk" type="date" form="frm-m-bulk" name="rows[<?= $iid ?>][last_done]" value="<?= he_m($i['last_done']??'') ?>"></td>
            <td><span class="ft-bulk"><?= $i['next_due'] ? date('d.m.Y', strtotime($i['next_due'])) : '–' ?></span><input class="inline-input fi-bulk" type="date" form="frm-m-bulk" name="rows[<?= $iid ?>][next_due]" value="<?= he_m($i['next_due']??'') ?>"></td>
            <td><span class="badge <?= $cls ?>"><?= he_m($i['status']) ?></span></td>
            <td><span class="ft-bulk"><?= he_m($i['notes']??'–') ?></span><input class="inline-input fi-bulk" form="frm-m-bulk" name="rows[<?= $iid ?>][notes]" value="<?= he_m($i['notes']??'') ?>"></td>
            <td class="col-actions">
                <form method="POST" action="?page=maintenance" class="form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="done">
                    <input type="hidden" name="id" value="<?= $iid ?>">
                    <input type="hidden" name="interval_type" value="<?= he_m($i['interval_type']??'') ?>">
                    <input type="hidden" name="person_filter" value="<?= he_m($person) ?>">
                    <button type="submit" class="btn btn-ok btn-xs">✓</button>
                </form>
                <form id="frm-m-del-<?= $iid ?>" method="POST" action="?page=maintenance" hidden>
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="delete">
                    <input type="hidden" name="id" value="<?= $iid ?>">
                    <input type="hidden" name="person_filter" value="<?= he_m($person) ?>">
                </form>
                <button type="submit" form="frm-m-del-<?= $iid ?>" class="btn btn-danger btn-xs fi-bulk btn-delete-confirm">✕</button>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?><tr><td colspan="9" class="empty-state">Keine Wartungen.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div>

<!-- Neue Wartung Card -->
<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Neue Wartung</h2></div>
    <form id="frm-m-new" method="POST" action="?page=maintenance">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="save">
        <input type="hidden" name="edit_id" value="0">
        <input type="hidden" name="person_filter" value="<?= he_m($person) ?>">
    </form>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Objekt</th><th>Aufgabe</th><th>Intervall</th><th>Zuständig</th><th>Zuletzt</th><th>Nächste</th><th>Notiz</th><th></th></tr></thead>
        <tbody><tr>
            <td><input class="inline-input new-input" form="frm-m-new" name="object_name" placeholder="Objekt" required></td>
            <td><input class="inline-input new-input" form="frm-m-new" name="task" placeholder="Aufgabe" required></td>
            <td><select class="inline-input new-input" form="frm-m-new" name="interval_type"><?php foreach ($intervalle as $iv): ?><option><?= $iv ?></option><?php endforeach; ?></select></td>
            <td><select class="inline-input new-input" form="frm-m-new" name="responsible"><?php foreach ($person_options as $p): ?><option value="<?= $p ?>"<?= $p===$def_person?' selected':'' ?>><?= $p ?></option><?php endforeach; ?></select></td>
            <td><input class="inline-input new-input" type="date" form="frm-m-new" name="last_done"></td>
            <td><input class="inline-input new-input" type="date" form="frm-m-new" name="next_due"></td>
            <td><input class="inline-input new-input" form="frm-m-new" name="notes" placeholder="Notiz"></td>
            <td class="col-actions"><button type="button" class="btn btn-primary btn-xs" id="btn-new-m">+ Hinzufügen</button></td>
        </tr></tbody>
    </table></div>
</div>