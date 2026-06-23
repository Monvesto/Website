<?php
// ════════════════════════════════════════════════
// versicherungen.php – Versicherungsverwaltung
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
        $exists = $db->query("SHOW TABLES LIKE 'versicherungen'")->rowCount() > 0;
        if (!$exists) {
            $db->exec("CREATE TABLE versicherungen (
                id              INT AUTO_INCREMENT PRIMARY KEY,
                user_id         INT NOT NULL DEFAULT 0,
                name            VARCHAR(100) NOT NULL,
                typ             VARCHAR(50)  NOT NULL DEFAULT '',
                anbieter        VARCHAR(100) NOT NULL DEFAULT '',
                betrag          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                turnus          ENUM('Monatlich','Vierteljährlich','Halbjährlich','Jährlich') NOT NULL DEFAULT 'Jährlich',
                vertragsnummer  VARCHAR(100) DEFAULT NULL,
                beginn          DATE         DEFAULT NULL,
                ende            DATE         DEFAULT NULL,
                kuendigungsfrist VARCHAR(50) DEFAULT NULL,
                person          VARCHAR(20)  NOT NULL DEFAULT 'Beide',
                status          ENUM('Aktiv','Gekündigt','Abgelaufen') NOT NULL DEFAULT 'Aktiv',
                notiz           TEXT         DEFAULT NULL,
                aktiv           TINYINT(1)   NOT NULL DEFAULT 1,
                created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
            )");
        } else {
            $has = $db->query("SHOW COLUMNS FROM versicherungen LIKE 'user_id'")->rowCount();
            if (!$has) $db->exec("ALTER TABLE versicherungen ADD COLUMN user_id INT NOT NULL DEFAULT 0 AFTER id");
        }
    } catch (PDOException $e) { error_log('Migration versicherungen: ' . $e->getMessage()); }
})();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';
    $pf  = $_POST['person_filter'] ?? $person;

    if ($act === 'save') {
        $name  = trim($_POST['name'] ?? '');
        $typ   = trim($_POST['typ'] ?? '');
        $anb   = trim($_POST['anbieter'] ?? '');
        $bet   = parse_betrag($_POST['betrag'] ?? '0');
        $tur   = $_POST['turnus'] ?? 'Jährlich';
        $vn    = trim($_POST['vertragsnummer'] ?? '');
        $beg   = $_POST['beginn'] ?: null;
        $end   = $_POST['ende'] ?: null;
        $kf    = trim($_POST['kuendigungsfrist'] ?? '');
        $per   = $_POST['person'] ?? $def_person;
        $sta   = $_POST['status'] ?? 'Aktiv';
        $not   = trim($_POST['notiz'] ?? '');
        $id    = (int)($_POST['edit_id'] ?? 0);
        if ($id > 0) {
            $db->prepare("UPDATE versicherungen SET name=?,typ=?,anbieter=?,betrag=?,turnus=?,vertragsnummer=?,beginn=?,ende=?,kuendigungsfrist=?,person=?,status=?,notiz=? WHERE id=? AND user_id=?")
               ->execute([$name,$typ,$anb,$bet,$tur,$vn,$beg,$end,$kf,$per,$sta,$not,$id,$uid]);
        } else {
            $db->prepare("INSERT INTO versicherungen (user_id,name,typ,anbieter,betrag,turnus,vertragsnummer,beginn,ende,kuendigungsfrist,person,status,notiz) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)")
               ->execute([$uid,$name,$typ,$anb,$bet,$tur,$vn,$beg,$end,$kf,$per,$sta,$not]);
        }
        header("Location: ?page=versicherungen&person=$pf&msg=saved"); exit;
    }

    if ($act === 'bulk_save') {
        foreach ($_POST['ids'] ?? [] as $id) {
            $id  = (int)$id; $row = $_POST['rows'][$id] ?? [];
            $name = trim($row['name'] ?? ''); if ($name === '') continue;
            $db->prepare("UPDATE versicherungen SET name=?,typ=?,anbieter=?,betrag=?,turnus=?,vertragsnummer=?,ende=?,kuendigungsfrist=?,person=?,status=?,notiz=? WHERE id=? AND user_id=?")
               ->execute([$name,trim($row['typ']??''),trim($row['anbieter']??''),parse_betrag($row['betrag']??'0'),$row['turnus']??'Jährlich',trim($row['vertragsnummer']??''),$row['ende']?:null,trim($row['kuendigungsfrist']??''),$row['person']??$def_person,$row['status']??'Aktiv',trim($row['notiz']??''),$id,$uid]);
        }
        header("Location: ?page=versicherungen&person=$pf&msg=saved"); exit;
    }

    if ($act === 'delete') {
        $db->prepare("DELETE FROM versicherungen WHERE id=? AND user_id=?")->execute([(int)$_POST['id'], $uid]);
        header("Location: ?page=versicherungen&person=$pf&msg=saved"); exit;
    }
}
if (defined('HANDLE_POST_ONLY')) return;

