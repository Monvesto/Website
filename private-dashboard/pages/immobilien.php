<?php
// ════════════════════════════════════════════════
// immobilien.php – Immobilien mit Bulk-Edit + Person-Filter
// Neues Objekt als tfoot in der Tabelle
// ════════════════════════════════════════════════
$db     = get_db();
$person = $_GET['person'] ?? 'Marcel';
if (!in_array($person, ['Marcel','Kim','Beide'], true)) $person = 'Marcel';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';
    $pf  = $_POST['person_filter'] ?? $person;

    if ($act === 'save') {
        $id  = (int)($_POST['edit_id'] ?? 0);
        $on  = trim($_POST['objekt_name'] ?? '');
        $ed  = $_POST['einzugsdatum'] ?: null;
        $km  = parse_betrag($_POST['kaltmiete'] ?? '0');
        $nk  = parse_betrag($_POST['nebenkosten'] ?? '0');
        $fk  = parse_betrag($_POST['fixkosten'] ?? '0');
        $ck  = parse_betrag($_POST['kreditkosten'] ?? '0');
        $ka  = parse_betrag($_POST['kaution'] ?? '0');
        $mi  = trim($_POST['mieter'] ?? '');
        $bem = trim($_POST['bemerkung'] ?? '');
        $per = $_POST['person'] ?? 'Marcel';
        if ($id > 0) {
            $db->prepare("UPDATE immobilien SET objekt_name=?,einzugsdatum=?,kaltmiete=?,nebenkosten=?,fixkosten=?,kreditkosten=?,kaution=?,mieter=?,bemerkung=?,person=? WHERE id=?")
               ->execute([$on,$ed,$km,$nk,$fk,$ck,$ka,$mi,$bem,$per,$id]);
        } else {
            $db->prepare("INSERT INTO immobilien (objekt_name,einzugsdatum,kaltmiete,nebenkosten,fixkosten,kreditkosten,kaution,mieter,bemerkung,person) VALUES (?,?,?,?,?,?,?,?,?,?)")
               ->execute([$on,$ed,$km,$nk,$fk,$ck,$ka,$mi,$bem,$per]);
        }
        header("Location: ?page=immobilien&person=$pf&msg=saved"); exit;
    }

    if ($act === 'bulk_save') {
        foreach ($_POST['ids'] ?? [] as $id) {
            $id  = (int)$id;
            $row = $_POST['rows'][$id] ?? [];
            $on  = trim($row['objekt_name'] ?? '');
            if ($on === '') continue;
            $ed  = $row['einzugsdatum'] ?: null;
            $km  = parse_betrag($row['kaltmiete'] ?? '0');
            $nk  = parse_betrag($row['nebenkosten'] ?? '0');
            $fk  = parse_betrag($row['fixkosten'] ?? '0');
            $ck  = parse_betrag($row['kreditkosten'] ?? '0');
            $ka  = parse_betrag($row['kaution'] ?? '0');
            $mi  = trim($row['mieter'] ?? '');
            $bem = trim($row['bemerkung'] ?? '');
            $per = $row['person'] ?? 'Marcel';
            $db->prepare("UPDATE immobilien SET objekt_name=?,einzugsdatum=?,kaltmiete=?,nebenkosten=?,fixkosten=?,kreditkosten=?,kaution=?,mieter=?,bemerkung=?,person=? WHERE id=?")
               ->execute([$on,$ed,$km,$nk,$fk,$ck,$ka,$mi,$bem,$per,$id]);
        }
        header("Location: ?page=immobilien&person=$pf&msg=saved"); exit;
    }

    if ($act === 'delete') {
        $db->prepare("DELETE FROM immobilien WHERE id=?")->execute([(int)$_POST['id']]);
        header("Location: ?page=immobilien&person=$pf&msg=saved"); exit;
    }
}
if (defined('HANDLE_POST_ONLY')) return;

function get_immobilien(PDO $db, string $person): array {
    if ($person === 'Beide') {
        return $db->query("SELECT * FROM immobilien ORDER BY aktiv DESC, objekt_name")->fetchAll();
    }
    $s = $db->prepare("SELECT * FROM immobilien WHERE (person=? OR person='Beide') ORDER BY aktiv DESC, objekt_name");
    $s->execute([$person]);
    return $s->fetchAll();
}

$objekte    = get_immobilien($db, $person);
$personen   = ['Marcel','Kim','Beide'];
$def_person = $person === 'Beide' ? 'Marcel' : $person;

