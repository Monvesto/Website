<?php
$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';

    if ($act === 'save') {
        $fields = ['objekt_name','einzugsdatum','kaltmiete','nebenkosten','fixkosten','kreditkosten','kaution','mieter','bemerkung'];
        $id = (int)($_POST['edit_id'] ?? 0);
        $vals = [];
        foreach ($fields as $f) {
            $v = trim($_POST[$f] ?? '');
            if (in_array($f, ['kaltmiete','nebenkosten','fixkosten','kreditkosten','kaution'])) $v = str_replace(',','.',$v);
            $vals[] = $v;
        }
        if ($id > 0) {
            $set = implode('=?,', $fields) . '=?';
            $db->prepare("UPDATE immobilien SET $set WHERE id=?")->execute([...$vals, $id]);
        } else {
            $ph = implode(',', array_fill(0, count($fields), '?'));
            $db->prepare("INSERT INTO immobilien (" . implode(',', $fields) . ") VALUES ($ph)")->execute($vals);
        }
        header("Location: ?page=immobilien&msg=saved"); exit;
    }

    if ($act === 'delete') {
        $db->prepare("DELETE FROM immobilien WHERE id=?")->execute([(int)$_POST['id']]);
        header("Location: ?page=immobilien&msg=saved"); exit;
    }
}
if (defined('HANDLE_POST_ONLY')) return;

$objekte = $db->query("SELECT * FROM immobilien ORDER BY aktiv DESC, objekt_name")->fetchAll();

function fmt_i(float $v): string { return number_format($v, 2, ',', '.') . ' €'; }
function he_i(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>

<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">Gespeichert.</div><?php endif; ?>

<div class="card">
    <div class="card-head">
        <h2 class="card-title">🏠 Immobilien</h2>
        <span class="badge badge-neutral"><?= count($objekte) ?> Objekte</span>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr>
                <th>Objekt</th><th>Mieter</th><th>Einzug</th><th class="col-right">Kaution</th>
                <th class="col-right">Kaltmiete</th><th class="col-right">NK</th>
                <th class="col-right">Fixkosten</th><th class="col-right">Kreditkosten</th>
                <th class="col-right">Cashflow</th><th>Status</th><th></th>
            </tr></thead>
            <tbody>
            <?php foreach ($objekte as $o):
                $cashflow = (float)$o['kaltmiete'] + (float)$o['nebenkosten'] - (float)$o['fixkosten'] - (float)$o['kreditkosten'];
            ?>
            <tr>
                <td><?= he_i($o['objekt_name']) ?></td>
                <td><?= he_i($o['mieter'] ?? '–') ?></td>
                <td><?= $o['einzugsdatum'] ? date('d.m.Y', strtotime($o['einzugsdatum'])) : '–' ?></td>
                <td class="col-right"><?= fmt_i((float)$o['kaution']) ?></td>
                <td class="col-right"><?= fmt_i((float)$o['kaltmiete']) ?></td>
                <td class="col-right"><?= fmt_i((float)$o['nebenkosten']) ?></td>
                <td class="col-right text-red"><?= fmt_i((float)$o['fixkosten']) ?></td>
                <td class="col-right text-red"><?= fmt_i((float)$o['kreditkosten']) ?></td>
                <td class="col-right fw-700 <?= $cashflow >= 0 ? 'text-green' : 'text-red' ?>"><?= fmt_i($cashflow) ?></td>
                <td><span class="badge <?= $o['aktiv'] ? 'badge-ok' : 'badge-neutral' ?>"><?= $o['aktiv'] ? 'Aktiv' : 'Inaktiv' ?></span></td>
                <td class="col-actions">
                    <form method="POST" action="?page=immobilien" class="form-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="delete">
                        <input type="hidden" name="id" value="<?= $o['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-xs btn-delete-confirm">✕</button>
                    </form>
                </td>
            </tr>
            <?php if (!empty($o['bemerkung'])): ?>
            <tr class="row-notiz">
                <td colspan="11" class="notiz-cell">💬 <?= he_i($o['bemerkung']) ?></td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
            <tr class="row-total">
                <td colspan="8" class="fw-700">Gesamt Cashflow</td>
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

<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Neues Objekt</h2></div>
    <form method="POST" action="?page=immobilien">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="save">
        <input type="hidden" name="edit_id" value="0">
        <div class="form-grid">
            <div class="form-group"><label>Objektname</label><input type="text" name="objekt_name" required></div>
            <div class="form-group"><label>Mieter</label><input type="text" name="mieter"></div>
            <div class="form-group"><label>Einzugsdatum</label><input type="date" name="einzugsdatum"></div>
            <div class="form-group"><label>Kaltmiete €</label><input type="text" name="kaltmiete" placeholder="0,00"></div>
            <div class="form-group"><label>Nebenkosten €</label><input type="text" name="nebenkosten" placeholder="0,00"></div>
            <div class="form-group"><label>Fixkosten €</label><input type="text" name="fixkosten" placeholder="0,00"></div>
            <div class="form-group"><label>Kreditkosten €</label><input type="text" name="kreditkosten" placeholder="0,00"></div>
            <div class="form-group"><label>Kaution €</label><input type="text" name="kaution" placeholder="0,00"></div>
            <div class="form-group fg-wide"><label>Bemerkung</label><textarea name="bemerkung" rows="2"></textarea></div>
        </div>
        <div class="form-actions form-actions--pad">
            <button type="submit" class="btn btn-primary">Speichern</button>
        </div>
    </form>
</div>