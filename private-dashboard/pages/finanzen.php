<?php
$db      = get_db();
$tab     = $_GET['tab'] ?? 'uebersicht';
$person  = $_GET['person'] ?? 'Marcel';
$errors  = [];
$success = '';

if (!in_array($person, ['Marcel','Kim','Beide'], true)) $person = 'Marcel';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';
    $pf  = $_POST['person_filter'] ?? $person;

    if ($act === 'einnahme_save') {
        $bez = trim($_POST['bezeichnung'] ?? '');
        $bet = str_replace(',', '.', $_POST['betrag'] ?? '0');
        $per = $_POST['person'] ?? 'Marcel';
        $kat = trim($_POST['kategorie'] ?? '');
        $tur = $_POST['turnus'] ?? 'Monatlich';
        if ($bez === '') $errors[] = 'Bezeichnung fehlt.';
        if (empty($errors)) {
            $id = (int)($_POST['edit_id'] ?? 0);
            if ($id > 0) {
                $db->prepare('UPDATE einnahmen SET bezeichnung=?,betrag=?,person=?,kategorie=?,turnus=? WHERE id=?')->execute([$bez,$bet,$per,$kat,$tur,$id]);
            } else {
                $db->prepare('INSERT INTO einnahmen (bezeichnung,betrag,person,kategorie,turnus) VALUES (?,?,?,?,?)')->execute([$bez,$bet,$per,$kat,$tur]);
            }
            header("Location: ?page=finanzen&tab=einnahmen&person=$pf&msg=saved"); exit;
        }
        $tab = 'einnahmen';
    }

    if ($act === 'einnahme_delete') {
        $db->prepare('DELETE FROM einnahmen WHERE id=?')->execute([(int)$_POST['id']]);
        header("Location: ?page=finanzen&tab=einnahmen&person=$pf&msg=saved"); exit;
    }

    if ($act === 'ausgabe_save') {
        $bez = trim($_POST['bezeichnung'] ?? '');
        $bet = str_replace(',', '.', $_POST['betrag'] ?? '0');
        $per = $_POST['person'] ?? 'Marcel';
        $kat = trim($_POST['kategorie'] ?? '');
        $tur = $_POST['turnus'] ?? 'Monatlich';
        if ($bez === '') $errors[] = 'Bezeichnung fehlt.';
        if (empty($errors)) {
            $id = (int)($_POST['edit_id'] ?? 0);
            if ($id > 0) {
                $db->prepare('UPDATE ausgaben SET bezeichnung=?,betrag=?,person=?,kategorie=?,turnus=? WHERE id=?')->execute([$bez,$bet,$per,$kat,$tur,$id]);
            } else {
                $db->prepare('INSERT INTO ausgaben (bezeichnung,betrag,person,kategorie,turnus) VALUES (?,?,?,?,?)')->execute([$bez,$bet,$per,$kat,$tur]);
            }
            header("Location: ?page=finanzen&tab=ausgaben&person=$pf&msg=saved"); exit;
        }
        $tab = 'ausgaben';
    }

    if ($act === 'ausgabe_delete') {
        $db->prepare('DELETE FROM ausgaben WHERE id=?')->execute([(int)$_POST['id']]);
        header("Location: ?page=finanzen&tab=ausgaben&person=$pf&msg=saved"); exit;
    }

    if ($act === 'schuld_save') {
        $gl = trim($_POST['glaeubiger'] ?? '');
        $ss = str_replace(',','.',$_POST['startsumme'] ?? '0');
        $rs = str_replace(',','.',$_POST['restsumme']  ?? '0');
        $rt = str_replace(',','.',$_POST['rate']       ?? '0');
        $no = trim($_POST['notiz'] ?? '');
        if ($gl === '') $errors[] = 'Gläubiger fehlt.';
        if (empty($errors)) {
            $id = (int)($_POST['edit_id'] ?? 0);
            if ($id > 0) {
                $db->prepare('UPDATE verbindlichkeiten SET glaeubiger=?,startsumme=?,restsumme=?,rate=?,notiz=? WHERE id=?')->execute([$gl,$ss,$rs,$rt,$no,$id]);
            } else {
                $db->prepare('INSERT INTO verbindlichkeiten (glaeubiger,startsumme,restsumme,rate,notiz) VALUES (?,?,?,?,?)')->execute([$gl,$ss,$rs,$rt,$no]);
            }
            header("Location: ?page=finanzen&tab=schulden&person=$pf&msg=saved"); exit;
        }
        $tab = 'schulden';
    }

    if ($act === 'schuld_delete') {
        $db->prepare('DELETE FROM verbindlichkeiten WHERE id=?')->execute([(int)$_POST['id']]);
        header("Location: ?page=finanzen&tab=schulden&person=$pf&msg=saved"); exit;
    }
}

