<?php
// ════════════════════════════════════════════════
// ziele.php – Ziele als Tabelle mit Bulk-Edit + Person-Filter
// Neues Ziel als separate Card mit Tabellen-Header
// ════════════════════════════════════════════════
$db     = get_db();
$person = $_GET['person'] ?? 'Marcel';
if (!in_array($person, ['Marcel','Kim','Beide'], true)) $person = 'Marcel';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';
    $pf  = $_POST['person_filter'] ?? $person;

    if ($act === 'save') {
        $ziel = trim($_POST['ziel'] ?? '');
        $kat  = trim($_POST['kategorie'] ?? '');
        $sw   = parse_betrag($_POST['startwert'] ?? '0');
        $zw   = parse_betrag($_POST['zielwert'] ?? '0');
        $aw   = parse_betrag($_POST['aktueller_wert'] ?? '0');
        $zt   = ($_POST['zieltermin'] ?? '') ?: null;
        $kom  = trim($_POST['kommentar'] ?? '');
        $per  = $_POST['person_ziel'] ?? 'Beide';
        $id   = (int)($_POST['edit_id'] ?? 0);
        if ($id > 0) {
            $db->prepare("UPDATE ziele SET ziel=?,kategorie=?,startwert=?,zielwert=?,aktueller_wert=?,zieltermin=?,kommentar=?,person=? WHERE id=?")->execute([$ziel,$kat,$sw,$zw,$aw,$zt,$kom,$per,$id]);
        } else {
            $db->prepare("INSERT INTO ziele (ziel,kategorie,startwert,zielwert,aktueller_wert,zieltermin,kommentar,person) VALUES (?,?,?,?,?,?,?,?)")->execute([$ziel,$kat,$sw,$zw,$aw,$zt,$kom,$per]);
        }
        header("Location: ?page=ziele&person=$pf&msg=saved"); exit;
    }

    if ($act === 'bulk_save') {
        foreach ($_POST['ids'] ?? [] as $id) {
            $id   = (int)$id;
            $row  = $_POST['rows'][$id] ?? [];
            $ziel = trim($row['ziel'] ?? '');
            if ($ziel === '') continue;
            $kat  = trim($row['kategorie'] ?? '');
            $sw   = parse_betrag($row['startwert'] ?? '0');
            $zw   = parse_betrag($row['zielwert'] ?? '0');
            $aw   = parse_betrag($row['aktueller_wert'] ?? '0');
            $zt   = ($row['zieltermin'] ?? '') ?: null;
            $kom  = trim($row['kommentar'] ?? '');
            $per  = $row['person'] ?? 'Beide';
            $db->prepare("UPDATE ziele SET ziel=?,kategorie=?,startwert=?,zielwert=?,aktueller_wert=?,zieltermin=?,kommentar=?,person=? WHERE id=?")->execute([$ziel,$kat,$sw,$zw,$aw,$zt,$kom,$per,$id]);
        }
        header("Location: ?page=ziele&person=$pf&msg=saved"); exit;
    }

    if ($act === 'delete') {
        $db->prepare("DELETE FROM ziele WHERE id=?")->execute([(int)$_POST['id']]);
        header("Location: ?page=ziele&person=$pf&msg=saved"); exit;
    }

    if ($act === 'update_wert') {
        $db->prepare("UPDATE ziele SET aktueller_wert=? WHERE id=?")->execute([
            parse_betrag($_POST['aktueller_wert'] ?? '0'),
            (int)$_POST['id']
        ]);
        header("Location: ?page=ziele&person=$pf&msg=saved"); exit;
    }
}
if (defined('HANDLE_POST_ONLY')) return;

$def_person = ($person === 'Beide') ? 'Marcel' : $person;
$kategorien = ['Finanzen','Gesundheit','Beruf','Persönlich','Immobilien','Sonstiges'];
$personen   = ['Marcel','Kim','Beide'];

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
function sel_z(array $opts, string $cur): string {
    $out = '';
    foreach ($opts as $o) $out .= '<option value="'.he_z($o).'"'.($o===$cur?' selected':'').'>'.he_z($o).'</option>';
    return $out;
}
function kat_sel_z(array $opts, string $cur): string {
    $out = '<option value="">– wählen –</option>';
    foreach ($opts as $o) $out .= '<option value="'.he_z($o).'"'.($o===$cur?' selected':'').'>'.he_z($o).'</option>';
    return $out;
}
?>

<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">Gespeichert.</div><?php endif; ?>

