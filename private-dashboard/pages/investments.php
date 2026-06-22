<?php
// ════════════════════════════════════════════════
// investments.php – Investment-Einträge mit Bulk-Edit + Person-Filter
// Neuer Eintrag als separate Card mit Tabellen-Header
// ════════════════════════════════════════════════
$db     = get_db();
$person = $_GET['person'] ?? 'Marcel';
if (!in_array($person, ['Marcel','Kim','Beide'], true)) $person = 'Marcel';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';
    $pf  = $_POST['person_filter'] ?? $person;

    if ($act === 'save') {
        $datum   = $_POST['datum'] ?? date('Y-m-d');
        $bereich = trim($_POST['bereich'] ?? '');
        $art     = trim($_POST['einnahmeart'] ?? '');
        $betrag  = parse_betrag($_POST['betrag'] ?? '0');
        $notiz   = trim($_POST['notiz'] ?? '');
        $per     = $_POST['person'] ?? 'Beide';
        $id      = (int)($_POST['edit_id'] ?? 0);
        if ($id > 0) {
            $db->prepare("UPDATE investments SET datum=?,bereich=?,einnahmeart=?,betrag=?,notiz=?,person=? WHERE id=?")->execute([$datum,$bereich,$art,$betrag,$notiz,$per,$id]);
        } else {
            $db->prepare("INSERT INTO investments (datum,bereich,einnahmeart,betrag,notiz,person) VALUES (?,?,?,?,?,?)")->execute([$datum,$bereich,$art,$betrag,$notiz,$per]);
        }
        header("Location: ?page=investments&person=$pf&msg=saved"); exit;
    }

    if ($act === 'bulk_save') {
        foreach ($_POST['ids'] ?? [] as $id) {
            $id      = (int)$id;
            $row     = $_POST['rows'][$id] ?? [];
            $datum   = $row['datum'] ?? date('Y-m-d');
            $bereich = trim($row['bereich'] ?? '');
            $art     = trim($row['einnahmeart'] ?? '');
            $betrag  = parse_betrag($row['betrag'] ?? '0');
            $notiz   = trim($row['notiz'] ?? '');
            $per     = $row['person'] ?? 'Beide';
            if ($bereich === '') continue;
            $db->prepare("UPDATE investments SET datum=?,bereich=?,einnahmeart=?,betrag=?,notiz=?,person=? WHERE id=?")->execute([$datum,$bereich,$art,$betrag,$notiz,$per,$id]);
        }
        header("Location: ?page=investments&person=$pf&msg=saved"); exit;
    }

    if ($act === 'delete') {
        $db->prepare("DELETE FROM investments WHERE id=?")->execute([(int)$_POST['id']]);
        header("Location: ?page=investments&person=$pf&msg=saved"); exit;
    }
}
if (defined('HANDLE_POST_ONLY')) return;

$def_person    = ($person === 'Beide') ? 'Marcel' : $person;
$personen      = ['Marcel','Kim','Beide'];
$bereiche_list = ['Grid EA','Affiliate','P2P','Tagesgeld','Krypto','Copy Trading','Sonstiges'];

if ($person === 'Beide') {
    $eintraege   = $db->query("SELECT * FROM investments ORDER BY datum DESC")->fetchAll();
    $bereiche    = $db->query("SELECT bereich, SUM(betrag) as gesamt, COUNT(*) as anz FROM investments GROUP BY bereich ORDER BY gesamt DESC")->fetchAll();
    $gesamt      = array_sum(array_column($bereiche, 'gesamt'));
    $monat_summe = (float)$db->query("SELECT COALESCE(SUM(betrag),0) FROM investments WHERE DATE_FORMAT(datum,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m')")->fetchColumn();
} else {
    $s = $db->prepare("SELECT * FROM investments WHERE (person=? OR person='Beide') ORDER BY datum DESC");
    $s->execute([$person]); $eintraege = $s->fetchAll();
    $s = $db->prepare("SELECT bereich, SUM(betrag) as gesamt, COUNT(*) as anz FROM investments WHERE (person=? OR person='Beide') GROUP BY bereich ORDER BY gesamt DESC");
    $s->execute([$person]); $bereiche = $s->fetchAll();
    $gesamt = array_sum(array_column($bereiche, 'gesamt'));
    $s = $db->prepare("SELECT COALESCE(SUM(betrag),0) FROM investments WHERE DATE_FORMAT(datum,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m') AND (person=? OR person='Beide')");
    $s->execute([$person]); $monat_summe = (float)$s->fetchColumn();
}