// Bei POST-only Mode hier aufhören
if (defined('HANDLE_POST_ONLY')) return;

// ... Rest der finanzen.php bleibt exakt gleich wie zuletzt ...

$msgs = ['saved' => 'Gespeichert.'];
if (isset($_GET['msg'], $msgs[$_GET['msg']])) $success = $msgs[$_GET['msg']];

function get_rows(PDO $db, string $table, string $person): array {
    if ($person === 'Beide') {
        return $db->query("SELECT * FROM $table ORDER BY person, kategorie, bezeichnung")->fetchAll();
    }
    $s = $db->prepare("SELECT * FROM $table WHERE person=? ORDER BY kategorie, bezeichnung");
    $s->execute([$person]);
    return $s->fetchAll();
}

$einnahmen_alle = get_rows($db, 'einnahmen', $person);
$ausgaben_alle  = get_rows($db, 'ausgaben',  $person);
$schulden_alle  = $db->query('SELECT * FROM verbindlichkeiten ORDER BY glaeubiger')->fetchAll();

function sum_active(array $rows): float {
    return array_sum(array_map(fn($r) => $r['aktiv'] ? (float)$r['betrag'] : 0, $rows));
}

$ein_gesamt      = sum_active($einnahmen_alle);
$aus_gesamt      = sum_active($ausgaben_alle);
$ueb_gesamt      = $ein_gesamt - $aus_gesamt;
$sparquote       = $ein_gesamt > 0 ? $ueb_gesamt / $ein_gesamt : 0;
$schulden_gesamt = array_sum(array_column($schulden_alle, 'restsumme'));
$raten_gesamt    = array_sum(array_column($schulden_alle, 'rate'));

$ein_marcel = 0; $ein_kim = 0; $aus_marcel = 0; $aus_kim = 0;
if ($person === 'Beide') {
    foreach ($einnahmen_alle as $r) { if (!$r['aktiv']) continue; if ($r['person']==='Marcel') $ein_marcel+=$r['betrag']; else $ein_kim+=$r['betrag']; }
    foreach ($ausgaben_alle  as $r) { if (!$r['aktiv']) continue; if ($r['person']==='Marcel') $aus_marcel+=$r['betrag']; else $aus_kim+=$r['betrag']; }
}

