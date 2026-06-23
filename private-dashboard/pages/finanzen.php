<?php
// ════════════════════════════════════════════════
// finanzen.php – Einnahmen, Ausgaben, Schulden
// Tabs + Person-Filter + Bulk-Edit
// user_id Filter: alle Queries auf eingeloggten User beschränkt
// ════════════════════════════════════════════════
$db      = get_db();
$uid     = current_user_id();
$tab     = $_GET['tab'] ?? 'uebersicht';
$person  = $_GET['person'] ?? 'Marcel';
$errors  = [];
$success = '';

if (!in_array($person, ['Marcel','Kim','Beide'], true)) $person = 'Marcel';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';
    $pf  = $_POST['person_filter'] ?? $person;

    function to_monthly(float $bet, string $tur): float {
        if ($tur === 'Jährlich')        return round($bet / 12, 2);
        if ($tur === 'Vierteljährlich') return round($bet / 3, 2);
        if ($tur === 'Halbjährlich')    return round($bet / 6, 2);
        return $bet;
    }

    if ($act === 'einnahme_save') {
        $bez      = trim($_POST['bezeichnung'] ?? '');
        $per      = $_POST['person'] ?? 'Marcel';
        $kat      = trim($_POST['kategorie'] ?? '');
        $tur      = $_POST['turnus'] ?? 'Monatlich';
        $bet_orig = (float)parse_betrag($_POST['betrag'] ?? '0');
        $bet      = to_monthly($bet_orig, $tur);
        if ($bez === '') $errors[] = 'Bezeichnung fehlt.';
        if (empty($errors)) {
            $id = (int)($_POST['edit_id'] ?? 0);
            if ($id > 0) {
                $db->prepare('UPDATE einnahmen SET bezeichnung=?,betrag=?,betrag_original=?,person=?,kategorie=?,turnus=? WHERE id=? AND user_id=?')->execute([$bez,$bet,$bet_orig,$per,$kat,$tur,$id,$uid]);
            } else {
                $db->prepare('INSERT INTO einnahmen (user_id,bezeichnung,betrag,betrag_original,person,kategorie,turnus) VALUES (?,?,?,?,?,?,?)')->execute([$uid,$bez,$bet,$bet_orig,$per,$kat,$tur]);
            }
            header("Location: ?page=finanzen&tab=einnahmen&person=$pf&msg=saved"); exit;
        }
        $tab = 'einnahmen';
    }

    if ($act === 'einnahmen_bulk_save') {
        foreach ($_POST['ids'] ?? [] as $id) {
            $id       = (int)$id;
            $row      = $_POST['rows'][$id] ?? [];
            $bez      = trim($row['bezeichnung'] ?? '');
            $per      = $row['person'] ?? 'Marcel';
            $kat      = trim($row['kategorie'] ?? '');
            $tur      = $row['turnus'] ?? 'Monatlich';
            $bet_orig = (float)parse_betrag($row['betrag'] ?? '0');
            $bet      = to_monthly($bet_orig, $tur);
            if ($bez === '') continue;
            $db->prepare('UPDATE einnahmen SET bezeichnung=?,betrag=?,betrag_original=?,person=?,kategorie=?,turnus=? WHERE id=? AND user_id=?')->execute([$bez,$bet,$bet_orig,$per,$kat,$tur,$id,$uid]);
        }
        header("Location: ?page=finanzen&tab=einnahmen&person=$pf&msg=saved"); exit;
    }

    if ($act === 'einnahme_delete') {
        $db->prepare('DELETE FROM einnahmen WHERE id=? AND user_id=?')->execute([(int)$_POST['id'],$uid]);
        header("Location: ?page=finanzen&tab=einnahmen&person=$pf&msg=saved"); exit;
    }

    if ($act === 'ausgabe_save') {
        $bez      = trim($_POST['bezeichnung'] ?? '');
        $per      = $_POST['person'] ?? 'Marcel';
        $kat      = trim($_POST['kategorie'] ?? '');
        $tur      = $_POST['turnus'] ?? 'Monatlich';
        $bet_orig = (float)parse_betrag($_POST['betrag'] ?? '0');
        $bet      = to_monthly($bet_orig, $tur);
        if ($bez === '') $errors[] = 'Bezeichnung fehlt.';
        if (empty($errors)) {
            $id = (int)($_POST['edit_id'] ?? 0);
            if ($id > 0) {
                $db->prepare('UPDATE ausgaben SET bezeichnung=?,betrag=?,betrag_original=?,person=?,kategorie=?,turnus=? WHERE id=? AND user_id=?')->execute([$bez,$bet,$bet_orig,$per,$kat,$tur,$id,$uid]);
            } else {
                $db->prepare('INSERT INTO ausgaben (user_id,bezeichnung,betrag,betrag_original,person,kategorie,turnus) VALUES (?,?,?,?,?,?,?)')->execute([$uid,$bez,$bet,$bet_orig,$per,$kat,$tur]);
            }
            header("Location: ?page=finanzen&tab=ausgaben&person=$pf&msg=saved"); exit;
        }
        $tab = 'ausgaben';
    }

    if ($act === 'ausgaben_bulk_save') {
        foreach ($_POST['ids'] ?? [] as $id) {
            $id       = (int)$id;
            $row      = $_POST['rows'][$id] ?? [];
            $bez      = trim($row['bezeichnung'] ?? '');
            $per      = $row['person'] ?? 'Marcel';
            $kat      = trim($row['kategorie'] ?? '');
            $tur      = $row['turnus'] ?? 'Monatlich';
            $bet_orig = (float)parse_betrag($row['betrag'] ?? '0');
            $bet      = to_monthly($bet_orig, $tur);
            if ($bez === '') continue;
            $db->prepare('UPDATE ausgaben SET bezeichnung=?,betrag=?,betrag_original=?,person=?,kategorie=?,turnus=? WHERE id=? AND user_id=?')->execute([$bez,$bet,$bet_orig,$per,$kat,$tur,$id,$uid]);
        }
        header("Location: ?page=finanzen&tab=ausgaben&person=$pf&msg=saved"); exit;
    }

    if ($act === 'ausgabe_delete') {
        $db->prepare('DELETE FROM ausgaben WHERE id=? AND user_id=?')->execute([(int)$_POST['id'],$uid]);
        header("Location: ?page=finanzen&tab=ausgaben&person=$pf&msg=saved"); exit;
    }

    if ($act === 'schuld_save') {
        $gl  = trim($_POST['glaeubiger'] ?? '');
        $ss  = parse_betrag($_POST['startsumme'] ?? '0');
        $rs  = parse_betrag($_POST['restsumme']  ?? '0');
        $rt  = parse_betrag($_POST['rate']        ?? '0');
        $no  = trim($_POST['notiz'] ?? '');
        $per = $_POST['person'] ?? 'Marcel';
        if ($gl === '') $errors[] = 'Gläubiger fehlt.';
        if (empty($errors)) {
            $id = (int)($_POST['edit_id'] ?? 0);
            if ($id > 0) {
                $db->prepare('UPDATE verbindlichkeiten SET glaeubiger=?,startsumme=?,restsumme=?,rate=?,notiz=?,person=? WHERE id=? AND user_id=?')->execute([$gl,$ss,$rs,$rt,$no,$per,$id,$uid]);
            } else {
                $db->prepare('INSERT INTO verbindlichkeiten (user_id,glaeubiger,startsumme,restsumme,rate,notiz,person) VALUES (?,?,?,?,?,?,?)')->execute([$uid,$gl,$ss,$rs,$rt,$no,$per]);
            }
            header("Location: ?page=finanzen&tab=schulden&person=$pf&msg=saved"); exit;
        }
        $tab = 'schulden';
    }

    if ($act === 'schulden_bulk_save') {
        foreach ($_POST['ids'] ?? [] as $id) {
            $id  = (int)$id;
            $row = $_POST['rows'][$id] ?? [];
            $gl  = trim($row['glaeubiger'] ?? '');
            $ss  = parse_betrag($row['startsumme'] ?? '0');
            $rs  = parse_betrag($row['restsumme']  ?? '0');
            $rt  = parse_betrag($row['rate']        ?? '0');
            $no  = trim($row['notiz'] ?? '');
            $per = $row['person'] ?? 'Marcel';
            if ($gl === '') continue;
            $db->prepare('UPDATE verbindlichkeiten SET glaeubiger=?,startsumme=?,restsumme=?,rate=?,notiz=?,person=? WHERE id=? AND user_id=?')->execute([$gl,$ss,$rs,$rt,$no,$per,$id,$uid]);
        }
        header("Location: ?page=finanzen&tab=schulden&person=$pf&msg=saved"); exit;
    }

    if ($act === 'schuld_delete') {
        $db->prepare('DELETE FROM verbindlichkeiten WHERE id=? AND user_id=?')->execute([(int)$_POST['id'],$uid]);
        header("Location: ?page=finanzen&tab=schulden&person=$pf&msg=saved"); exit;
    }

    if ($act === 'reorder') {
        $table   = $_POST['table'] ?? '';
        $id      = (int)($_POST['id'] ?? 0);
        $dir     = $_POST['dir'] ?? '';
        $allowed_tables = ['einnahmen','ausgaben','verbindlichkeiten'];
        if (in_array($table, $allowed_tables, true) && $id > 0 && in_array($dir, ['up','down'], true)) {
            $cur = $db->prepare("SELECT position FROM `$table` WHERE id=? AND user_id=?");
            $cur->execute([$id, $uid]);
            $curPos = (int)($cur->fetchColumn() ?? 0);
            if ($dir === 'up') {
                $nb = $db->prepare("SELECT id, position FROM `$table` WHERE position < ? AND user_id=? ORDER BY position DESC LIMIT 1");
            } else {
                $nb = $db->prepare("SELECT id, position FROM `$table` WHERE position > ? AND user_id=? ORDER BY position ASC LIMIT 1");
            }
            $nb->execute([$curPos, $uid]);
            $neighbor = $nb->fetch();
            if ($neighbor) {
                $db->prepare("UPDATE `$table` SET position=? WHERE id=? AND user_id=?")->execute([$neighbor['position'], $id, $uid]);
                $db->prepare("UPDATE `$table` SET position=? WHERE id=? AND user_id=?")->execute([$curPos, $neighbor['id'], $uid]);
            }
        }
        $redirect = $_POST['redirect'] ?? '?page=finanzen';
        header("Location: $redirect"); exit;
    }
}