<div class="finance-topbar">
    <div class="tab-bar"></div>
    <div class="person-switcher">
        <?php foreach (['Marcel','Kim','Beide'] as $p): ?>
        <a href="?page=ziele&person=<?= $p ?>" class="person-btn <?= $person===$p?'active':'' ?>"><?= $p ?></a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Bulk-Form außerhalb Tabelle -->
<form id="frm-zl-bulk" method="POST" action="?page=ziele">
    <?= csrf_field() ?>
    <input type="hidden" name="act" value="bulk_save">
    <input type="hidden" name="person_filter" value="<?= he_z($person) ?>">
    <?php foreach ($ziele as $z): ?>
    <input type="hidden" name="ids[]" value="<?= $z['id'] ?>">
    <?php endforeach; ?>
</form>

<!-- Ziele Tabelle -->
<div class="card mt-4" id="card-ziele">
    <div class="card-head">
        <div class="card-head-left">
            <h2 class="card-title">🎯 Ziele<?= $person!=='Beide'?' – '.$person:'' ?></h2>
            <span class="badge badge-neutral"><?= count($ziele) ?> Ziele</span>
        </div>
        <div class="bulk-bar">
            <button type="button" class="btn btn-ghost btn-sm" id="btn-edit-zl">✏ Bearbeiten</button>
            <button type="submit" form="frm-zl-bulk" class="btn btn-primary btn-sm btn-hidden" id="btn-save-zl">✓ Speichern</button>
        </div>
    </div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr>
            <th>Ziel</th>
            <th>Kategorie</th>
            <?php if($person==='Beide'): ?><th>Person</th><?php endif; ?>
            <th>Fortschritt</th>
            <th class="col-right">Aktuell</th>
            <th class="col-right">Zielwert</th>
            <th>Termin</th>
            <th></th>
        </tr></thead>
        <tbody>
        <?php foreach ($ziele as $z):
            $zid   = $z['id'];
            $range = abs((float)$z['zielwert'] - (float)$z['startwert']);
            $curr  = abs((float)$z['aktueller_wert'] - (float)$z['startwert']);
            $prog  = $range > 0 ? min(1, $curr / $range) : 0;
            $pcls  = progress_cls($prog);
            $pct_r = number_format($prog * 100, 1, '.', '');
        ?>
        <tr>
            <td>
                <span class="ft-bulk"><?= he_z($z['ziel']) ?></span>
                <input class="inline-input fi-bulk" form="frm-zl-bulk" name="rows[<?= $zid ?>][ziel]" value="<?= he_z($z['ziel']) ?>" required>
                <?php if ($z['kommentar']): ?>
                <div class="ft-bulk" style="font-size:11px;color:var(--text-muted);margin-top:2px"><?= he_z($z['kommentar']) ?></div>
                <?php endif; ?>
                <input class="inline-input fi-bulk" form="frm-zl-bulk" name="rows[<?= $zid ?>][kommentar]" value="<?= he_z($z['kommentar']??'') ?>" placeholder="Kommentar" style="margin-top:3px">
            </td>
            <td>
                <span class="ft-bulk"><span class="badge badge-neutral"><?= he_z($z['kategorie']??'–') ?></span></span>
                <select class="inline-input fi-bulk" form="frm-zl-bulk" name="rows[<?= $zid ?>][kategorie]"><?= kat_sel_z($kategorien,$z['kategorie']??'') ?></select>
            </td>
            <?php if($person==='Beide'): ?>
            <td>
                <span class="ft-bulk"><?= he_z($z['person']??'–') ?></span>
                <select class="inline-input fi-bulk" form="frm-zl-bulk" name="rows[<?= $zid ?>][person]"><?= sel_z($personen,$z['person']??'Beide') ?></select>
            </td>
            <?php else: ?>
            <input type="hidden" form="frm-zl-bulk" name="rows[<?= $zid ?>][person]" value="<?= he_z($z['person']??$def_person) ?>">
            <?php endif; ?>
            <td>
                <div class="progress-wrap">
                    <div class="progress-track"><div class="progress-fill <?= $pcls ?>" data-width="<?= $pct_r ?>"></div></div>
                    <span class="progress-label <?= $pcls ?>"><?= $pct_r ?>%</span>
                </div>
            </td>
            <td class="col-right">
                <!-- Schnell-Update im Anzeigemodus -->
                <form method="POST" action="?page=ziele" class="ft-bulk" style="display:flex;align-items:center;gap:4px;justify-content:flex-end">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="update_wert">
                    <input type="hidden" name="id" value="<?= $zid ?>">
                    <input type="hidden" name="person_filter" value="<?= he_z($person) ?>">
                    <input type="text" name="aktueller_wert" value="<?= he_z(number_format((float)$z['aktueller_wert'],0,',','.')) ?>" class="inline-input goal-input" style="max-width:70px;text-align:right">
                    <button type="submit" class="btn btn-primary btn-xs">Aktualisieren</button>
                </form>
                <input class="inline-input fi-bulk input-right input-narrow" form="frm-zl-bulk" name="rows[<?= $zid ?>][aktueller_wert]" value="<?= he_z(number_format((float)$z['aktueller_wert'],0,',','.')) ?>">
            </td>
            <td class="col-right">
                <span class="ft-bulk fw-700"><?= number_format((float)$z['zielwert'],0,',','.') ?></span>
                <div class="fi-bulk" style="display:flex;flex-direction:column;gap:3px">
                    <input class="inline-input input-right input-narrow" form="frm-zl-bulk" name="rows[<?= $zid ?>][zielwert]" value="<?= he_z(number_format((float)$z['zielwert'],0,',','.')) ?>" placeholder="Ziel">
                    <input class="inline-input input-right input-narrow" form="frm-zl-bulk" name="rows[<?= $zid ?>][startwert]" value="<?= he_z(number_format((float)$z['startwert'],0,',','.')) ?>" placeholder="Start">
                </div>
            </td>
            <td>
                <span class="ft-bulk"><?= $z['zieltermin'] ? date('d.m.Y', strtotime($z['zieltermin'])) : '–' ?></span>
                <input class="inline-input fi-bulk" type="date" form="frm-zl-bulk" name="rows[<?= $zid ?>][zieltermin]" value="<?= he_z($z['zieltermin']??'') ?>">
            </td>
            <td class="col-actions">
                <form id="frm-zl-del-<?= $zid ?>" method="POST" action="?page=ziele" hidden>
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="delete">
                    <input type="hidden" name="id" value="<?= $zid ?>">
                    <input type="hidden" name="person_filter" value="<?= he_z($person) ?>">
                </form>
                <button type="submit" form="frm-zl-del-<?= $zid ?>" class="btn btn-danger btn-xs fi-bulk btn-delete-confirm">✕</button>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($ziele)): ?>
        <tr><td colspan="8" class="empty-state">Noch keine Ziele angelegt.</td></tr>
        <?php endif; ?>
        </tbody>
    </table></div>