function fmt2(float $v, bool $sign = false): string {
    $s = number_format(abs($v), 2, ',', '.');
    if ($sign) return ($v >= 0 ? '+' : '–') . $s . ' €';
    return ($v < 0 ? '–' : '') . $s . ' €';
}
function he(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function sel(array $opts, string $cur): string {
    $out = '';
    foreach ($opts as $o) $out .= '<option value="'.he($o).'"'.($o===$cur?' selected':'').'>'.he($o).'</option>';
    return $out;
}
function kat_sel(array $opts, string $cur): string {
    $out = '<option value="">– wählen –</option>';
    foreach ($opts as $o) $out .= '<option value="'.he($o).'"'.($o===$cur?' selected':'').'>'.he($o).'</option>';
    return $out;
}

$personen      = ['Marcel','Kim','Beide'];
$turnusse      = ['Monatlich','Jährlich','Einmalig'];
$kat_einnahmen = ['Gehalt','Nebeneinkommen','Immobilien','Investments','Sonstiges'];
$kat_ausgaben  = ['Wohnen','KFZ','Versicherung','Kommunikation','Unterhaltung','Lebensmittel','Haustiere','Gesundheit','Pflege','Schulden','Immobilien','Investments','Business','Bank','Sonstiges'];
?>

<?php if ($success): ?><div class="alert alert-success"><?= he($success) ?></div><?php endif; ?>
<?php if ($errors):  ?><div class="alert alert-error"><?= implode('<br>', array_map('htmlspecialchars',$errors)) ?></div><?php endif; ?>

<div class="finance-topbar">
    <div class="tab-bar">
        <?php foreach (['uebersicht'=>'Übersicht','einnahmen'=>'Einnahmen','ausgaben'=>'Ausgaben','schulden'=>'Schulden'] as $k=>$l): ?>
        <a href="?page=finanzen&tab=<?= $k ?>&person=<?= $person ?>" class="tab-link <?= $tab===$k?'active':'' ?>"><?= $l ?></a>
        <?php endforeach; ?>
    </div>
    <div class="person-switcher">
        <?php foreach (['Marcel','Kim','Beide'] as $p): ?>
        <a href="?page=finanzen&tab=<?= $tab ?>&person=<?= $p ?>" class="person-btn <?= $person===$p?'active':'' ?>"><?= $p ?></a>
        <?php endforeach; ?>
    </div>
</div>

<?php if ($tab === 'uebersicht'): ?>
<!-- ════ ÜBERSICHT ════ -->

<div class="kpi-grid kpi-grid--4 mt-4">
    <div class="kpi-card">
        <div class="kpi-label">📥 Einnahmen<?= $person!=='Beide'?' '.$person:'' ?></div>
        <div class="kpi-value kpi-value--md text-green"><?= fmt2($ein_gesamt) ?></div>
        <?php if ($person==='Beide'): ?><div class="kpi-sub">Marcel <?= fmt2($ein_marcel) ?> · Kim <?= fmt2($ein_kim) ?></div><?php endif; ?>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">📤 Ausgaben<?= $person!=='Beide'?' '.$person:'' ?></div>
        <div class="kpi-value kpi-value--md text-red"><?= fmt2($aus_gesamt) ?></div>
        <?php if ($person==='Beide'): ?><div class="kpi-sub">Marcel <?= fmt2($aus_marcel) ?> · Kim <?= fmt2($aus_kim) ?></div><?php endif; ?>
    </div>
    <div class="kpi-card <?= $ueb_gesamt>=0?'kpi-card--info':'kpi-card--alert' ?>">
        <div class="kpi-label">💰 Überschuss</div>
        <div class="kpi-value kpi-value--md <?= $ueb_gesamt>=0?'text-green':'text-red' ?>"><?= fmt2($ueb_gesamt,true) ?></div>
        <div class="kpi-sub">Sparquote <?= number_format($sparquote*100,1,',','.') ?>%</div>
    </div>
    <div class="kpi-card kpi-card--alert">
        <div class="kpi-label">🏦 Schulden</div>
        <div class="kpi-value kpi-value--md text-red"><?= fmt2($schulden_gesamt) ?></div>
        <div class="kpi-sub">Raten <?= fmt2($raten_gesamt) ?>/Mon.</div>
    </div>
</div>

<?php if ($person==='Beide'): ?>
<div class="dashboard-row mt-4">
    <div class="card"><div class="card-head"><h2 class="card-title">👤 Marcel</h2></div>
        <div style="padding:20px">
            <div class="finance-split-row"><span>Einnahmen</span><span class="text-green fw-700"><?= fmt2($ein_marcel) ?></span></div>
            <div class="finance-split-row"><span>Ausgaben</span><span class="text-red fw-700"><?= fmt2($aus_marcel) ?></span></div>
            <div class="finance-split-row finance-split-total"><span>Überschuss</span><span class="<?= ($ein_marcel-$aus_marcel)>=0?'text-green':'text-red' ?> fw-700"><?= fmt2($ein_marcel-$aus_marcel,true) ?></span></div>
        </div>
    </div>
    <div class="card"><div class="card-head"><h2 class="card-title">👤 Kim</h2></div>
        <div style="padding:20px">
            <div class="finance-split-row"><span>Einnahmen</span><span class="text-green fw-700"><?= fmt2($ein_kim) ?></span></div>
            <div class="finance-split-row"><span>Ausgaben</span><span class="text-red fw-700"><?= fmt2($aus_kim) ?></span></div>
            <div class="finance-split-row finance-split-total"><span>Überschuss</span><span class="<?= ($ein_kim-$aus_kim)>=0?'text-green':'text-red' ?> fw-700"><?= fmt2($ein_kim-$aus_kim,true) ?></span></div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="dashboard-row mt-4">
    <div class="card">
        <div class="card-head"><h2 class="card-title">Einnahmen im Detail</h2><span class="badge badge-ok"><?= fmt2($ein_gesamt) ?>/Mon.</span></div>
        <div class="table-wrap"><table class="data-table">
            <thead><tr><th>Bezeichnung</th><?php if($person==='Beide'): ?><th>Person</th><?php endif; ?><th>Kategorie</th><th style="text-align:right">Betrag</th></tr></thead>
            <tbody>
            <?php foreach ($einnahmen_alle as $e): if (!$e['aktiv']) continue; ?>
            <tr>
                <td><?= he($e['bezeichnung']) ?></td>
                <?php if($person==='Beide'): ?><td><?= he($e['person']) ?></td><?php endif; ?>
                <td><span class="badge badge-neutral"><?= he($e['kategorie']??'–') ?></span></td>
                <td style="text-align:right" class="fw-700 text-green"><?= fmt2((float)$e['betrag']) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr style="border-top:1px solid var(--border)">
                <td colspan="<?= $person==='Beide'?3:2 ?>" class="fw-700">Gesamt</td>
                <td style="text-align:right" class="fw-700 text-green"><?= fmt2($ein_gesamt) ?></td>
            </tr>
            </tbody>
        </table></div>
    </div>
    <div class="card">
        <div class="card-head"><h2 class="card-title">Ausgaben im Detail</h2><span class="badge badge-danger"><?= fmt2($aus_gesamt) ?>/Mon.</span></div>
        <div class="table-wrap"><table class="data-table">
            <thead><tr><th>Bezeichnung</th><?php if($person==='Beide'): ?><th>Person</th><?php endif; ?><th>Kategorie</th><th style="text-align:right">Betrag</th></tr></thead>
            <tbody>
            <?php foreach ($ausgaben_alle as $a): if (!$a['aktiv']) continue; ?>
            <tr>
                <td><?= he($a['bezeichnung']) ?></td>
                <?php if($person==='Beide'): ?><td><?= he($a['person']) ?></td><?php endif; ?>
                <td><span class="badge badge-neutral"><?= he($a['kategorie']??'–') ?></span></td>
                <td style="text-align:right" class="fw-700 text-red"><?= fmt2((float)$a['betrag']) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr style="border-top:1px solid var(--border)">
                <td colspan="<?= $person==='Beide'?3:2 ?>" class="fw-700">Gesamt</td>
                <td style="text-align:right" class="fw-700 text-red"><?= fmt2($aus_gesamt) ?></td>
            </tr>
            </tbody>
        </table></div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Ausgaben nach Kategorie</h2></div>
    <?php
    $kat_summen = [];
    foreach ($ausgaben_alle as $a) {
        if (!$a['aktiv']) continue;
        $k = $a['kategorie'] ?: 'Sonstiges';
        $kat_summen[$k] = ($kat_summen[$k] ?? 0) + (float)$a['betrag'];
    }
    arsort($kat_summen);
    $kat_total = array_sum($kat_summen);
    ?>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Kategorie</th><th>Anteil</th><th style="text-align:right">Betrag</th></tr></thead>
        <tbody>
        <?php foreach ($kat_summen as $kat => $sum):
            $anteil = $kat_total > 0 ? $sum / $kat_total : 0;
        ?>
        <tr>
            <td><?= he($kat) ?></td>
            <td>
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="flex:1;background:var(--border);border-radius:4px;height:6px;overflow:hidden;min-width:80px">
                        <div style="height:100%;background:var(--text-muted);border-radius:4px;width:<?= number_format($anteil*100,1,',','.') ?>%"></div>
                    </div>
                    <span style="font-size:12px;color:var(--text-muted);min-width:36px"><?= number_format($anteil*100,0) ?>%</span>
                </div>
            </td>
            <td style="text-align:right" class="fw-700"><?= fmt2($sum) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>

<?php elseif ($tab === 'einnahmen'): ?>
<!-- ════ EINNAHMEN ════ -->

<div class="card mt-4">
    <div class="card-head">
        <h2 class="card-title">Einnahmen<?= $person!=='Beide'?' – '.$person:'' ?></h2>
        <span class="badge badge-ok"><?= fmt2($ein_gesamt) ?>/Mon.</span>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Bezeichnung</th>
                    <?php if($person==='Beide'): ?><th>Person</th><?php endif; ?>
                    <th>Kategorie</th>
                    <th>Turnus</th>
                    <th style="text-align:right">Betrag</th>
                    <th style="text-align:right">Aktionen</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($einnahmen_alle as $e): $eid = $e['id']; ?>
            <tr id="row-e-<?= $eid ?>">
                <td>
                    <span class="ft"><?= he($e['bezeichnung']) ?></span>
                    <input class="inline-input fi" form="frm-e-<?= $eid ?>" name="bezeichnung" value="<?= he($e['bezeichnung']) ?>" required>
                </td>
                <?php if($person==='Beide'): ?>
                <td>
                    <span class="ft"><?= he($e['person']) ?></span>
                    <select class="inline-input fi" form="frm-e-<?= $eid ?>" name="person"><?= sel($personen,$e['person']) ?></select>
                </td>
                <?php endif; ?>
                <td>
                    <span class="ft"><span class="badge badge-neutral"><?= he($e['kategorie']??'–') ?></span></span>
                    <select class="inline-input fi" form="frm-e-<?= $eid ?>" name="kategorie"><?= kat_sel($kat_einnahmen,$e['kategorie']??'') ?></select>
                </td>
                <td>
                    <span class="ft"><?= he($e['turnus']) ?></span>
                    <select class="inline-input fi" form="frm-e-<?= $eid ?>" name="turnus"><?= sel($turnusse,$e['turnus']) ?></select>
                </td>
                <td style="text-align:right">
                    <span class="ft fw-700 text-green"><?= fmt2((float)$e['betrag']) ?></span>
                    <input class="inline-input fi" form="frm-e-<?= $eid ?>" name="betrag" value="<?= he(number_format((float)$e['betrag'],2,',','.')) ?>" style="text-align:right;max-width:90px">
                </td>
                <td style="text-align:right;white-space:nowrap">
                    <form id="frm-e-<?= $eid ?>" method="POST" action="?page=finanzen" style="display:none">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="einnahme_save">
                        <input type="hidden" name="edit_id" value="<?= $eid ?>">
                        <input type="hidden" name="person_filter" value="<?= he($person) ?>">
                    </form>
                    <button type="button" class="btn btn-ghost btn-xs b-edit"   onclick="rowEdit('e',<?= $eid ?>)">Bearb.</button>
                    <button type="button" class="btn btn-primary btn-xs b-save" onclick="rowSave('e',<?= $eid ?>)">Speichern</button>
                    <button type="button" class="btn btn-ghost btn-xs b-cancel" onclick="rowCancel('e',<?= $eid ?>)">Abbruch</button>
                    <form method="POST" action="?page=finanzen" style="display:inline" onsubmit="return confirm('Diesen Eintrag wirklich löschen?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="einnahme_delete">
                        <input type="hidden" name="id" value="<?= $eid ?>">
                        <input type="hidden" name="person_filter" value="<?= he($person) ?>">
                        <button type="submit" class="btn btn-danger btn-xs">✕</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>

            <!-- NEUE ZEILE -->
            <tr>
                <td colspan="<?= $person==='Beide'?6:5 ?>" style="padding:4px 16px 0">
                    <span style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--green)">Neuer Datensatz</span>
                </td>
            </tr>
            <tr class="new-row">
                <td><input class="inline-input new-input" form="frm-e-new" name="bezeichnung" placeholder="Bezeichnung" required></td>
                <?php if($person==='Beide'): ?>
                <td><select class="inline-input new-input" form="frm-e-new" name="person"><?= sel($personen,'Marcel') ?></select></td>
                <?php endif; ?>
                <td><select class="inline-input new-input" form="frm-e-new" name="kategorie"><?= kat_sel($kat_einnahmen,'') ?></select></td>
                <td><select class="inline-input new-input" form="frm-e-new" name="turnus"><?= sel($turnusse,'Monatlich') ?></select></td>
                <td><input class="inline-input new-input" form="frm-e-new" name="betrag" placeholder="0,00" style="text-align:right;max-width:90px"></td>
                <td style="text-align:right">
                    <form id="frm-e-new" method="POST" action="?page=finanzen" style="display:none">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="einnahme_save">
                        <input type="hidden" name="edit_id" value="0">
                        <input type="hidden" name="person_filter" value="<?= he($person) ?>">
                    </form>
                    <button type="button" class="btn btn-primary btn-xs" onclick="document.getElementById('frm-e-new').submit()">+ Hinzufügen</button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($tab === 'ausgaben'): ?>