function he_vs(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function fmt_vs(float $v): string { return number_format($v, 2, ',', '.') . ' €'; }
function to_monthly_vs(float $bet, string $tur): float {
    if ($tur === 'Jährlich')        return round($bet / 12, 2);
    if ($tur === 'Vierteljährlich') return round($bet / 3, 2);
    if ($tur === 'Halbjährlich')    return round($bet / 6, 2);
    return $bet;
}
function sel_vs(array $opts, string $cur): string {
    $out = '';
    foreach ($opts as $o) $out .= '<option value="'.he_vs($o).'"'.($o===$cur?' selected':'').'>'.he_vs($o).'</option>';
    return $out;
}

$typen      = ['KFZ','Haftpflicht','Hausrat','Rechtsschutz','Rente/Leben','Kranken','Unfall','Gebäude','Reise','Sonstiges'];
$turnusse   = ['Monatlich','Vierteljährlich','Halbjährlich','Jährlich'];
$statuslist  = ['Aktiv','Gekündigt','Abgelaufen'];

if ($is_all) {
    $s = $db->prepare("SELECT * FROM versicherungen WHERE user_id=? ORDER BY status, typ, name"); $s->execute([$uid]);
} else {
    $s = $db->prepare("SELECT * FROM versicherungen WHERE user_id=? AND (person=? OR person='Beide') ORDER BY status, typ, name"); $s->execute([$uid,$person]);
}
$vers = $s->fetchAll();

$monatlich_gesamt = 0; $jaehrlich_gesamt = 0; $aktiv_count = 0; $ablauf_soon = [];
foreach ($vers as $v) {
    if ($v['status'] !== 'Aktiv') continue;
    $aktiv_count++;
    $monatlich_gesamt += to_monthly_vs((float)$v['betrag'], $v['turnus']);
    $jaehrlich_gesamt += to_monthly_vs((float)$v['betrag'], $v['turnus']) * 12;
    if ($v['ende']) {
        $diff = (strtotime($v['ende']) - strtotime('today')) / 86400;
        if ($diff >= 0 && $diff <= 60) $ablauf_soon[] = $v;
    }
}

$typ_summen = [];
foreach ($vers as $v) {
    if ($v['status'] !== 'Aktiv') continue;
    $t = $v['typ'] ?: 'Sonstiges';
    $typ_summen[$t] = ($typ_summen[$t] ?? 0) + to_monthly_vs((float)$v['betrag'], $v['turnus']) * 12;
}
arsort($typ_summen);
?>

<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">Gespeichert.</div><?php endif; ?>

<div class="finance-topbar">
    <div class="tab-bar"></div>
    <div class="person-switcher">
        <?php foreach ($person_options as $p): ?>
        <a href="?page=versicherungen&person=<?= $p ?>" class="person-btn <?= $person===$p?'active':'' ?>"><?= $p ?></a>
        <?php endforeach; ?>
    </div>
</div>

<div class="kpi-grid kpi-grid--4 mt-4">
    <div class="kpi-card kpi-card--info"><div class="kpi-label">Monatliche Kosten</div><div class="kpi-value kpi-value--md text-red"><?= fmt_vs($monatlich_gesamt) ?></div></div>
    <div class="kpi-card"><div class="kpi-label">Jährliche Kosten</div><div class="kpi-value kpi-value--md"><?= fmt_vs($jaehrlich_gesamt) ?></div></div>
    <div class="kpi-card"><div class="kpi-label">Aktive Versicherungen</div><div class="kpi-value kpi-value--md"><?= $aktiv_count ?></div></div>
    <div class="kpi-card <?= count($ablauf_soon)>0?'kpi-card--alert':'' ?>"><div class="kpi-label">Läuft bald ab (60 Tage)</div><div class="kpi-value kpi-value--md <?= count($ablauf_soon)>0?'text-red':'' ?>"><?= count($ablauf_soon) ?></div></div>
</div>

<?php if (!empty($ablauf_soon)): ?>
<div class="card mt-4">
    <div class="card-head card-head--amber"><h2 class="card-title">⚠ Läuft bald ab / zu kündigen</h2><span class="badge badge-warning"><?= count($ablauf_soon) ?> in 60 Tagen</span></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Versicherung</th><th>Anbieter</th><th>Kündigung bis</th><th class="col-right">Betrag/Jahr</th></tr></thead>
        <tbody>
        <?php foreach ($ablauf_soon as $v): ?>
        <tr><td><?= he_vs($v['name']) ?></td><td><?= he_vs($v['anbieter']) ?></td><td><span class="date-soon"><?= date('d.m.Y',strtotime($v['ende'])) ?></span></td><td class="col-right fw-700 text-red"><?= fmt_vs(to_monthly_vs((float)$v['betrag'],$v['turnus'])*12) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>

<?php if (!empty($typ_summen)): ?>
<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Nach Versicherungstyp</h2></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Typ</th><th>Anteil</th><th class="col-right">Jährlich</th></tr></thead>
        <tbody>
        <?php foreach ($typ_summen as $typ => $sum):
            $anteil = $jaehrlich_gesamt > 0 ? $sum / $jaehrlich_gesamt * 100 : 0;
        ?>
        <tr><td><?= he_vs($typ) ?></td>
        <td><div class="bar-wrap"><div class="bar-track"><div class="bar-fill" data-width="<?= number_format($anteil,1,'.','') ?>"></div></div><span class="bar-label"><?= number_format($anteil,0) ?>%</span></div></td>
        <td class="col-right fw-700"><?= fmt_vs($sum) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>

<form id="frm-vs-bulk" method="POST" action="?page=versicherungen">
    <?= csrf_field() ?><input type="hidden" name="act" value="bulk_save"><input type="hidden" name="person_filter" value="<?= he_vs($person) ?>">
    <?php foreach ($vers as $v): ?><input type="hidden" name="ids[]" value="<?= $v['id'] ?>"><?php endforeach; ?>
</form>

<div class="card mt-4" id="card-versicherungen">
    <div class="card-head">
        <div class="card-head-left"><h2 class="card-title">🛡 Alle Versicherungen<?= !$is_all?' – '.$person:'' ?></h2><span class="badge badge-neutral"><?= count($vers) ?> gesamt</span></div>
        <div class="bulk-bar">
            <button type="button" class="btn btn-ghost btn-sm" id="btn-edit-vs">✏ Bearbeiten</button>
            <button type="submit" form="frm-vs-bulk" class="btn btn-primary btn-sm btn-hidden" id="btn-save-vs">✓ Speichern</button>
        </div>
    </div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr>
            <th>Name</th><th>Typ</th><th>Anbieter</th>
            <?php if($is_all): ?><th>Person</th><?php endif; ?>
            <th>Turnus</th><th class="col-right">Betrag</th><th class="col-right">Jährlich</th>
            <th>Vertragsnr.</th><th>Läuft bis</th><th>Kündigung</th><th>Status</th><th></th>
        </tr></thead>
        <tbody>
        <?php foreach ($vers as $v): $vid=$v['id']; ?>
        <tr class="<?= $v['status']!=='Aktiv'?'row-done':'' ?>">
            <td>
                <span class="ft-bulk"><?= he_vs($v['name']) ?></span><input class="inline-input fi-bulk" form="frm-vs-bulk" name="rows[<?= $vid ?>][name]" value="<?= he_vs($v['name']) ?>" required>
                <?php if ($v['notiz']): ?><div class="ft-bulk" style="font-size:11px;color:var(--text-muted)"><?= he_vs($v['notiz']) ?></div><?php endif; ?>
                <input class="inline-input fi-bulk" form="frm-vs-bulk" name="rows[<?= $vid ?>][notiz]" value="<?= he_vs($v['notiz']??'') ?>" placeholder="Notiz" style="margin-top:2px">
            </td>
            <td><span class="ft-bulk"><span class="badge badge-neutral"><?= he_vs($v['typ']??'–') ?></span></span><select class="inline-input fi-bulk" form="frm-vs-bulk" name="rows[<?= $vid ?>][typ]"><?= sel_vs($typen,$v['typ']??'') ?></select></td>
            <td><span class="ft-bulk"><?= he_vs($v['anbieter']??'–') ?></span><input class="inline-input fi-bulk" form="frm-vs-bulk" name="rows[<?= $vid ?>][anbieter]" value="<?= he_vs($v['anbieter']??'') ?>"></td>
            <?php if($is_all): ?>
            <td><span class="ft-bulk"><?= he_vs($v['person']) ?></span><select class="inline-input fi-bulk" form="frm-vs-bulk" name="rows[<?= $vid ?>][person]"><?= sel_vs($person_options,$v['person']) ?></select></td>
            <?php else: ?>
            <input type="hidden" form="frm-vs-bulk" name="rows[<?= $vid ?>][person]" value="<?= he_vs($v['person']) ?>">
            <?php endif; ?>
            <td><span class="ft-bulk"><?= he_vs($v['turnus']) ?></span><select class="inline-input fi-bulk" form="frm-vs-bulk" name="rows[<?= $vid ?>][turnus]"><?= sel_vs($turnusse,$v['turnus']) ?></select></td>
            <td class="col-right"><span class="ft-bulk fw-700"><?= fmt_vs((float)$v['betrag']) ?></span><input class="inline-input fi-bulk input-right input-narrow" form="frm-vs-bulk" name="rows[<?= $vid ?>][betrag]" value="<?= he_vs(number_format((float)$v['betrag'],2,',','.')) ?>"></td>
            <td class="col-right text-muted"><?= fmt_vs(to_monthly_vs((float)$v['betrag'],$v['turnus'])*12) ?></td>
            <td><span class="ft-bulk"><?= he_vs($v['vertragsnummer']??'–') ?></span><input class="inline-input fi-bulk" form="frm-vs-bulk" name="rows[<?= $vid ?>][vertragsnummer]" value="<?= he_vs($v['vertragsnummer']??'') ?>"></td>
            <td><span class="ft-bulk"><?= $v['ende']?date('d.m.Y',strtotime($v['ende'])):'–' ?></span><input class="inline-input fi-bulk" type="date" form="frm-vs-bulk" name="rows[<?= $vid ?>][ende]" value="<?= he_vs($v['ende']??'') ?>"></td>
            <td><span class="ft-bulk"><?= he_vs($v['kuendigungsfrist']??'–') ?></span><input class="inline-input fi-bulk" form="frm-vs-bulk" name="rows[<?= $vid ?>][kuendigungsfrist]" value="<?= he_vs($v['kuendigungsfrist']??'') ?>" placeholder="z.B. 3 Monate"></td>
            <td>
                <span class="ft-bulk"><?php $scls=['Aktiv'=>'badge-ok','Gekündigt'=>'badge-warning','Abgelaufen'=>'badge-neutral']; echo '<span class="badge '.($scls[$v['status']]??'badge-neutral').'">'.he_vs($v['status']).'</span>'; ?></span>
                <select class="inline-input fi-bulk" form="frm-vs-bulk" name="rows[<?= $vid ?>][status]"><?= sel_vs($statuslist,$v['status']) ?></select>
            </td>
            <td class="col-actions">
                <form id="frm-vs-del-<?= $vid ?>" method="POST" action="?page=versicherungen" hidden><?= csrf_field() ?><input type="hidden" name="act" value="delete"><input type="hidden" name="id" value="<?= $vid ?>"><input type="hidden" name="person_filter" value="<?= he_vs($person) ?>"></form>
                <button type="submit" form="frm-vs-del-<?= $vid ?>" class="btn btn-danger btn-xs fi-bulk btn-delete-confirm">✕</button>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($vers)): ?><tr><td colspan="12" class="empty-state">Noch keine Versicherungen angelegt.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div>

