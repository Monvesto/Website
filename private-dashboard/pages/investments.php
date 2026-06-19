<?php
$db     = get_db();
$person = $_GET['person'] ?? 'Marcel';
if (!in_array($person, ['Marcel','Kim','Beide'], true)) $person = 'Marcel';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act    = $_POST['act'] ?? '';
    $person = $_POST['person_filter'] ?? $person;

    if ($act === 'save') {
        $datum   = $_POST['datum'] ?? date('Y-m-d');
        $bereich = trim($_POST['bereich'] ?? '');
        $art     = trim($_POST['einnahmeart'] ?? '');
        $betrag  = str_replace(',', '.', $_POST['betrag'] ?? '0');
        $notiz   = trim($_POST['notiz'] ?? '');
        $per     = $_POST['person'] ?? 'Beide';
        $id      = (int)($_POST['edit_id'] ?? 0);
        if ($id > 0) {
            $db->prepare("UPDATE investments SET datum=?,bereich=?,einnahmeart=?,betrag=?,notiz=?,person=? WHERE id=?")->execute([$datum,$bereich,$art,$betrag,$notiz,$per,$id]);
        } else {
            $db->prepare("INSERT INTO investments (datum,bereich,einnahmeart,betrag,notiz,person) VALUES (?,?,?,?,?,?)")->execute([$datum,$bereich,$art,$betrag,$notiz,$per]);
        }
        header("Location: ?page=investments&person=$person&msg=saved"); exit;
    }

    if ($act === 'delete') {
        $db->prepare("DELETE FROM investments WHERE id=?")->execute([(int)$_POST['id']]);
        header("Location: ?page=investments&person=$person&msg=saved"); exit;
    }
}
if (defined('HANDLE_POST_ONLY')) return;

$def_person = ($person === 'Beide') ? 'Marcel' : $person;

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
function sel_person_v(string $cur): string {
    $out = '';
    foreach (['Marcel','Kim','Beide'] as $p)
        $out .= '<option value="'.he_v($p).'"'.($p===$cur?' selected':'').'>'.he_v($p).'</option>';
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

<div class="dashboard-row mt-4">
    <div class="card">
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

    <div class="card">
        <div class="card-head"><h2 class="card-title">Neuer Eintrag</h2></div>
        <form method="POST" action="?page=investments">
            <?= csrf_field() ?>
            <input type="hidden" name="act" value="save">
            <input type="hidden" name="edit_id" value="0">
            <input type="hidden" name="person_filter" value="<?= he_v($person) ?>">
            <div class="form-grid">
                <div class="form-group"><label>Datum</label><input type="date" name="datum" value="<?= date('Y-m-d') ?>"></div>
                <div class="form-group"><label>Bereich</label>
                    <select name="bereich">
                        <?php foreach (['Grid EA','Affiliate','P2P','Tagesgeld','Krypto','Copy Trading','Sonstiges'] as $b): ?>
                        <option><?= $b ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Person</label>
                    <select name="person"><?= sel_person_v($def_person) ?></select>
                </div>
                <div class="form-group"><label>Einnahmeart</label><input type="text" name="einnahmeart" placeholder="z.B. Zinsen"></div>
                <div class="form-group"><label>Betrag €</label><input type="text" name="betrag" placeholder="0,00"></div>
                <div class="form-group fg-wide"><label>Notiz</label><input type="text" name="notiz"></div>
            </div>
            <div class="form-actions form-actions--pad">
                <button type="submit" class="btn btn-primary">Hinzufügen</button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Alle Einträge<?= $person!=='Beide'?' – '.$person:'' ?></h2></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Datum</th><th>Bereich</th><th>Art</th><?php if($person==='Beide'): ?><th>Person</th><?php endif; ?><th>Notiz</th><th class="col-right">Betrag</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($eintraege as $e): ?>
        <tr>
            <td><?= date('d.m.Y', strtotime($e['datum'])) ?></td>
            <td><span class="badge badge-neutral"><?= he_v($e['bereich']) ?></span></td>
            <td><?= he_v($e['einnahmeart'] ?? '–') ?></td>
            <?php if($person==='Beide'): ?><td><?= he_v($e['person'] ?? '–') ?></td><?php endif; ?>
            <td><?= he_v($e['notiz'] ?? '–') ?></td>
            <td class="col-right fw-700 text-green"><?= fmt_v((float)$e['betrag']) ?></td>
            <td class="col-actions">
                <form method="POST" action="?page=investments" class="form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="delete">
                    <input type="hidden" name="id" value="<?= $e['id'] ?>">
                    <input type="hidden" name="person_filter" value="<?= he_v($person) ?>">
                    <button type="submit" class="btn btn-danger btn-xs btn-delete-confirm">✕</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>