<!-- ════ AUSGABEN ════ -->

<div class="card mt-4">
    <div class="card-head">
        <h2 class="card-title">Ausgaben<?= $person!=='Beide'?' – '.$person:'' ?></h2>
        <span class="badge badge-danger"><?= fmt2($aus_gesamt) ?>/Mon.</span>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Bezeichnung</th>
                    <?php if($person==='Beide'): ?><th>Person</th><?php endif; ?>
                    <th>Kategorie</th>
                    <th>Turnus</th>
                    <th style="text-align:right">Betrag</th>
                    <th style="text-align:right">Aktionen</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($ausgaben_alle as $a): $aid = $a['id']; ?>
            <tr id="row-a-<?= $aid ?>">
                <td>
                    <span class="ft"><?= he($a['bezeichnung']) ?></span>
                    <input class="inline-input fi" form="frm-a-<?= $aid ?>" name="bezeichnung" value="<?= he($a['bezeichnung']) ?>" required>
                </td>
                <?php if($person==='Beide'): ?>
                <td>
                    <span class="ft"><?= he($a['person']) ?></span>
                    <select class="inline-input fi" form="frm-a-<?= $aid ?>" name="person"><?= sel($personen,$a['person']) ?></select>
                </td>
                <?php endif; ?>
                <td>
                    <span class="ft"><span class="badge badge-neutral"><?= he($a['kategorie']??'–') ?></span></span>
                    <select class="inline-input fi" form="frm-a-<?= $aid ?>" name="kategorie"><?= kat_sel($kat_ausgaben,$a['kategorie']??'') ?></select>
                </td>
                <td>
                    <span class="ft"><?= he($a['turnus']) ?></span>
                    <select class="inline-input fi" form="frm-a-<?= $aid ?>" name="turnus"><?= sel($turnusse,$a['turnus']) ?></select>
                </td>
                <td style="text-align:right">
                    <span class="ft fw-700 text-red"><?= fmt2((float)$a['betrag']) ?></span>
                    <input class="inline-input fi" form="frm-a-<?= $aid ?>" name="betrag" value="<?= he(number_format((float)$a['betrag'],2,',','.')) ?>" style="text-align:right;max-width:90px">
                </td>
                <td style="text-align:right;white-space:nowrap">
                    <form id="frm-a-<?= $aid ?>" method="POST" action="?page=finanzen" style="display:none">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="ausgabe_save">
                        <input type="hidden" name="edit_id" value="<?= $aid ?>">
                        <input type="hidden" name="person_filter" value="<?= he($person) ?>">
                    </form>
                    <button type="button" class="btn btn-ghost btn-xs b-edit"   onclick="rowEdit('a',<?= $aid ?>)">Bearb.</button>
                    <button type="button" class="btn btn-primary btn-xs b-save" onclick="rowSave('a',<?= $aid ?>)">Speichern</button>
                    <button type="button" class="btn btn-ghost btn-xs b-cancel" onclick="rowCancel('a',<?= $aid ?>)">Abbruch</button>
                    <form method="POST" action="?page=finanzen" style="display:inline" onsubmit="return confirm('Diesen Eintrag wirklich löschen?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="ausgabe_delete">
                        <input type="hidden" name="id" value="<?= $aid ?>">
                        <input type="hidden" name="person_filter" value="<?= he($person) ?>">
                        <button type="submit" class="btn btn-danger btn-xs">✕</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>

            <!-- NEUE ZEILE -->
            <tr>
                <td colspan="<?= $person==='Beide'?6:5 ?>" style="padding:4px 16px 0">
                    <span style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--green)">Neuer Datensatz</span>
                </td>
            </tr>
            <tr class="new-row">
                <td><input class="inline-input new-input" form="frm-a-new" name="bezeichnung" placeholder="Bezeichnung" required></td>
                <?php if($person==='Beide'): ?>
                <td><select class="inline-input new-input" form="frm-a-new" name="person"><?= sel($personen,'Marcel') ?></select></td>
                <?php endif; ?>
                <td><select class="inline-input new-input" form="frm-a-new" name="kategorie"><?= kat_sel($kat_ausgaben,'') ?></select></td>
                <td><select class="inline-input new-input" form="frm-a-new" name="turnus"><?= sel($turnusse,'Monatlich') ?></select></td>
                <td><input class="inline-input new-input" form="frm-a-new" name="betrag" placeholder="0,00" style="text-align:right;max-width:90px"></td>
                <td style="text-align:right">
                    <form id="frm-a-new" method="POST" action="?page=finanzen" style="display:none">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="ausgabe_save">
                        <input type="hidden" name="edit_id" value="0">
                        <input type="hidden" name="person_filter" value="<?= he($person) ?>">
                    </form>
                    <button type="button" class="btn btn-primary btn-xs" onclick="document.getElementById('frm-a-new').submit()">+ Hinzufügen</button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($tab === 'schulden'): ?>