function fmt_v(float $v): string { return number_format($v, 2, ',', '.') . ' €'; }
function he_v(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function sel_v(array $opts, string $cur): string {
    $out = '';
    foreach ($opts as $o) $out .= '<option value="'.he_v($o).'"'.($o===$cur?' selected':'').'>'.he_v($o).'</option>';
    return $out;
}
?>

<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">Gespeichert.</div><?php endif; ?>

<div class="finance-topbar">
    <div></div>
    <div class="person-switcher">
        <?php foreach (['Marcel','Kim','Beide'] as $p): ?>
        <a href="?page=investments&person=<?= $p ?>" class="person-btn <?= $person===$p?'active':'' ?>"><?= $p ?></a>
        <?php endforeach; ?>
    </div>
</div>

<!-- KPI Cards -->
<div class="kpi-grid kpi-grid--4 mt-4">
    <div class="kpi-card kpi-card--info">
        <div class="kpi-label">Gesamt investiert<?= $person!=='Beide'?' '.$person:'' ?></div>
        <div class="kpi-value kpi-value--md text-green"><?= fmt_v($gesamt) ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Diesen Monat</div>
        <div class="kpi-value kpi-value--md"><?= fmt_v($monat_summe) ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Bereiche</div>
        <div class="kpi-value kpi-value--md"><?= count($bereiche) ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Einträge</div>
        <div class="kpi-value kpi-value--md"><?= count($eintraege) ?></div>
    </div>
</div>

<!-- Nach Bereich -->
<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Nach Bereich</h2></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Bereich</th><th>Einträge</th><th class="col-right">Gesamt</th><th class="col-right">Anteil</th></tr></thead>
        <tbody>
        <?php foreach ($bereiche as $b):
            $anteil = $gesamt > 0 ? $b['gesamt'] / $gesamt * 100 : 0;
        ?>
        <tr>
            <td><?= he_v($b['bereich']) ?></td>
            <td><?= $b['anz'] ?></td>
            <td class="col-right fw-700 text-green"><?= fmt_v((float)$b['gesamt']) ?></td>
            <td class="col-right"><?= number_format($anteil, 1, ',', '.') ?>%</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>

<!-- Neuer Eintrag Card -->
<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Neuer Eintrag</h2></div>
    <form id="frm-iv-new" method="POST" action="?page=investments">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="save">
        <input type="hidden" name="edit_id" value="0">
        <input type="hidden" name="person_filter" value="<?= he_v($person) ?>">
    </form>
    <div class="table-wrap"><table class="data-table">
        <thead><tr>
            <th>Datum</th><th>Bereich</th><th>Art</th><th>Person</th><th>Notiz</th>
            <th>Betrag</th><th></th>
        </tr></thead>
        <tbody><tr>
            <td><input class="inline-input new-input" type="date" form="frm-iv-new" name="datum" value="<?= date('Y-m-d') ?>"></td>
            <td><select class="inline-input new-input" form="frm-iv-new" name="bereich">
                <?= sel_v($bereiche_list, 'Grid EA') ?>
            </select></td>
            <td><input class="inline-input new-input" form="frm-iv-new" name="einnahmeart" placeholder="z.B. Zinsen"></td>
            <td><select class="inline-input new-input" form="frm-iv-new" name="person">
                <?= sel_v($personen, $def_person) ?>
            </select></td>
            <td><input class="inline-input new-input" form="frm-iv-new" name="notiz" placeholder="Notiz"></td>
            <td><input class="inline-input new-input input-right input-narrow" form="frm-iv-new" name="betrag" placeholder="0,00"></td>
            <td class="col-actions">
                <button type="button" class="btn btn-primary btn-xs" id="btn-new-iv">+ Hinzufügen</button>
            </td>
        </tr></tbody>
    </table></div>
</div>

<!-- Alle Einträge mit Bulk-Edit -->
<div class="card mt-4" id="card-investments">
    <div class="card-head">
        <div class="card-head-left">
            <h2 class="card-title">Alle Einträge<?= $person!=='Beide'?' – '.$person:'' ?></h2>
        </div>
        <div class="bulk-bar">
            <button type="button" class="btn btn-ghost btn-sm" id="btn-edit-iv">✏ Bearbeiten</button>
            <button type="submit" form="frm-iv-bulk" class="btn btn-primary btn-sm btn-hidden" id="btn-save-iv">✓ Speichern</button>
        </div>
    </div>
    <form id="frm-iv-bulk" method="POST" action="?page=investments">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="bulk_save">
        <input type="hidden" name="person_filter" value="<?= he_v($person) ?>">
        <?php foreach ($eintraege as $e): ?>
        <input type="hidden" name="ids[]" value="<?= $e['id'] ?>">
        <?php endforeach; ?>
    </form>
    <div class="table-wrap"><table class="data-table">
        <thead><tr>
            <th>Datum</th><th>Bereich</th><th>Art</th><th>Person</th><th>Notiz</th>
            <th class="col-right">Betrag</th><th></th>
        </tr></thead>
        <tbody>
        <?php foreach ($eintraege as $e): $eid = $e['id']; ?>
        <tr>
            <td>
                <span class="ft-bulk"><?= date('d.m.Y', strtotime($e['datum'])) ?></span>
                <input class="inline-input fi-bulk" type="date" form="frm-iv-bulk" name="rows[<?= $eid ?>][datum]" value="<?= he_v($e['datum']) ?>">
            </td>
            <td>
                <span class="ft-bulk"><span class="badge badge-neutral"><?= he_v($e['bereich']) ?></span></span>
                <select class="inline-input fi-bulk" form="frm-iv-bulk" name="rows[<?= $eid ?>][bereich]">
                    <?= sel_v($bereiche_list, $e['bereich']) ?>
                </select>
            </td>
            <td>
                <span class="ft-bulk"><?= he_v($e['einnahmeart']??'–') ?></span>
                <input class="inline-input fi-bulk" form="frm-iv-bulk" name="rows[<?= $eid ?>][einnahmeart]" value="<?= he_v($e['einnahmeart']??'') ?>">
            </td>
            <td>
                <span class="ft-bulk"><?= he_v($e['person']??'–') ?></span>
                <select class="inline-input fi-bulk" form="frm-iv-bulk" name="rows[<?= $eid ?>][person]">
                    <?= sel_v($personen, $e['person']??'Beide') ?>
                </select>
            </td>
            <td>
                <span class="ft-bulk"><?= he_v($e['notiz']??'–') ?></span>
                <input class="inline-input fi-bulk" form="frm-iv-bulk" name="rows[<?= $eid ?>][notiz]" value="<?= he_v($e['notiz']??'') ?>">
            </td>
            <td class="col-right">
                <span class="ft-bulk fw-700 text-green"><?= fmt_v((float)$e['betrag']) ?></span>
                <input class="inline-input fi-bulk input-right input-narrow" form="frm-iv-bulk" name="rows[<?= $eid ?>][betrag]" value="<?= he_v(number_format((float)$e['betrag'],2,',','.')) ?>">
            </td>
            <td class="col-actions">
                <form id="frm-iv-del-<?= $eid ?>" method="POST" action="?page=investments" hidden>
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="delete">
                    <input type="hidden" name="id" value="<?= $eid ?>">
                    <input type="hidden" name="person_filter" value="<?= he_v($person) ?>">
                </form>
                <button type="submit" form="frm-iv-del-<?= $eid ?>" class="btn btn-danger btn-xs fi-bulk btn-delete-confirm">✕</button>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>