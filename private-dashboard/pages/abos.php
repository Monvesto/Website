<?php
// ════════════════════════════════════════════════
// abos.php – Abo-Verwaltung
// Übersicht und Verwaltung aller aktiven Abonnements
// user_id Filter + Person-Optionen aus Profilen
// ════════════════════════════════════════════════

$db     = get_db();
$uid    = current_user_id();
$person_options = get_person_options();
$person = $_GET['person'] ?? ($person_options[0] ?? 'Marcel');
if (!in_array($person, $person_options, true)) $person = $person_options[0] ?? 'Marcel';
$is_all     = person_is_all($person);
$def_person = $is_all ? ($person_options[0] ?? 'Marcel') : $person;

// ── Automatische Migration ──
(function() use ($db) {
    try {
        $exists = $db->query("SHOW TABLES LIKE 'abos'")->rowCount() > 0;
        if (!$exists) {
            $db->exec("CREATE TABLE abos (
                id               INT AUTO_INCREMENT PRIMARY KEY,
                user_id          INT NOT NULL DEFAULT 0,
                name             VARCHAR(100) NOT NULL,
                kategorie        VARCHAR(50)  NOT NULL DEFAULT '',
                betrag           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                turnus           ENUM('Monatlich','Vierteljährlich','Halbjährlich','Jährlich') NOT NULL DEFAULT 'Monatlich',
                zahlungsmethode  VARCHAR(50)  NOT NULL DEFAULT '',
                startdatum       DATE         DEFAULT NULL,
                naechste_abbuchung DATE       DEFAULT NULL,
                person           VARCHAR(20)  NOT NULL DEFAULT 'Beide',
                status           ENUM('Aktiv','Pausiert','Gekündigt') NOT NULL DEFAULT 'Aktiv',
                notiz            TEXT         DEFAULT NULL,
                aktiv            TINYINT(1)   NOT NULL DEFAULT 1,
                created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
            )");
        } else {
            // user_id nachrüsten falls fehlt
            $has = $db->query("SHOW COLUMNS FROM abos LIKE 'user_id'")->rowCount();
            if (!$has) $db->exec("ALTER TABLE abos ADD COLUMN user_id INT NOT NULL DEFAULT 0 AFTER id");
        }
    } catch (PDOException $e) { error_log('Migration abos: ' . $e->getMessage()); }
})();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';
    $pf  = $_POST['person_filter'] ?? $person;

    if ($act === 'save') {
        $name  = trim($_POST['name'] ?? '');
        $kat   = trim($_POST['kategorie'] ?? '');
        $bet   = parse_betrag($_POST['betrag'] ?? '0');
        $tur   = $_POST['turnus'] ?? 'Monatlich';
        $zm    = trim($_POST['zahlungsmethode'] ?? '');
        $sd    = $_POST['startdatum'] ?: null;
        $na    = $_POST['naechste_abbuchung'] ?: null;
        $per   = $_POST['person'] ?? $def_person;
        $sta   = $_POST['status'] ?? 'Aktiv';
        $not   = trim($_POST['notiz'] ?? '');
        $id    = (int)($_POST['edit_id'] ?? 0);
        if ($id > 0) {
            $db->prepare("UPDATE abos SET name=?,kategorie=?,betrag=?,turnus=?,zahlungsmethode=?,startdatum=?,naechste_abbuchung=?,person=?,status=?,notiz=? WHERE id=? AND user_id=?")
               ->execute([$name,$kat,$bet,$tur,$zm,$sd,$na,$per,$sta,$not,$id,$uid]);
        } else {
            $db->prepare("INSERT INTO abos (user_id,name,kategorie,betrag,turnus,zahlungsmethode,startdatum,naechste_abbuchung,person,status,notiz) VALUES (?,?,?,?,?,?,?,?,?,?,?)")
               ->execute([$uid,$name,$kat,$bet,$tur,$zm,$sd,$na,$per,$sta,$not]);
        }
        header("Location: ?page=abos&person=$pf&msg=saved"); exit;
    }

    if ($act === 'bulk_save') {
        foreach ($_POST['ids'] ?? [] as $id) {
            $id   = (int)$id;
            $row  = $_POST['rows'][$id] ?? [];
            $name = trim($row['name'] ?? '');
            if ($name === '') continue;
            $db->prepare("UPDATE abos SET name=?,kategorie=?,betrag=?,turnus=?,zahlungsmethode=?,naechste_abbuchung=?,person=?,status=?,notiz=? WHERE id=? AND user_id=?")
               ->execute([
                   $name, trim($row['kategorie']??''), parse_betrag($row['betrag']??'0'),
                   $row['turnus']??'Monatlich', trim($row['zahlungsmethode']??''),
                   $row['naechste_abbuchung']?:null, $row['person']??$def_person,
                   $row['status']??'Aktiv', trim($row['notiz']??''), $id, $uid
               ]);
        }
        header("Location: ?page=abos&person=$pf&msg=saved"); exit;
    }

    if ($act === 'delete') {
        $db->prepare("DELETE FROM abos WHERE id=? AND user_id=?")->execute([(int)$_POST['id'], $uid]);
        header("Location: ?page=abos&person=$pf&msg=saved"); exit;
    }
}
if (defined('HANDLE_POST_ONLY')) return;

