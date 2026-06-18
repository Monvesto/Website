<?php
$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';

    if ($act === 'save') {
        $datum   = $_POST['datum'] ?? date('Y-m-d');
        $bereich = trim($_POST['bereich'] ?? '');
        $art     = trim($_POST['einnahmeart'] ?? '');
        $betrag  = str_replace(',', '.', $_POST['betrag'] ?? '0');
        $notiz   = trim($_POST['notiz'] ?? '');
        $id      = (int)($_POST['edit_id'] ?? 0);
        if ($id > 0) {
            $db->prepare("UPDATE investments SET datum=?,bereich=?,einnahmeart=?,betrag=?,notiz=? WHERE id=?")->execute([$datum,$bereich,$art,$betrag,$notiz,$id]);
        } else {
            $db->prepare("INSERT INTO investments (datum,bereich,einnahmeart,betrag,notiz) VALUES (?,?,?,?,?)")->execute([$datum,$bereich,$art,$betrag,$notiz]);
        }
        header("Location: ?page=investments&msg=saved"); exit;
    }

    if ($act === 'delete') {
        $db->prepare("DELETE FROM investments WHERE id=?")->execute([(int)$_POST['id']]);
        header("Location: ?page=investments&msg=saved"); exit;
    }
}
if (defined('HANDLE_POST_ONLY')) return;

$eintraege   = $db->query("SELECT * FROM investments ORDER BY datum DESC")->fetchAll();
$bereiche    = $db->query("SELECT bereich, SUM(betrag) as gesamt, COUNT(*) as anz FROM investments GROUP BY bereich ORDER BY gesamt DESC")->fetchAll();
$gesamt      = array_sum(array_column($bereiche, 'gesamt'));
$monat_summe = (float)$db->query("SELECT COALESCE(SUM(betrag),0) FROM investments WHERE DATE_FORMAT(datum,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m')")->fetchColumn();

function fmt_v(float $v): string { return number_format($v, 2, ',', '.') . ' €'; }
function he_v(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>

<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">Gespeichert.</div><?php endif; ?>

<div class="kpi-grid kpi-grid--4">
    <div class="kpi-card kpi-card--info">
        <div class="kpi-label">Gesamt investiert</div>
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
            <div class="form-grid">
                <div class="form-group"><label>Datum</label><input type="date" name="datum" value="<?= date('Y-m-d') ?>"></div>
                <div class="form-group"><label>Bereich</label>
                    <select name="bereich">
                        <?php foreach (['Grid EA','Affiliate','P2P','Tagesgeld','Krypto','Copy Trading','Sonstiges'] as $b): ?>
                        <option><?= $b ?></option><?php endforeach; ?>
                    </select>
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
    <div class="card-head"><h2 class="card-title">Alle Einträge</h2></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Datum</th><th>Bereich</th><th>Art</th><th>Notiz</th><th class="col-right">Betrag</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($eintraege as $e): ?>
        <tr>
            <td><?= date('d.m.Y', strtotime($e['datum'])) ?></td>
            <td><span class="badge badge-neutral"><?= he_v($e['bereich']) ?></span></td>
            <td><?= he_v($e['einnahmeart'] ?? '–') ?></td>
            <td><?= he_v($e['notiz'] ?? '–') ?></td>
            <td class="col-right fw-700 text-green"><?= fmt_v((float)$e['betrag']) ?></td>
            <td class="col-actions">
                <form method="POST" action="?page=investments" class="form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="delete">
                    <input type="hidden" name="id" value="<?= $e['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-xs btn-delete-confirm">✕</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>