function fmt_i(float $v): string { return number_format($v, 2, ',', '.') . ' €'; }
function he_i(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>

<div class="finance-topbar">
    <div class="tab-bar"></div>
    <div class="person-switcher">
        <?php foreach (['Marcel','Kim','Beide'] as $p): ?>
        <a href="?page=immobilien&person=<?= $p ?>" class="person-btn <?= $person===$p?'active':'' ?>"><?= $p ?></a>
        <?php endforeach; ?>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">Gespeichert.</div><?php endif; ?>

<div class="card mt-4" id="card-immobilien">
    <div class="card-head">
        <div class="card-head-left">
            <h2 class="card-title">🏠 Immobilien<?= $person!=='Beide'?' – '.$person:'' ?></h2>
            <span class="badge badge-neutral"><?= count($objekte) ?> Objekte</span>
        </div>
        <div class="bulk-bar">
            <button type="button" class="btn btn-ghost btn-sm" id="btn-edit-i">✏ Bearbeiten</button>
            <button type="submit" form="frm-i-bulk" class="btn btn-primary btn-sm btn-hidden" id="btn-save-i">✓ Speichern</button>
        </div>
    </div>
    <form id="frm-i-bulk" method="POST" action="?page=immobilien">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="bulk_save">
        <input type="hidden" name="person_filter" value="<?= he_i($person) ?>">
        <?php foreach ($objekte as $o): ?>
        <input type="hidden" name="ids[]" value="<?= $o['id'] ?>">
        <?php endforeach; ?>
    </form>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr>
                <th>Objekt</th><th>Person</th><th>Mieter</th><th>Einzug</th>
                <th class="col-right">Kaution</th><th class="col-right">Kaltmiete</th>
                <th class="col-right">NK</th><th class="col-right">Fixkosten</th>
                <th class="col-right">Kreditkosten</th><th class="col-right">Cashflow</th>
                <th>Status</th><th></th>
            </tr></thead>
            <tbody>
            <?php foreach ($objekte as $o): $oid = $o['id'];
                $cashflow = (float)$o['kaltmiete'] + (float)$o['nebenkosten'] - (float)$o['fixkosten'] - (float)$o['kreditkosten'];
            ?>
            <tr>
                <td>
                    <span class="ft-bulk"><?= he_i($o['objekt_name']) ?></span>
                    <input class="inline-input fi-bulk" form="frm-i-bulk" name="rows[<?= $oid ?>][objekt_name]" value="<?= he_i($o['objekt_name']) ?>" required>
                </td>
                <td>
                    <span class="ft-bulk"><?= he_i($o['person']??'–') ?></span>
                    <select class="inline-input fi-bulk" form="frm-i-bulk" name="rows[<?= $oid ?>][person]">
                        <?php foreach ($personen as $p): ?>
                        <option value="<?= $p ?>"<?= $p===$o['person']?' selected':'' ?>><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <span class="ft-bulk"><?= he_i($o['mieter']??'–') ?></span>
                    <input class="inline-input fi-bulk" form="frm-i-bulk" name="rows[<?= $oid ?>][mieter]" value="<?= he_i($o['mieter']??'') ?>">
                </td>
                <td>
                    <span class="ft-bulk"><?= $o['einzugsdatum'] ? date('d.m.Y', strtotime($o['einzugsdatum'])) : '–' ?></span>
                    <input class="inline-input fi-bulk" type="date" form="frm-i-bulk" name="rows[<?= $oid ?>][einzugsdatum]" value="<?= he_i($o['einzugsdatum']??'') ?>">
                </td>
                <td class="col-right">
                    <span class="ft-bulk"><?= fmt_i((float)$o['kaution']) ?></span>
                    <input class="inline-input fi-bulk input-right input-narrow" form="frm-i-bulk" name="rows[<?= $oid ?>][kaution]" value="<?= he_i(number_format((float)$o['kaution'],2,',','.')) ?>">
                </td>
                <td class="col-right">
                    <span class="ft-bulk"><?= fmt_i((float)$o['kaltmiete']) ?></span>
                    <input class="inline-input fi-bulk input-right input-narrow" form="frm-i-bulk" name="rows[<?= $oid ?>][kaltmiete]" value="<?= he_i(number_format((float)$o['kaltmiete'],2,',','.')) ?>">
                </td>
                <td class="col-right">
                    <span class="ft-bulk"><?= fmt_i((float)$o['nebenkosten']) ?></span>
                    <input class="inline-input fi-bulk input-right input-narrow" form="frm-i-bulk" name="rows[<?= $oid ?>][nebenkosten]" value="<?= he_i(number_format((float)$o['nebenkosten'],2,',','.')) ?>">
                </td>
                <td class="col-right">
                    <span class="ft-bulk text-red"><?= fmt_i((float)$o['fixkosten']) ?></span>
                    <input class="inline-input fi-bulk input-right input-narrow" form="frm-i-bulk" name="rows[<?= $oid ?>][fixkosten]" value="<?= he_i(number_format((float)$o['fixkosten'],2,',','.')) ?>">
                </td>
                <td class="col-right">
                    <span class="ft-bulk text-red"><?= fmt_i((float)$o['kreditkosten']) ?></span>
                    <input class="inline-input fi-bulk input-right input-narrow" form="frm-i-bulk" name="rows[<?= $oid ?>][kreditkosten]" value="<?= he_i(number_format((float)$o['kreditkosten'],2,',','.')) ?>">
                </td>
                <td class="col-right fw-700 <?= $cashflow >= 0 ? 'text-green' : 'text-red' ?>">
                    <?= fmt_i($cashflow) ?>
                </td>
                <td><span class="badge <?= $o['aktiv'] ? 'badge-ok' : 'badge-neutral' ?>"><?= $o['aktiv'] ? 'Aktiv' : 'Inaktiv' ?></span></td>
                <td class="col-actions">
                    <form id="frm-i-del-<?= $oid ?>" method="POST" action="?page=immobilien" hidden>
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="delete">
                        <input type="hidden" name="id" value="<?= $oid ?>">
                        <input type="hidden" name="person_filter" value="<?= he_i($person) ?>">
                    </form>
                    <button type="submit" form="frm-i-del-<?= $oid ?>" class="btn btn-danger btn-xs fi-bulk btn-delete-confirm">✕</button>
                </td>
            </tr>
            <?php if (!empty($o['bemerkung'])): ?>
            <tr class="row-notiz">
                <td colspan="12" class="notiz-cell">
                    <span class="ft-bulk">💬 <?= he_i($o['bemerkung']) ?></span>
                    <input class="inline-input fi-bulk" form="frm-i-bulk" name="rows[<?= $oid ?>][bemerkung]" value="<?= he_i($o['bemerkung']??'') ?>" placeholder="Bemerkung">
                </td>
            </tr>
            <?php else: ?>
            <tr class="row-notiz">
                <td colspan="12" class="notiz-cell">
                    <span class="ft-bulk"></span>
                    <input class="inline-input fi-bulk" form="frm-i-bulk" name="rows[<?= $oid ?>][bemerkung]" value="" placeholder="Bemerkung">
                </td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
            <tr class="row-total">
                <td colspan="9" class="fw-700">Gesamt Cashflow</td>
                <td class="col-right fw-700">
                    <?php
                    $cf_total = 0;
                    foreach ($objekte as $o) $cf_total += (float)$o['kaltmiete']+(float)$o['nebenkosten']-(float)$o['fixkosten']-(float)$o['kreditkosten'];
                    echo '<span class="'.($cf_total >= 0 ? 'text-green' : 'text-red').'">'.fmt_i($cf_total).'</span>';
                    ?>
                </td>
                <td colspan="2"></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Neues Objekt Card -->
<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Neues Objekt</h2></div>
    <form id="frm-i-new" method="POST" action="?page=immobilien">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="save">
        <input type="hidden" name="edit_id" value="0">
        <input type="hidden" name="person_filter" value="<?= he_i($person) ?>">
        <input type="hidden" name="bemerkung" value="">
    </form>
    <div class="table-wrap"><table class="data-table">
        <thead><tr>
            <th>Objekt</th><th>Person</th><th>Mieter</th><th>Einzug</th>
            <th class="col-right">Kaution</th><th class="col-right">Kaltmiete</th>
            <th class="col-right">NK</th><th class="col-right">Fixkosten</th>
            <th class="col-right">Kreditkosten</th><th></th>
        </tr></thead>
        <tbody><tr>
            <td><input class="inline-input new-input" form="frm-i-new" name="objekt_name" placeholder="Objektname" required></td>
            <td><select class="inline-input new-input" form="frm-i-new" name="person">
                <?php foreach ($personen as $p): ?><option value="<?= $p ?>"<?= $p===$def_person?' selected':'' ?>><?= $p ?></option><?php endforeach; ?>
            </select></td>
            <td><input class="inline-input new-input" form="frm-i-new" name="mieter" placeholder="Mieter"></td>
            <td><input class="inline-input new-input" type="date" form="frm-i-new" name="einzugsdatum"></td>
            <td><input class="inline-input new-input input-right input-narrow" form="frm-i-new" name="kaution" placeholder="0,00"></td>
            <td><input class="inline-input new-input input-right input-narrow" form="frm-i-new" name="kaltmiete" placeholder="0,00"></td>
            <td><input class="inline-input new-input input-right input-narrow" form="frm-i-new" name="nebenkosten" placeholder="0,00"></td>
            <td><input class="inline-input new-input input-right input-narrow" form="frm-i-new" name="fixkosten" placeholder="0,00"></td>
            <td><input class="inline-input new-input input-right input-narrow" form="frm-i-new" name="kreditkosten" placeholder="0,00"></td>
            <td class="col-actions">
                <button type="button" class="btn btn-primary btn-xs" id="btn-new-i">+ Hinzufügen</button>
            </td>
        </tr></tbody>
    </table></div>
</div>