function he_ab(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function fmt_ab(float $v): string { return number_format($v, 2, ',', '.') . ' €'; }
function to_monthly_ab(float $bet, string $tur): float {
    if ($tur === 'Jährlich')        return round($bet / 12, 2);
    if ($tur === 'Vierteljährlich') return round($bet / 3, 2);
    if ($tur === 'Halbjährlich')    return round($bet / 6, 2);
    return $bet;
}
function sel_ab(array $opts, string $cur): string {
    $out = '';
    foreach ($opts as $o) $out .= '<option value="'.he_ab($o).'"'.($o===$cur?' selected':'').'>'.he_ab($o).'</option>';
    return $out;
}

$kategorien = ['Streaming','Gaming','Musik','Software','Shopping','Finanzen','Gesundheit','News','Sonstiges'];
$turnusse   = ['Monatlich','Vierteljährlich','Halbjährlich','Jährlich'];
$zahlmeth   = ['Kreditkarte','PayPal','Lastschrift','Überweisung','Sonstiges'];
$statuslist  = ['Aktiv','Pausiert','Gekündigt'];

if ($is_all) {
    $s = $db->prepare("SELECT * FROM abos WHERE user_id=? ORDER BY status, kategorie, name");
    $s->execute([$uid]);
} else {
    $s = $db->prepare("SELECT * FROM abos WHERE user_id=? AND (person=? OR person='Beide') ORDER BY status, kategorie, name");
    $s->execute([$uid, $person]);
}
$abos = $s->fetchAll();

$monatlich_gesamt = 0; $jaehrlich_gesamt = 0; $aktiv_count = 0; $next_7_days = [];
foreach ($abos as $a) {
    if ($a['status'] !== 'Aktiv') continue;
    $aktiv_count++;
    $monatlich_gesamt += to_monthly_ab((float)$a['betrag'], $a['turnus']);
    $jaehrlich_gesamt += to_monthly_ab((float)$a['betrag'], $a['turnus']) * 12;
    if ($a['naechste_abbuchung']) {
        $diff = (strtotime($a['naechste_abbuchung']) - strtotime('today')) / 86400;
        if ($diff >= 0 && $diff <= 7) $next_7_days[] = $a;
    }
}

$kat_summen = [];
foreach ($abos as $a) {
    if ($a['status'] !== 'Aktiv') continue;
    $k = $a['kategorie'] ?: 'Sonstiges';
    $kat_summen[$k] = ($kat_summen[$k] ?? 0) + to_monthly_ab((float)$a['betrag'], $a['turnus']);
}
arsort($kat_summen);
?>

<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">Gespeichert.</div><?php endif; ?>

<div class="finance-topbar">
    <div class="tab-bar"></div>
    <div class="person-switcher">
        <?php foreach ($person_options as $p): ?>
        <a href="?page=abos&person=<?= $p ?>" class="person-btn <?= $person===$p?'active':'' ?>"><?= $p ?></a>
        <?php endforeach; ?>
    </div>
</div>

<div class="kpi-grid kpi-grid--4 mt-4">
    <div class="kpi-card kpi-card--info"><div class="kpi-label">Monatliche Kosten</div><div class="kpi-value kpi-value--md text-red"><?= fmt_ab($monatlich_gesamt) ?></div></div>
    <div class="kpi-card"><div class="kpi-label">Jährliche Kosten</div><div class="kpi-value kpi-value--md"><?= fmt_ab($jaehrlich_gesamt) ?></div></div>
    <div class="kpi-card"><div class="kpi-label">Aktive Abos</div><div class="kpi-value kpi-value--md"><?= $aktiv_count ?></div></div>
    <div class="kpi-card <?= count($next_7_days)>0?'kpi-card--alert':'' ?>"><div class="kpi-label">Fällig in 7 Tagen</div><div class="kpi-value kpi-value--md <?= count($next_7_days)>0?'text-red':'' ?>"><?= count($next_7_days) ?></div></div>
</div>

<?php if (!empty($next_7_days)): ?>
<div class="card mt-4">
    <div class="card-head card-head--amber"><h2 class="card-title">⏰ Bald fällig</h2><span class="badge badge-warning"><?= count($next_7_days) ?> in 7 Tagen</span></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Abo</th><th>Kategorie</th><th class="col-right">Betrag</th><th>Fällig am</th></tr></thead>
        <tbody>
        <?php foreach ($next_7_days as $a): ?>
        <tr><td><?= he_ab($a['name']) ?></td><td><span class="badge badge-neutral"><?= he_ab($a['kategorie']) ?></span></td><td class="col-right fw-700 text-red"><?= fmt_ab((float)$a['betrag']) ?></td><td><span class="date-soon"><?= date('d.m.Y',strtotime($a['naechste_abbuchung'])) ?></span></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>

<?php if (!empty($kat_summen)): ?>
<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Nach Kategorie</h2></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Kategorie</th><th>Anteil</th><th class="col-right">Monatlich</th></tr></thead>
        <tbody>
        <?php foreach ($kat_summen as $kat => $sum):
            $anteil = $monatlich_gesamt > 0 ? $sum / $monatlich_gesamt * 100 : 0;
        ?>
        <tr><td><?= he_ab($kat) ?></td>
        <td><div class="bar-wrap"><div class="bar-track"><div class="bar-fill" data-width="<?= number_format($anteil,1,'.','') ?>"></div></div><span class="bar-label"><?= number_format($anteil,0) ?>%</span></div></td>
        <td class="col-right fw-700"><?= fmt_ab($sum) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>

<form id="frm-ab-bulk" method="POST" action="?page=abos">
    <?= csrf_field() ?><input type="hidden" name="act" value="bulk_save"><input type="hidden" name="person_filter" value="<?= he_ab($person) ?>">
    <?php foreach ($abos as $a): ?><input type="hidden" name="ids[]" value="<?= $a['id'] ?>"><?php endforeach; ?>
</form>

<div class="card mt-4" id="card-abos">
    <div class="card-head">
        <div class="card-head-left"><h2 class="card-title">📦 Alle Abos<?= !$is_all?' – '.$person:'' ?></h2><span class="badge badge-neutral"><?= count($abos) ?> gesamt</span></div>
        <div class="bulk-bar">
            <button type="button" class="btn btn-ghost btn-sm" id="btn-edit-ab">✏ Bearbeiten</button>
            <button type="submit" form="frm-ab-bulk" class="btn btn-primary btn-sm btn-hidden" id="btn-save-ab">✓ Speichern</button>
        </div>
    </div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr>
            <th>Name</th><th>Kategorie</th>
            <?php if($is_all): ?><th>Person</th><?php endif; ?>
            <th>Turnus</th><th>Zahlungsart</th><th class="col-right">Betrag</th><th class="col-right">Monatlich</th><th>Nächste Abbuchung</th><th>Status</th><th></th>
        </tr></thead>
        <tbody>
        <?php foreach ($abos as $a): $aid = $a['id']; ?>
        <tr class="<?= $a['status']==='Gekündigt'?'row-done':'' ?>">
            <td>
                <span class="ft-bulk"><?= he_ab($a['name']) ?></span>
                <input class="inline-input fi-bulk" form="frm-ab-bulk" name="rows[<?= $aid ?>][name]" value="<?= he_ab($a['name']) ?>" required>
                <?php if ($a['notiz']): ?><div class="ft-bulk" style="font-size:11px;color:var(--text-muted)"><?= he_ab($a['notiz']) ?></div><?php endif; ?>
                <input class="inline-input fi-bulk" form="frm-ab-bulk" name="rows[<?= $aid ?>][notiz]" value="<?= he_ab($a['notiz']??'') ?>" placeholder="Notiz" style="margin-top:2px">
            </td>
            <td><span class="ft-bulk"><span class="badge badge-neutral"><?= he_ab($a['kategorie']??'–') ?></span></span><select class="inline-input fi-bulk" form="frm-ab-bulk" name="rows[<?= $aid ?>][kategorie]"><?= sel_ab($kategorien,$a['kategorie']??'') ?></select></td>
            <?php if($is_all): ?>
            <td><span class="ft-bulk"><?= he_ab($a['person']) ?></span><select class="inline-input fi-bulk" form="frm-ab-bulk" name="rows[<?= $aid ?>][person]"><?= sel_ab($person_options,$a['person']) ?></select></td>
            <?php else: ?>
            <input type="hidden" form="frm-ab-bulk" name="rows[<?= $aid ?>][person]" value="<?= he_ab($a['person']) ?>">
            <?php endif; ?>
            <td><span class="ft-bulk"><?= he_ab($a['turnus']) ?></span><select class="inline-input fi-bulk" form="frm-ab-bulk" name="rows[<?= $aid ?>][turnus]"><?= sel_ab($turnusse,$a['turnus']) ?></select></td>
            <td><span class="ft-bulk"><?= he_ab($a['zahlungsmethode']??'–') ?></span><select class="inline-input fi-bulk" form="frm-ab-bulk" name="rows[<?= $aid ?>][zahlungsmethode]"><?= sel_ab($zahlmeth,$a['zahlungsmethode']??'') ?></select></td>
            <td class="col-right"><span class="ft-bulk fw-700 text-red"><?= fmt_ab((float)$a['betrag']) ?></span><input class="inline-input fi-bulk input-right input-narrow" form="frm-ab-bulk" name="rows[<?= $aid ?>][betrag]" value="<?= he_ab(number_format((float)$a['betrag'],2,',','.')) ?>"></td>
            <td class="col-right text-muted"><?php $mon=to_monthly_ab((float)$a['betrag'],$a['turnus']); echo $a['turnus']!=='Monatlich'?fmt_ab($mon).'/Mon.':''; ?></td>
            <td><span class="ft-bulk"><?= $a['naechste_abbuchung']?date('d.m.Y',strtotime($a['naechste_abbuchung'])):'–' ?></span><input class="inline-input fi-bulk" type="date" form="frm-ab-bulk" name="rows[<?= $aid ?>][naechste_abbuchung]" value="<?= he_ab($a['naechste_abbuchung']??'') ?>"></td>
            <td>
                <span class="ft-bulk"><?php $scls=['Aktiv'=>'badge-ok','Pausiert'=>'badge-warning','Gekündigt'=>'badge-neutral']; echo '<span class="badge '.($scls[$a['status']]??'badge-neutral').'">'.he_ab($a['status']).'</span>'; ?></span>
                <select class="inline-input fi-bulk" form="frm-ab-bulk" name="rows[<?= $aid ?>][status]"><?= sel_ab($statuslist,$a['status']) ?></select>
            </td>
            <td class="col-actions">
                <form id="frm-ab-del-<?= $aid ?>" method="POST" action="?page=abos" hidden><?= csrf_field() ?><input type="hidden" name="act" value="delete"><input type="hidden" name="id" value="<?= $aid ?>"><input type="hidden" name="person_filter" value="<?= he_ab($person) ?>"></form>
                <button type="submit" form="frm-ab-del-<?= $aid ?>" class="btn btn-danger btn-xs fi-bulk btn-delete-confirm">✕</button>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($abos)): ?><tr><td colspan="10" class="empty-state">Noch keine Abos angelegt.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div>