</div>

<!-- Neues Ziel Card -->
<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Neues Ziel</h2></div>
    <form id="frm-zl-new" method="POST" action="?page=ziele">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="save">
        <input type="hidden" name="edit_id" value="0">
        <input type="hidden" name="person_filter" value="<?= he_z($person) ?>">
        <?php if($person!=='Beide'): ?>
        <input type="hidden" name="person_ziel" value="<?= he_z($def_person) ?>">
        <?php endif; ?>
    </form>
    <div class="table-wrap"><table class="data-table">
        <thead><tr>
            <th>Ziel</th>
            <th>Kategorie</th>
            <?php if($person==='Beide'): ?><th>Person</th><?php endif; ?>
            <th>Aktuell</th>
            <th>Zielwert</th>
            <th>Startwert</th>
            <th>Termin</th>
            <th></th>
        </tr></thead>
        <tbody><tr>
            <td>
                <input class="inline-input new-input" form="frm-zl-new" name="ziel" placeholder="Zielname" required>
                <input class="inline-input new-input" form="frm-zl-new" name="kommentar" placeholder="Kommentar" style="margin-top:3px">
            </td>
            <td><select class="inline-input new-input" form="frm-zl-new" name="kategorie"><?= kat_sel_z($kategorien,'') ?></select></td>
            <?php if($person==='Beide'): ?>
            <td><select class="inline-input new-input" form="frm-zl-new" name="person_ziel"><?= sel_z($personen,$def_person) ?></select></td>
            <?php endif; ?>
            <td><input class="inline-input new-input input-right input-narrow" form="frm-zl-new" name="aktueller_wert" placeholder="0"></td>
            <td><input class="inline-input new-input input-right input-narrow" form="frm-zl-new" name="zielwert" placeholder="0"></td>
            <td><input class="inline-input new-input input-right input-narrow" form="frm-zl-new" name="startwert" placeholder="0"></td>
            <td><input class="inline-input new-input" type="date" form="frm-zl-new" name="zieltermin"></td>
            <td class="col-actions">
                <button type="button" class="btn btn-primary btn-xs" id="btn-new-zl">+ Hinzufügen</button>
            </td>
        </tr></tbody>
    </table></div>
</div>