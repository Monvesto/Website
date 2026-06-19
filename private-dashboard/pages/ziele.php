<?php
$db     = get_db();
$person = $_GET['person'] ?? 'Marcel';
if (!in_array($person, ['Marcel','Kim','Beide'], true)) $person = 'Marcel';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act    = $_POST['act'] ?? '';
    $person = $_POST['person_filter'] ?? $person;

    if ($act === 'save') {
        $fields = ['ziel','kategorie','startwert','zielwert','aktueller_wert','zieltermin','kommentar','person'];
        $id = (int)($_POST['edit_id'] ?? 0);
        $vals = [];
        foreach (['ziel','kategorie','startwert','zielwert','aktueller_wert','zieltermin','kommentar'] as $f) {
            $v = trim($_POST[$f] ?? '');
            if (in_array($f, ['startwert','zielwert','aktueller_wert'])) $v = str_replace(',','.',$v);
            if ($f === 'zieltermin' && $v === '') $v = null;
            $vals[] = $v;
        }
        $vals[] = $_POST['person_ziel'] ?? 'Beide';
        if ($id > 0) {
            $db->prepare("UPDATE ziele SET ziel=?,kategorie=?,startwert=?,zielwert=?,aktueller_wert=?,zieltermin=?,kommentar=?,person=? WHERE id=?")->execute([...$vals, $id]);
        } else {
            $db->prepare("INSERT INTO ziele (ziel,kategorie,startwert,zielwert,aktueller_wert,zieltermin,kommentar,person) VALUES (?,?,?,?,?,?,?,?)")->execute($vals);
        }
        header("Location: ?page=ziele&person=$person&msg=saved"); exit;
    }

    if ($act === 'delete') {
        $db->prepare("DELETE FROM ziele WHERE id=?")->execute([(int)$_POST['id']]);
        header("Location: ?page=ziele&person=$person&msg=saved"); exit;
    }

    if ($act === 'update_wert') {
        $db->prepare("UPDATE ziele SET aktueller_wert=? WHERE id=?")->execute([
            str_replace(',','.',$_POST['aktueller_wert'] ?? '0'),
            (int)$_POST['id']
        ]);
        header("Location: ?page=ziele&person=$person&msg=saved"); exit;
    }
}
if (defined('HANDLE_POST_ONLY')) return;

$def_person = ($person === 'Beide') ? 'Marcel' : $person;

if ($person === 'Beide') {
    $ziele = $db->query("SELECT * FROM ziele ORDER BY position, zieltermin")->fetchAll();
} else {
    $s = $db->prepare("SELECT * FROM ziele WHERE (person=? OR person='Beide') ORDER BY position, zieltermin");
    $s->execute([$person]); $ziele = $s->fetchAll();
}