<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Neues Abo</h2></div>
    <form id="frm-ab-new" method="POST" action="?page=abos">
        <?= csrf_field() ?><input type="hidden" name="act" value="save"><input type="hidden" name="edit_id" value="0"><input type="hidden" name="person_filter" value="<?= he_ab($person) ?>">
        <?php if(!$is_all): ?><input type="hidden" name="person" value="<?= he_ab($def_person) ?>"><?php endif; ?>
    </form>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Name</th><th>Kategorie</th><?php if($is_all): ?><th>Person</th><?php endif; ?><th>Turnus</th><th>Zahlungsart</th><th>Betrag</th><th></th><th>Nächste Abbuchung</th><th>Status</th><th></th></tr></thead>
        <tbody><tr>
            <td><input class="inline-input new-input" form="frm-ab-new" name="name" placeholder="z.B. Netflix" required></td>
            <td><select class="inline-input new-input" form="frm-ab-new" name="kategorie"><?= sel_ab($kategorien,'Streaming') ?></select></td>
            <?php if($is_all): ?><td><select class="inline-input new-input" form="frm-ab-new" name="person"><?= sel_ab($person_options,$def_person) ?></select></td><?php endif; ?>
            <td><select class="inline-input new-input" form="frm-ab-new" name="turnus"><?= sel_ab($turnusse,'Monatlich') ?></select></td>
            <td><select class="inline-input new-input" form="frm-ab-new" name="zahlungsmethode"><?= sel_ab($zahlmeth,'Kreditkarte') ?></select></td>
            <td><input class="inline-input new-input input-right input-narrow" form="frm-ab-new" name="betrag" placeholder="0,00"></td>
            <td></td>
            <td><input class="inline-input new-input" type="date" form="frm-ab-new" name="naechste_abbuchung"></td>
            <td><select class="inline-input new-input" form="frm-ab-new" name="status"><?= sel_ab($statuslist,'Aktiv') ?></select></td>
            <td class="col-actions"><button type="button" class="btn btn-primary btn-xs" id="btn-new-ab">+ Hinzufügen</button></td>
        </tr></tbody>
    </table></div>
</div>