<!-- ════ SCHULDEN ════ -->

<div class="card mt-4">
    <div class="card-head">
        <h2 class="card-title">Verbindlichkeiten</h2>
        <span class="badge badge-danger">Gesamt: <?= fmt2($schulden_gesamt) ?></span>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Gläubiger</th>
                    <th>Startsumme</th>
                    <th>Restsumme</th>
                    <th>Rate/Mon.</th>
                    <th>Abbezahlt</th>
                    <th>Notiz</th>
                    <th style="text-align:right">Aktionen</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($schulden_alle as $s): $sid = $s['id'];
                $abgezahlt = (float)$s['startsumme'] > 0
                    ? max(0, min(1, 1 - (float)$s['restsumme'] / (float)$s['startsumme']))
                    : 0;
            ?>
            <tr id="row-s-<?= $sid ?>">
                <td>
                    <span class="ft"><?= he($s['glaeubiger']) ?></span>
                    <input class="inline-input fi" form="frm-s-<?= $sid ?>" name="glaeubiger" value="<?= he($s['glaeubiger']) ?>" required>
                </td>
                <td>
                    <span class="ft"><?= fmt2((float)$s['startsumme']) ?></span>
                    <input class="inline-input fi" form="frm-s-<?= $sid ?>" name="startsumme" value="<?= he(number_format((float)$s['startsumme'],2,',','.')) ?>" style="max-width:90px">
                </td>
                <td>
                    <span class="ft fw-700 text-red"><?= fmt2((float)$s['restsumme']) ?></span>
                    <input class="inline-input fi" form="frm-s-<?= $sid ?>" name="restsumme" value="<?= he(number_format((float)$s['restsumme'],2,',','.')) ?>" style="max-width:90px">
                </td>
                <td>
                    <span class="ft"><?= $s['rate']>0?fmt2((float)$s['rate']):'–' ?></span>
                    <input class="inline-input fi" form="frm-s-<?= $sid ?>" name="rate" value="<?= he(number_format((float)$s['rate'],2,',','.')) ?>" style="max-width:90px">
                </td>
                <td>
                    <div class="ft" style="display:flex;align-items:center;gap:8px">
                        <div style="flex:1;background:var(--border);border-radius:4px;height:6px;overflow:hidden;min-width:60px">
                            <div style="height:100%;background:var(--green);border-radius:4px;width:<?= number_format($abgezahlt*100,0) ?>%"></div>
                        </div>
                        <span style="font-size:12px;color:var(--text-muted)"><?= number_format($abgezahlt*100,0) ?>%</span>
                    </div>
                    <span class="fi" style="font-size:11px;color:var(--text-muted)">auto</span>
                </td>
                <td>
                    <span class="ft"><?= he($s['notiz']??'–') ?></span>
                    <input class="inline-input fi" form="frm-s-<?= $sid ?>" name="notiz" value="<?= he($s['notiz']??'') ?>">
                </td>
                <td style="text-align:right;white-space:nowrap">
                    <form id="frm-s-<?= $sid ?>" method="POST" action="?page=finanzen" style="display:none">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="schuld_save">
                        <input type="hidden" name="edit_id" value="<?= $sid ?>">
                        <input type="hidden" name="person_filter" value="<?= he($person) ?>">
                    </form>
                    <button type="button" class="btn btn-ghost btn-xs b-edit"   onclick="rowEdit('s',<?= $sid ?>)">Bearb.</button>
                    <button type="button" class="btn btn-primary btn-xs b-save" onclick="rowSave('s',<?= $sid ?>)">Speichern</button>
                    <button type="button" class="btn btn-ghost btn-xs b-cancel" onclick="rowCancel('s',<?= $sid ?>)">Abbruch</button>
                    <form method="POST" action="?page=finanzen" style="display:inline" onsubmit="return confirm('Diesen Eintrag wirklich löschen?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="schuld_delete">
                        <input type="hidden" name="id" value="<?= $sid ?>">
                        <input type="hidden" name="person_filter" value="<?= he($person) ?>">
                        <button type="submit" class="btn btn-danger btn-xs">✕</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>

            <!-- NEUE ZEILE -->
            <tr>
                <td colspan="7" style="padding:4px 16px 0">
                    <span style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--green)">Neuer Datensatz</span>
                </td>
            </tr>
            <tr class="new-row">
                <td><input class="inline-input new-input" form="frm-s-new" name="glaeubiger" placeholder="Gläubiger" required></td>
                <td><input class="inline-input new-input" form="frm-s-new" name="startsumme" placeholder="0,00" style="max-width:90px"></td>
                <td><input class="inline-input new-input" form="frm-s-new" name="restsumme"  placeholder="0,00" style="max-width:90px"></td>
                <td><input class="inline-input new-input" form="frm-s-new" name="rate"       placeholder="0,00" style="max-width:90px"></td>
                <td></td>
                <td><input class="inline-input new-input" form="frm-s-new" name="notiz" placeholder="optional"></td>
                <td style="text-align:right">
                    <form id="frm-s-new" method="POST" action="?page=finanzen" style="display:none">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="schuld_save">
                        <input type="hidden" name="edit_id" value="0">
                        <input type="hidden" name="person_filter" value="<?= he($person) ?>">
                    </form>
                    <button type="button" class="btn btn-primary btn-xs" onclick="document.getElementById('frm-s-new').submit()">+ Hinzufügen</button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<script>