<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Neue Versicherung</h2></div>
    <form id="frm-vs-new" method="POST" action="?page=versicherungen">
        <?= csrf_field() ?><input type="hidden" name="act" value="save"><input type="hidden" name="edit_id" value="0"><input type="hidden" name="person_filter" value="<?= he_vs($person) ?>">
        <?php if(!$is_all): ?><input type="hidden" name="person" value="<?= he_vs($def_person) ?>"><?php endif; ?>
    </form>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Name</th><th>Typ</th><th>Anbieter</th><?php if($is_all): ?><th>Person</th><?php endif; ?><th>Turnus</th><th>Betrag</th><th></th><th>Vertragsnr.</th><th>Läuft bis</th><th>Kündigung</th><th>Status</th><th></th></tr></thead>
        <tbody><tr>
            <td><input class="inline-input new-input" form="frm-vs-new" name="name" placeholder="z.B. KFZ Haftpflicht" required></td>
            <td><select class="inline-input new-input" form="frm-vs-new" name="typ"><?= sel_vs($typen,'KFZ') ?></select></td>
            <td><input class="inline-input new-input" form="frm-vs-new" name="anbieter" placeholder="Anbieter"></td>
            <?php if($is_all): ?><td><select class="inline-input new-input" form="frm-vs-new" name="person"><?= sel_vs($person_options,$def_person) ?></select></td><?php endif; ?>
            <td><select class="inline-input new-input" form="frm-vs-new" name="turnus"><?= sel_vs($turnusse,'Jährlich') ?></select></td>
            <td><input class="inline-input new-input input-right input-narrow" form="frm-vs-new" name="betrag" placeholder="0,00"></td>
            <td></td>
            <td><input class="inline-input new-input" form="frm-vs-new" name="vertragsnummer" placeholder="Vertragsnr."></td>
            <td><input class="inline-input new-input" type="date" form="frm-vs-new" name="ende"></td>
            <td><input class="inline-input new-input" form="frm-vs-new" name="kuendigungsfrist" placeholder="z.B. 3 Monate"></td>
            <td><select class="inline-input new-input" form="frm-vs-new" name="status"><?= sel_vs($statuslist,'Aktiv') ?></select></td>
            <td class="col-actions"><button type="button" class="btn btn-primary btn-xs" id="btn-new-vs">+ Hinzufügen</button></td>
        </tr></tbody>
    </table></div>
</div>