function progress_cls(float $p): string {
    if ($p >= 0.75) return 'progress-green';
    if ($p >= 0.4)  return 'progress-amber';
    return 'progress-red';
}
function he_z(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function sel_p_z(string $cur): string {
    $out = '';
    foreach (['Marcel','Kim','Beide'] as $p)
        $out .= '<option value="'.he_z($p).'"'.($p===$cur?' selected':'').'>'.he_z($p).'</option>';
    return $out;
}
$kategorien = ['Finanzen','Gesundheit','Beruf','Persönlich','Immobilien','Sonstiges'];
?>

<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">Gespeichert.</div><?php endif; ?>

<div class="finance-topbar">
    <div></div>
    <div class="person-switcher">
        <?php foreach (['Marcel','Kim','Beide'] as $p): ?>
        <a href="?page=ziele&person=<?= $p ?>" class="person-btn <?= $person===$p?'active':'' ?>"><?= $p ?></a>
        <?php endforeach; ?>
    </div>
</div>

<div class="goals-list card mt-4">
<?php foreach ($ziele as $z):
    $range = abs((float)$z['zielwert'] - (float)$z['startwert']);
    $curr  = abs((float)$z['aktueller_wert'] - (float)$z['startwert']);
    $prog  = $range > 0 ? min(1, $curr / $range) : 0;
    $pcls  = progress_cls($prog);
    $pct   = number_format($prog * 100, 1, ',', '.') . '%';
    $pct_r = number_format($prog * 100, 1, ',', '.');
?>
<div class="goal-row">
    <div class="goal-header">
        <div>
            <div class="goal-name"><?= he_z($z['ziel']) ?></div>
            <div>
                <?php if ($z['kategorie']): ?><span class="badge badge-neutral"><?= he_z($z['kategorie']) ?></span><?php endif; ?>
                <?php if ($person === 'Beide'): ?><span class="badge badge-neutral"><?= he_z($z['person'] ?? 'Beide') ?></span><?php endif; ?>
            </div>
        </div>
        <div class="goal-meta">
            <span class="goal-pct <?= $pcls ?>"><?= $pct ?></span>
            <?php if ($z['zieltermin']): ?>
            <span class="goal-date text-muted"><?= date('d.m.Y', strtotime($z['zieltermin'])) ?></span>
            <?php endif; ?>
            <form method="POST" action="?page=ziele" class="form-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="act" value="delete">
                <input type="hidden" name="id" value="<?= $z['id'] ?>">
                <input type="hidden" name="person_filter" value="<?= he_z($person) ?>">
                <button type="submit" class="btn btn-danger btn-xs btn-delete-confirm">✕</button>
            </form>
        </div>
    </div>
    <div class="goal-bar-track">
        <div class="goal-bar-fill <?= $pcls ?>" data-width="<?= $pct_r ?>"></div>
    </div>
    <div class="goal-edit-row">
        <span class="goal-values">
            <span class="fw-700"><?= number_format((float)$z['aktueller_wert'],0,',','.') ?></span>
            <span class="text-muted">von <?= number_format((float)$z['zielwert'],0,',','.') ?></span>
        </span>
        <form method="POST" action="?page=ziele" class="goal-update-form">
            <?= csrf_field() ?>
            <input type="hidden" name="act" value="update_wert">
            <input type="hidden" name="id" value="<?= $z['id'] ?>">
            <input type="hidden" name="person_filter" value="<?= he_z($person) ?>">
            <input type="text" name="aktueller_wert" value="<?= he_z(number_format((float)$z['aktueller_wert'],0,',','.')) ?>" class="inline-input goal-input">
            <button type="submit" class="btn btn-primary btn-xs">Aktualisieren</button>
        </form>
    </div>
    <?php if ($z['kommentar']): ?><div class="goal-kommentar text-muted"><?= he_z($z['kommentar']) ?></div><?php endif; ?>
</div>
<?php endforeach; ?>
<?php if (empty($ziele)): ?><p class="empty-state">Noch keine Ziele angelegt.</p><?php endif; ?>
</div>

<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Neues Ziel</h2></div>
    <form method="POST" action="?page=ziele">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="save">
        <input type="hidden" name="edit_id" value="0">
        <input type="hidden" name="person_filter" value="<?= he_z($person) ?>">
        <div class="form-grid">
            <div class="form-group fg-wide"><label>Ziel</label><input type="text" name="ziel" required></div>
            <div class="form-group"><label>Kategorie</label>
                <select name="kategorie"><option value="">– wählen –</option>
                <?php foreach ($kategorien as $k): ?><option><?= $k ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Person</label>
                <select name="person_ziel"><?= sel_p_z($def_person) ?></select>
            </div>
            <div class="form-group"><label>Startwert</label><input type="text" name="startwert" placeholder="0"></div>
            <div class="form-group"><label>Zielwert</label><input type="text" name="zielwert" placeholder="0"></div>
            <div class="form-group"><label>Aktueller Wert</label><input type="text" name="aktueller_wert" placeholder="0"></div>
            <div class="form-group"><label>Zieltermin</label><input type="date" name="zieltermin"></div>
            <div class="form-group fg-wide"><label>Kommentar</label><textarea name="kommentar" rows="2"></textarea></div>
        </div>
        <div class="form-actions form-actions--pad">
            <button type="submit" class="btn btn-primary">Ziel anlegen</button>
        </div>
    </form>
</div>