if (defined('HANDLE_POST_ONLY')) return;

$msgs = ['saved' => 'Gespeichert.'];
if (isset($_GET['msg'], $msgs[$_GET['msg']])) $success = $msgs[$_GET['msg']];

function get_rows(PDO $db, string $table, string $person, int $uid): array {
    if ($person === 'Beide') {
        $s = $db->prepare("SELECT * FROM $table WHERE user_id=? ORDER BY position, id");
        $s->execute([$uid]);
        return $s->fetchAll();
    }
    $s = $db->prepare("SELECT * FROM $table WHERE user_id=? AND (person=? OR person='Beide') ORDER BY position, id");
    $s->execute([$uid, $person]);
    return $s->fetchAll();
}

$einnahmen_alle = get_rows($db, 'einnahmen', $person, $uid);
$ausgaben_alle  = get_rows($db, 'ausgaben',  $person, $uid);
$schulden_alle  = get_rows($db, 'verbindlichkeiten', $person, $uid);

function sum_active(array $rows): float {
    $sum=0; foreach($rows as $r) $sum+=$r["aktiv"]?(float)$r["betrag"]:0; return $sum;
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
function reorder_btns(string $table, int $id, string $tab, string $person): string {
    $csrf = csrf_field();
    $redirect = he('?page=finanzen&tab='.$tab.'&person='.$person);
    return '
    <form method="POST" action="?page=finanzen" class="form-inline">'.$csrf.'
        <input type="hidden" name="act" value="reorder">
        <input type="hidden" name="table" value="'.he($table).'">
        <input type="hidden" name="id" value="'.$id.'">
        <input type="hidden" name="redirect" value="'.$redirect.'">
        <input type="hidden" name="dir" value="up">
        <button type="submit" class="btn-sort" title="Nach oben">▲</button>
    </form>
    <form method="POST" action="?page=finanzen" class="form-inline">'.$csrf.'
        <input type="hidden" name="act" value="reorder">
        <input type="hidden" name="table" value="'.he($table).'">
        <input type="hidden" name="id" value="'.$id.'">
        <input type="hidden" name="redirect" value="'.$redirect.'">
        <input type="hidden" name="dir" value="down">
        <button type="submit" class="btn-sort" title="Nach unten">▼</button>
    </form>';
}

// ── Person-Switcher aus User-Profilen laden ──
$person_options = get_person_options();
$def_person     = $person === 'Beide' || $person === 'Alle' ? ($person_options[0] ?? 'Marcel') : $person;

$turnusse      = ['Monatlich','Vierteljährlich','Halbjährlich','Jährlich','Einmalig'];
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
        <?php foreach ($person_options as $p): ?>
        <a href="?page=finanzen&tab=<?= $tab ?>&person=<?= $p ?>" class="person-btn <?= $person===$p?'active':'' ?>"><?= $p ?></a>
        <?php endforeach; ?>
    </div>
</div>

<?php
// person_is_all() prüft ob "Beide" oder "Alle" gewählt
$is_all = person_is_all($person);
?>

<?php if ($tab === 'uebersicht'): ?>
<!-- ════ ÜBERSICHT ════ -->

<div class="kpi-grid kpi-grid--4 mt-4">
    <div class="kpi-card">
        <div class="kpi-label">📥 Einnahmen<?= !$is_all?' '.$person:'' ?></div>
        <div class="kpi-value kpi-value--md text-green"><?= fmt2($ein_gesamt) ?></div>
        <?php if ($is_all): ?><div class="kpi-sub"><?= $person_options[0] ?? '' ?> <?= fmt2($ein_marcel) ?> · <?= $person_options[1] ?? '' ?> <?= fmt2($ein_kim) ?></div><?php endif; ?>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">📤 Ausgaben<?= !$is_all?' '.$person:'' ?></div>
        <div class="kpi-value kpi-value--md text-red"><?= fmt2($aus_gesamt) ?></div>
        <?php if ($is_all): ?><div class="kpi-sub"><?= $person_options[0] ?? '' ?> <?= fmt2($aus_marcel) ?> · <?= $person_options[1] ?? '' ?> <?= fmt2($aus_kim) ?></div><?php endif; ?>
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

<?php if ($is_all): ?>
<div class="dashboard-row mt-4">
    <div class="card">
        <div class="card-head"><h2 class="card-title">👤 <?= he($person_options[0] ?? 'Profil 1') ?></h2></div>
        <div class="split-pad">
            <div class="finance-split-row"><span>Einnahmen</span><span class="text-green fw-700"><?= fmt2($ein_marcel) ?></span></div>
            <div class="finance-split-row"><span>Ausgaben</span><span class="text-red fw-700"><?= fmt2($aus_marcel) ?></span></div>
            <div class="finance-split-row finance-split-total"><span>Überschuss</span><span class="<?= ($ein_marcel-$aus_marcel)>=0?'text-green':'text-red' ?> fw-700"><?= fmt2($ein_marcel-$aus_marcel,true) ?></span></div>
        </div>
    </div>
    <div class="card">
        <div class="card-head"><h2 class="card-title">👤 <?= he($person_options[1] ?? 'Profil 2') ?></h2></div>
        <div class="split-pad">
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
            <thead><tr><th>Bezeichnung</th><?php if($is_all): ?><th>Person</th><?php endif; ?><th>Kategorie</th><th class="col-right">Betrag</th><th class="col-right">Monatlich</th></tr></thead>
            <tbody>
            <?php foreach ($einnahmen_alle as $e): if (!$e['aktiv']) continue; ?>
            <tr>
                <td><?= he($e['bezeichnung']) ?></td>
                <?php if($is_all): ?><td><?= he($e['person']) ?></td><?php endif; ?>
                <td><span class="badge badge-neutral"><?= he($e['kategorie']??'–') ?></span></td>
                <td class="col-right fw-700 text-green"><?= fmt2((float)$e['betrag_original']) ?> <span class="text-muted" style="font-size:0.8em"><?= $e['turnus']!=='Monatlich'?he($e['turnus']):'' ?></span></td>
                <td class="col-right text-muted"><?= $e['turnus']!=='Monatlich'?fmt2((float)$e['betrag']).'/Mon.':'' ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="row-total">
                <td colspan="<?= $is_all?4:3 ?>" class="fw-700">Gesamt</td>
                <td class="col-right fw-700 text-green"><?= fmt2($ein_gesamt) ?>/Mon.</td>
            </tr>
            </tbody>
        </table></div>
    </div>
    <div class="card">
        <div class="card-head"><h2 class="card-title">Ausgaben im Detail</h2><span class="badge badge-danger"><?= fmt2($aus_gesamt) ?>/Mon.</span></div>
        <div class="table-wrap"><table class="data-table">
            <thead><tr><th>Bezeichnung</th><?php if($is_all): ?><th>Person</th><?php endif; ?><th>Kategorie</th><th class="col-right">Betrag</th><th class="col-right">Monatlich</th></tr></thead>
            <tbody>
            <?php foreach ($ausgaben_alle as $a): if (!$a['aktiv']) continue; ?>
            <tr>
                <td><?= he($a['bezeichnung']) ?></td>
                <?php if($is_all): ?><td><?= he($a['person']) ?></td><?php endif; ?>
                <td><span class="badge badge-neutral"><?= he($a['kategorie']??'–') ?></span></td>
                <td class="col-right fw-700 text-red"><?= fmt2((float)$a['betrag_original']) ?> <span class="text-muted" style="font-size:0.8em"><?= $a['turnus']!=='Monatlich'?he($a['turnus']):'' ?></span></td>
                <td class="col-right text-muted"><?= $a['turnus']!=='Monatlich'?fmt2((float)$a['betrag']).'/Mon.':'' ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="row-total">
                <td colspan="<?= $is_all?4:3 ?>" class="fw-700">Gesamt</td>
                <td class="col-right fw-700 text-red"><?= fmt2($aus_gesamt) ?>/Mon.</td>
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
        <thead><tr><th>Kategorie</th><th>Anteil</th><th class="col-right">Betrag/Mon.</th></tr></thead>
        <tbody>
        <?php foreach ($kat_summen as $kat => $sum):
            $anteil = $kat_total > 0 ? $sum / $kat_total : 0;
            $pct    = number_format($anteil*100, 1, '.', '');
        ?>
        <tr>
            <td><?= he($kat) ?></td>
            <td>
                <div class="bar-wrap">
                    <div class="bar-track"><div class="bar-fill" data-width="<?= $pct ?>"></div></div>
                    <span class="bar-label"><?= number_format($anteil*100,0) ?>%</span>
                </div>
            </td>
            <td class="col-right fw-700"><?= fmt2($sum) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>

<?php elseif ($tab === 'einnahmen'): ?>
<!-- ════ EINNAHMEN ════ -->

<div class="card mt-4" id="card-einnahmen">
    <div class="card-head">
        <div class="card-head-left">
            <h2 class="card-title">Einnahmen<?= !$is_all?' – '.$person:'' ?></h2>
            <span class="card-sum text-green"><?= fmt2($ein_gesamt) ?>/Mon.</span>
        </div>
        <div class="bulk-bar">
            <button type="button" class="btn btn-ghost btn-sm" id="btn-edit-e">✏ Bearbeiten</button>
            <button type="submit" form="frm-e-bulk" class="btn btn-primary btn-sm btn-hidden" id="btn-save-e">✓ Speichern</button>
        </div>
    </div>
    <form id="frm-e-bulk" method="POST" action="?page=finanzen">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="einnahmen_bulk_save">
        <input type="hidden" name="person_filter" value="<?= he($person) ?>">
        <?php foreach ($einnahmen_alle as $e): ?>
        <input type="hidden" name="ids[]" value="<?= $e['id'] ?>">
        <?php endforeach; ?>
    </form>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr>
                <th class="col-sort"></th>
                <th>Bezeichnung</th>
                <?php if($is_all): ?><th>Person</th><?php endif; ?>
                <th>Kategorie</th><th>Turnus</th>
                <th class="col-right">Betrag</th>
                <th class="col-right">Monatlich</th>
                <th></th>
            </tr></thead>
            <tbody>
            <?php foreach ($einnahmen_alle as $e): $eid = $e['id']; ?>
            <tr>
                <td class="col-sort"><?= reorder_btns('einnahmen', $eid, 'einnahmen', $person) ?></td>
                <td>
                    <span class="ft-bulk"><?= he($e['bezeichnung']) ?></span>
                    <input class="inline-input fi-bulk" form="frm-e-bulk" name="rows[<?= $eid ?>][bezeichnung]" value="<?= he($e['bezeichnung']) ?>" required>
                </td>
                <?php if($is_all): ?>
                <td>
                    <span class="ft-bulk"><?= he($e['person']) ?></span>
                    <select class="inline-input fi-bulk" form="frm-e-bulk" name="rows[<?= $eid ?>][person]"><?= sel($person_options,$e['person']) ?></select>
                </td>
                <?php endif; ?>
                <td>
                    <span class="ft-bulk"><span class="badge badge-neutral"><?= he($e['kategorie']??'–') ?></span></span>
                    <select class="inline-input fi-bulk" form="frm-e-bulk" name="rows[<?= $eid ?>][kategorie]"><?= kat_sel($kat_einnahmen,$e['kategorie']??'') ?></select>
                </td>
                <td>
                    <span class="ft-bulk"><?= he($e['turnus']) ?></span>
                    <select class="inline-input fi-bulk" form="frm-e-bulk" name="rows[<?= $eid ?>][turnus]"><?= sel($turnusse,$e['turnus']) ?></select>
                </td>
                <td class="col-right">
                    <span class="ft-bulk fw-700 text-green"><?= fmt2((float)$e['betrag_original']) ?></span>
                    <input class="inline-input fi-bulk input-right input-narrow" form="frm-e-bulk" name="rows[<?= $eid ?>][betrag]" value="<?= he(number_format((float)$e['betrag_original'],2,',','.')) ?>">
                </td>
                <td class="col-right">
                    <span class="ft-bulk text-muted"><?= $e['turnus']!=='Monatlich'?fmt2((float)$e['betrag']).'/Mon.':'' ?></span>
                </td>
                <td class="col-actions">
                    <form id="frm-e-del-<?= $eid ?>" method="POST" action="?page=finanzen" hidden>
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="einnahme_delete">
                        <input type="hidden" name="id" value="<?= $eid ?>">
                        <input type="hidden" name="person_filter" value="<?= he($person) ?>">
                    </form>
                    <button type="submit" form="frm-e-del-<?= $eid ?>" class="btn btn-danger btn-xs fi-bulk btn-delete-confirm">✕</button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Neue Einnahme Card -->
<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Neue Einnahme</h2></div>
    <form id="frm-e-new" method="POST" action="?page=finanzen">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="einnahme_save">
        <input type="hidden" name="edit_id" value="0">
        <input type="hidden" name="person_filter" value="<?= he($person) ?>">
        <?php if(!$is_all): ?><input type="hidden" name="person" value="<?= he($def_person) ?>"><?php endif; ?>
    </form>
    <div class="table-wrap"><table class="data-table">
        <thead><tr>
            <th>Bezeichnung</th>
            <?php if($is_all): ?><th>Person</th><?php endif; ?>
            <th>Kategorie</th><th>Turnus</th><th>Betrag</th><th></th>
        </tr></thead>
        <tbody><tr>
            <td><input class="inline-input new-input" form="frm-e-new" name="bezeichnung" placeholder="Bezeichnung" required></td>
            <?php if($is_all): ?>
            <td><select class="inline-input new-input" form="frm-e-new" name="person"><?= sel($person_options,$def_person) ?></select></td>
            <?php endif; ?>
            <td><select class="inline-input new-input" form="frm-e-new" name="kategorie"><?= kat_sel($kat_einnahmen,'') ?></select></td>
            <td><select class="inline-input new-input" form="frm-e-new" name="turnus"><?= sel($turnusse,'Monatlich') ?></select></td>
            <td><input class="inline-input new-input input-right input-narrow" form="frm-e-new" name="betrag" placeholder="0,00"></td>
            <td class="col-actions"><button type="button" class="btn btn-primary btn-xs" id="btn-new-e">+ Hinzufügen</button></td>
        </tr></tbody>
    </table></div>
</div>

<?php elseif ($tab === 'ausgaben'): ?>
<!-- ════ AUSGABEN ════ -->

<div class="card mt-4" id="card-ausgaben">
    <div class="card-head">
        <div class="card-head-left">
            <h2 class="card-title">Ausgaben<?= !$is_all?' – '.$person:'' ?></h2>
            <span class="card-sum text-red"><?= fmt2($aus_gesamt) ?>/Mon.</span>
        </div>
        <div class="bulk-bar">
            <button type="button" class="btn btn-ghost btn-sm" id="btn-edit-a">✏ Bearbeiten</button>
            <button type="submit" form="frm-a-bulk" class="btn btn-primary btn-sm btn-hidden" id="btn-save-a">✓ Speichern</button>
        </div>
    </div>
    <form id="frm-a-bulk" method="POST" action="?page=finanzen">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="ausgaben_bulk_save">
        <input type="hidden" name="person_filter" value="<?= he($person) ?>">
        <?php foreach ($ausgaben_alle as $a): ?>
        <input type="hidden" name="ids[]" value="<?= $a['id'] ?>">
        <?php endforeach; ?>
    </form>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr>
                <th class="col-sort"></th>
                <th>Bezeichnung</th>
                <?php if($is_all): ?><th>Person</th><?php endif; ?>
                <th>Kategorie</th><th>Turnus</th>
                <th class="col-right">Betrag</th>
                <th class="col-right">Monatlich</th>
                <th></th>
            </tr></thead>
            <tbody>
            <?php foreach ($ausgaben_alle as $a): $aid = $a['id']; ?>
            <tr>
                <td class="col-sort"><?= reorder_btns('ausgaben', $aid, 'ausgaben', $person) ?></td>
                <td>
                    <span class="ft-bulk"><?= he($a['bezeichnung']) ?></span>
                    <input class="inline-input fi-bulk" form="frm-a-bulk" name="rows[<?= $aid ?>][bezeichnung]" value="<?= he($a['bezeichnung']) ?>" required>
                </td>
                <?php if($is_all): ?>
                <td>
                    <span class="ft-bulk"><?= he($a['person']) ?></span>
                    <select class="inline-input fi-bulk" form="frm-a-bulk" name="rows[<?= $aid ?>][person]"><?= sel($person_options,$a['person']) ?></select>
                </td>
                <?php endif; ?>
                <td>
                    <span class="ft-bulk"><span class="badge badge-neutral"><?= he($a['kategorie']??'–') ?></span></span>
                    <select class="inline-input fi-bulk" form="frm-a-bulk" name="rows[<?= $aid ?>][kategorie]"><?= kat_sel($kat_ausgaben,$a['kategorie']??'') ?></select>
                </td>
                <td>
                    <span class="ft-bulk"><?= he($a['turnus']) ?></span>
                    <select class="inline-input fi-bulk" form="frm-a-bulk" name="rows[<?= $aid ?>][turnus]"><?= sel($turnusse,$a['turnus']) ?></select>
                </td>
                <td class="col-right">
                    <span class="ft-bulk fw-700 text-red"><?= fmt2((float)$a['betrag_original']) ?></span>
                    <input class="inline-input fi-bulk input-right input-narrow" form="frm-a-bulk" name="rows[<?= $aid ?>][betrag]" value="<?= he(number_format((float)$a['betrag_original'],2,',','.')) ?>">
                </td>
                <td class="col-right">
                    <span class="ft-bulk text-muted"><?= $a['turnus']!=='Monatlich'?fmt2((float)$a['betrag']).'/Mon.':'' ?></span>
                </td>
                <td class="col-actions">
                    <form id="frm-a-del-<?= $aid ?>" method="POST" action="?page=finanzen" hidden>
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="ausgabe_delete">
                        <input type="hidden" name="id" value="<?= $aid ?>">
                        <input type="hidden" name="person_filter" value="<?= he($person) ?>">
                    </form>
                    <button type="submit" form="frm-a-del-<?= $aid ?>" class="btn btn-danger btn-xs fi-bulk btn-delete-confirm">✕</button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Neue Ausgabe Card -->
<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Neue Ausgabe</h2></div>
    <form id="frm-a-new" method="POST" action="?page=finanzen">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="ausgabe_save">
        <input type="hidden" name="edit_id" value="0">
        <input type="hidden" name="person_filter" value="<?= he($person) ?>">
        <?php if(!$is_all): ?><input type="hidden" name="person" value="<?= he($def_person) ?>"><?php endif; ?>
    </form>
    <div class="table-wrap"><table class="data-table">
        <thead><tr>
            <th>Bezeichnung</th>
            <?php if($is_all): ?><th>Person</th><?php endif; ?>
            <th>Kategorie</th><th>Turnus</th><th>Betrag</th><th></th>
        </tr></thead>
        <tbody><tr>
            <td><input class="inline-input new-input" form="frm-a-new" name="bezeichnung" placeholder="Bezeichnung" required></td>
            <?php if($is_all): ?>
            <td><select class="inline-input new-input" form="frm-a-new" name="person"><?= sel($person_options,$def_person) ?></select></td>
            <?php endif; ?>
            <td><select class="inline-input new-input" form="frm-a-new" name="kategorie"><?= kat_sel($kat_ausgaben,'') ?></select></td>
            <td><select class="inline-input new-input" form="frm-a-new" name="turnus"><?= sel($turnusse,'Monatlich') ?></select></td>
            <td><input class="inline-input new-input input-right input-narrow" form="frm-a-new" name="betrag" placeholder="0,00"></td>
            <td class="col-actions"><button type="button" class="btn btn-primary btn-xs" id="btn-new-a">+ Hinzufügen</button></td>
        </tr></tbody>
    </table></div>
</div>

<?php elseif ($tab === 'schulden'): ?>
<!-- ════ SCHULDEN ════ -->

<div class="card mt-4" id="card-schulden">
    <div class="card-head">
        <div class="card-head-left">
            <h2 class="card-title">Verbindlichkeiten<?= !$is_all?' – '.$person:'' ?></h2>
            <span class="card-sum text-red"><?= fmt2($schulden_gesamt) ?></span>
        </div>
        <div class="bulk-bar">
            <button type="button" class="btn btn-ghost btn-sm" id="btn-edit-s">✏ Bearbeiten</button>
            <button type="submit" form="frm-s-bulk" class="btn btn-primary btn-sm btn-hidden" id="btn-save-s">✓ Speichern</button>
        </div>
    </div>
    <form id="frm-s-bulk" method="POST" action="?page=finanzen">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="schulden_bulk_save">
        <input type="hidden" name="person_filter" value="<?= he($person) ?>">
        <?php foreach ($schulden_alle as $s): ?>
        <input type="hidden" name="ids[]" value="<?= $s['id'] ?>">
        <?php endforeach; ?>
    </form>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr>
                <th class="col-sort"></th>
                <th>Gläubiger</th>
                <?php if($is_all): ?><th>Person</th><?php endif; ?>
                <th>Startsumme</th><th>Restsumme</th>
                <th>Rate/Mon.</th><th>Abbezahlt</th><th>Notiz</th>
                <th></th>
            </tr></thead>
            <tbody>
            <?php foreach ($schulden_alle as $s): $sid = $s['id'];
                $abgezahlt = (float)$s['startsumme'] > 0
                    ? max(0, min(1, 1 - (float)$s['restsumme'] / (float)$s['startsumme']))
                    : 0;
            ?>
            <tr>
                <td class="col-sort"><?= reorder_btns('verbindlichkeiten', $sid, 'schulden', $person) ?></td>
                <td>
                    <span class="ft-bulk"><?= he($s['glaeubiger']) ?></span>
                    <input class="inline-input fi-bulk" form="frm-s-bulk" name="rows[<?= $sid ?>][glaeubiger]" value="<?= he($s['glaeubiger']) ?>" required>
                </td>
                <?php if($is_all): ?>
                <td>
                    <span class="ft-bulk"><?= he($s['person']) ?></span>
                    <select class="inline-input fi-bulk" form="frm-s-bulk" name="rows[<?= $sid ?>][person]"><?= sel($person_options,$s['person']) ?></select>
                </td>
                <?php endif; ?>
                <td>
                    <span class="ft-bulk"><?= fmt2((float)$s['startsumme']) ?></span>
                    <input class="inline-input fi-bulk input-narrow" form="frm-s-bulk" name="rows[<?= $sid ?>][startsumme]" value="<?= he(number_format((float)$s['startsumme'],2,',','.')) ?>">
                </td>
                <td>
                    <span class="ft-bulk fw-700 text-red"><?= fmt2((float)$s['restsumme']) ?></span>
                    <input class="inline-input fi-bulk input-narrow" form="frm-s-bulk" name="rows[<?= $sid ?>][restsumme]" value="<?= he(number_format((float)$s['restsumme'],2,',','.')) ?>">
                </td>
                <td>
                    <span class="ft-bulk"><?= $s['rate']>0?fmt2((float)$s['rate']):'–' ?></span>
                    <input class="inline-input fi-bulk input-narrow" form="frm-s-bulk" name="rows[<?= $sid ?>][rate]" value="<?= he(number_format((float)$s['rate'],2,',','.')) ?>">
                </td>
                <td>
                    <div class="progress-wrap">
                        <div class="progress-track"><div class="progress-fill" data-width="<?= number_format($abgezahlt*100,0) ?>"></div></div>
                        <span class="progress-label"><?= number_format($abgezahlt*100,0) ?>%</span>
                    </div>
                </td>
                <td>
                    <span class="ft-bulk"><?= he($s['notiz']??'–') ?></span>
                    <input class="inline-input fi-bulk" form="frm-s-bulk" name="rows[<?= $sid ?>][notiz]" value="<?= he($s['notiz']??'') ?>">
                </td>
                <td class="col-actions">
                    <form id="frm-s-del-<?= $sid ?>" method="POST" action="?page=finanzen" hidden>
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="schuld_delete">
                        <input type="hidden" name="id" value="<?= $sid ?>">
                        <input type="hidden" name="person_filter" value="<?= he($person) ?>">
                    </form>
                    <button type="submit" form="frm-s-del-<?= $sid ?>" class="btn btn-danger btn-xs fi-bulk btn-delete-confirm">✕</button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Neue Verbindlichkeit Card -->
<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Neue Verbindlichkeit</h2></div>
    <form id="frm-s-new" method="POST" action="?page=finanzen">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="schuld_save">
        <input type="hidden" name="edit_id" value="0">
        <input type="hidden" name="person_filter" value="<?= he($person) ?>">
        <?php if(!$is_all): ?><input type="hidden" name="person" value="<?= he($def_person) ?>"><?php endif; ?>
    </form>
    <div class="table-wrap"><table class="data-table">
        <thead><tr>
            <th>Gläubiger</th>
            <?php if($is_all): ?><th>Person</th><?php endif; ?>
            <th>Startsumme</th><th>Restsumme</th><th>Rate/Mon.</th><th>Notiz</th><th></th>
        </tr></thead>
        <tbody><tr>
            <td><input class="inline-input new-input" form="frm-s-new" name="glaeubiger" placeholder="Gläubiger" required></td>
            <?php if($is_all): ?>
            <td><select class="inline-input new-input" form="frm-s-new" name="person"><?= sel($person_options,$def_person) ?></select></td>
            <?php endif; ?>
            <td><input class="inline-input new-input input-narrow" form="frm-s-new" name="startsumme" placeholder="0,00"></td>
            <td><input class="inline-input new-input input-narrow" form="frm-s-new" name="restsumme" placeholder="0,00"></td>
            <td><input class="inline-input new-input input-narrow" form="frm-s-new" name="rate" placeholder="0,00"></td>
            <td><input class="inline-input new-input" form="frm-s-new" name="notiz" placeholder="Notiz"></td>
            <td class="col-actions"><button type="button" class="btn btn-primary btn-xs" id="btn-new-s">+ Hinzufügen</button></td>
        </tr></tbody>
    </table></div>
</div>

<?php endif; ?>