// Beim Laden: alle .fi (Field-Inputs) verstecken, .ft (Field-Text) zeigen
// Speichern/Abbruch-Buttons verstecken
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.fi').forEach(function(el) { el.style.display = 'none'; });
    document.querySelectorAll('.b-save, .b-cancel').forEach(function(el) { el.style.display = 'none'; });
});

function rowEdit(type, id) {
    var row = document.getElementById('row-' + type + '-' + id);
    row.querySelectorAll('.ft').forEach(function(el) { el.style.display = 'none'; });
    row.querySelectorAll('.fi').forEach(function(el) { el.style.display = ''; });
    row.querySelector('.b-edit').style.display   = 'none';
    row.querySelector('.b-save').style.display   = '';
    row.querySelector('.b-cancel').style.display = '';
    row.style.background = 'var(--green-light)';
    var first = row.querySelector('.fi.inline-input');
    if (first) first.focus();
}

function rowCancel(type, id) {
    var row = document.getElementById('row-' + type + '-' + id);
    row.querySelectorAll('.ft').forEach(function(el) { el.style.display = ''; });
    row.querySelectorAll('.fi').forEach(function(el) { el.style.display = 'none'; });
    row.querySelector('.b-edit').style.display   = '';
    row.querySelector('.b-save').style.display   = 'none';
    row.querySelector('.b-cancel').style.display = 'none';
    row.style.background = '';
}

function rowSave(type, id) {
    var frm = document.getElementById('frm-' + type + '-' + id);
    if (frm) frm.submit();
}
</script>