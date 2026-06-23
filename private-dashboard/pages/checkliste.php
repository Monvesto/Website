<?php
// ════════════════════════════════════════════════
// checkliste.php – Zahlungen & Mieteinnahmen
// Tabs: Zahlungen / Mieteinnahmen / Übersicht / Verwaltung
// user_id Filter: alle Queries auf eingeloggten User beschränkt
// ════════════════════════════════════════════════
$db     = get_db();
$uid    = current_user_id();
$tab    = $_GET['tab'] ?? 'uebersicht';
$person_options = get_person_options();
$person = $_GET['person'] ?? ($person_options[0] ?? 'Marcel');
if (!in_array($person, $person_options, true)) $person = $person_options[0] ?? 'Marcel';
$is_all     = person_is_all($person);
$def_person = $is_all ? ($person_options[0] ?? 'Marcel') : $person;

$monat_param = $_GET['monat'] ?? date('Y-m');
$monat_date  = $monat_param . '-01';
$monate_de   = ['January'=>'Januar','February'=>'Februar','March'=>'März','April'=>'April',
    'May'=>'Mai','June'=>'Juni','July'=>'Juli','August'=>'August',
    'September'=>'September','October'=>'Oktober','November'=>'November','December'=>'Dezember'];
$monat_label = strtr(date('F Y', strtotime($monat_date)), $monate_de);
$prev_monat  = date('Y-m', strtotime($monat_date . ' -1 month'));
$next_monat  = date('Y-m', strtotime($monat_date . ' +1 month'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act    = $_POST['act'] ?? '';
    $mo     = $_POST['monat'] ?? $monat_param;
    $person = $_POST['person_filter'] ?? $person;

    // ── Status-Updates verwenden zahlung_id/miete_id ──
    // checkliste_status und mieten_status haben keine user_id –
    // Sicherheit kommt durch JOIN mit user-eigenen zahlung_ids
    if ($act === 'set_status') {
        $stmt = $db->prepare('INSERT INTO checkliste_status (zahlung_id, monat, status)
            VALUES (?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status)');
        $stmt->execute([(int)$_POST['zahlung_id'], $mo.'-01', (int)$_POST['status']]);
        header("Location: ?page=checkliste&tab=zahlungen&monat=$mo&person=$person"); exit;
    }

    if ($act === 'set_miete_status') {
        $stmt = $db->prepare('INSERT INTO mieten_status (miete_id, monat, status)
            VALUES (?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status)');
        $stmt->execute([(int)$_POST['miete_id'], $mo.'-01', (int)$_POST['status']]);
        header("Location: ?page=checkliste&tab=mieten&monat=$mo&person=$person"); exit;
    }

    if ($act === 'zahlung_create') {
        $bez = trim($_POST['bezeichnung'] ?? '');
        $bet = parse_betrag($_POST['betrag'] ?? '0');
        $per = $_POST['person'] ?? $def_person;
        $kat = trim($_POST['kategorie'] ?? '');
        $tur = $_POST['turnus'] ?? 'Monatlich';
        if ($bez !== '') {
            $max = $db->prepare('SELECT COALESCE(MAX(sort_order),0)+1 FROM checkliste_zahlungen WHERE user_id=?');
            $max->execute([$uid]); $max = $max->fetchColumn();
            $db->prepare('INSERT INTO checkliste_zahlungen (user_id,bezeichnung,betrag,person,kategorie,turnus,sort_order) VALUES (?,?,?,?,?,?,?)')->execute([$uid,$bez,$bet,$per,$kat,$tur,$max]);
        }
        header("Location: ?page=checkliste&tab=verwaltung&person=$person"); exit;
    }

    if ($act === 'zahlung_delete') {
        $id = (int)$_POST['id'];
        $db->prepare('DELETE FROM checkliste_zahlungen WHERE id=? AND user_id=?')->execute([$id,$uid]);
        $db->prepare('DELETE FROM checkliste_status WHERE zahlung_id=?')->execute([$id]);
        header("Location: ?page=checkliste&tab=verwaltung&person=$person"); exit;
    }

    if ($act === 'zahlungen_bulk_save') {
        foreach ($_POST['ids'] ?? [] as $id) {
            $id  = (int)$id; $row = $_POST['rows'][$id] ?? [];
            $bez = trim($row['bezeichnung'] ?? '');
            if ($bez === '') continue;
            $db->prepare('UPDATE checkliste_zahlungen SET bezeichnung=?,betrag=?,person=?,kategorie=?,turnus=? WHERE id=? AND user_id=?')
               ->execute([trim($row['bezeichnung']??''),parse_betrag($row['betrag']??'0'),$row['person']??$def_person,trim($row['kategorie']??''),$row['turnus']??'Monatlich',$id,$uid]);
        }
        header("Location: ?page=checkliste&tab=verwaltung&person=$person"); exit;
    }

    if ($act === 'mieten_bulk_save') {
        foreach ($_POST['ids'] ?? [] as $id) {
            $id  = (int)$id; $row = $_POST['rows'][$id] ?? [];
            $bez = trim($row['bezeichnung'] ?? '');
            if ($bez === '') continue;
            $db->prepare('UPDATE mieten_checkliste SET bezeichnung=?,typ=?,person=? WHERE id=? AND user_id=?')
               ->execute([$bez,trim($row['typ']??'Kaltmiete'),$row['person']??$def_person,$id,$uid]);
        }
        header("Location: ?page=checkliste&tab=verwaltung&person=$person"); exit;
    }

    if ($act === 'miete_create') {
        $bez = trim($_POST['bezeichnung'] ?? '');
        $typ = trim($_POST['typ'] ?? 'Kaltmiete');
        $per = $_POST['person'] ?? $def_person;
        $iid = (int)($_POST['immobilien_id'] ?? 0);
        if ($bez !== '') {
            $max = $db->prepare('SELECT COALESCE(MAX(sort_order),0)+1 FROM mieten_checkliste WHERE user_id=?');
            $max->execute([$uid]); $max = $max->fetchColumn();
            $db->prepare('INSERT INTO mieten_checkliste (user_id,bezeichnung,typ,person,immobilien_id,sort_order) VALUES (?,?,?,?,?,?)')->execute([$uid,$bez,$typ,$per,$iid,$max]);
        }
        header("Location: ?page=checkliste&tab=verwaltung&person=$person"); exit;
    }

    if ($act === 'miete_delete') {
        $id = (int)$_POST['id'];
        $db->prepare('DELETE FROM mieten_checkliste WHERE id=? AND user_id=?')->execute([$id,$uid]);
        $db->prepare('DELETE FROM mieten_status WHERE miete_id=?')->execute([$id]);
        header("Location: ?page=checkliste&tab=verwaltung&person=$person"); exit;
    }

    if ($act === 'zahlung_reorder') {
        $id = (int)$_POST['id']; $dir = $_POST['dir'] ?? '';
        $cur = $db->prepare("SELECT sort_order FROM checkliste_zahlungen WHERE id=? AND user_id=?");
        $cur->execute([$id,$uid]); $curPos = (int)$cur->fetchColumn();
        if ($dir === 'up') {
            $nb = $db->prepare("SELECT id, sort_order FROM checkliste_zahlungen WHERE user_id=? AND sort_order < ? ORDER BY sort_order DESC LIMIT 1");
        } else {
            $nb = $db->prepare("SELECT id, sort_order FROM checkliste_zahlungen WHERE user_id=? AND sort_order > ? ORDER BY sort_order ASC LIMIT 1");
        }
        $nb->execute([$uid,$curPos]); $neighbor = $nb->fetch();
        if ($neighbor) {
            $db->prepare("UPDATE checkliste_zahlungen SET sort_order=? WHERE id=? AND user_id=?")->execute([$neighbor['sort_order'],$id,$uid]);
            $db->prepare("UPDATE checkliste_zahlungen SET sort_order=? WHERE id=? AND user_id=?")->execute([$curPos,$neighbor['id'],$uid]);
        }
        header("Location: ?page=checkliste&tab=verwaltung&person=$person"); exit;
    }

    if ($act === 'miete_reorder') {
        $id = (int)$_POST['id']; $dir = $_POST['dir'] ?? '';
        $cur = $db->prepare("SELECT sort_order FROM mieten_checkliste WHERE id=? AND user_id=?");
        $cur->execute([$id,$uid]); $curPos = (int)$cur->fetchColumn();
        if ($dir === 'up') {
            $nb = $db->prepare("SELECT id, sort_order FROM mieten_checkliste WHERE user_id=? AND sort_order < ? ORDER BY sort_order DESC LIMIT 1");
        } else {
            $nb = $db->prepare("SELECT id, sort_order FROM mieten_checkliste WHERE user_id=? AND sort_order > ? ORDER BY sort_order ASC LIMIT 1");
        }
        $nb->execute([$uid,$curPos]); $neighbor = $nb->fetch();
        if ($neighbor) {
            $db->prepare("UPDATE mieten_checkliste SET sort_order=? WHERE id=? AND user_id=?")->execute([$neighbor['sort_order'],$id,$uid]);
            $db->prepare("UPDATE mieten_checkliste SET sort_order=? WHERE id=? AND user_id=?")->execute([$curPos,$neighbor['id'],$uid]);
        }
        header("Location: ?page=checkliste&tab=verwaltung&person=$person"); exit;
    }
}

if (defined('HANDLE_POST_ONLY')) return;

// ── Zahlungen laden ──
if ($is_all) {
    $s = $db->prepare('SELECT * FROM checkliste_zahlungen WHERE user_id=? AND aktiv=1 ORDER BY sort_order');
    $s->execute([$uid]);
} else {
    $s = $db->prepare("SELECT * FROM checkliste_zahlungen WHERE user_id=? AND aktiv=1 AND (person=? OR person='Beide') ORDER BY sort_order");
    $s->execute([$uid,$person]);
}
$zahlungen = $s->fetchAll();

$status_map = [];
if (!empty($zahlungen)) {
    $ids  = implode(',', array_column($zahlungen, 'id'));
    $rows = $db->query("SELECT zahlung_id, status FROM checkliste_status WHERE monat='$monat_date' AND zahlung_id IN ($ids)")->fetchAll();
    foreach ($rows as $r) $status_map[$r['zahlung_id']] = (int)$r['status'];
}

$z_offen = []; $z_bezahlt = []; $z_klaerung = [];
foreach ($zahlungen as $z) {
    $st = $status_map[$z['id']] ?? 0;
    if ($st === 1)     $z_bezahlt[]  = array_merge($z, ['status' => $st]);
    elseif ($st === 3) $z_klaerung[] = array_merge($z, ['status' => $st]);
    else               $z_offen[]    = array_merge($z, ['status' => $st]);
}

// ── Mieten laden ──
if ($is_all) {
    $s = $db->prepare('SELECT * FROM mieten_checkliste WHERE user_id=? AND aktiv=1 ORDER BY sort_order');
    $s->execute([$uid]);
} else {
    $s = $db->prepare("SELECT * FROM mieten_checkliste WHERE user_id=? AND aktiv=1 AND (person=? OR person='Beide') ORDER BY sort_order");
    $s->execute([$uid,$person]);
}
$mieten = $s->fetchAll();

$mieten_status_map = [];
if (!empty($mieten)) {
    $ids  = implode(',', array_column($mieten, 'id'));
    $rows = $db->query("SELECT miete_id, status FROM mieten_status WHERE monat='$monat_date' AND miete_id IN ($ids)")->fetchAll();
    foreach ($rows as $r) $mieten_status_map[$r['miete_id']] = (int)$r['status'];
}

$m_offen = []; $m_bezahlt = []; $m_verspaetet = [];
foreach ($mieten as $m) {
    $st = $mieten_status_map[$m['id']] ?? 0;
    if ($st === 1)     $m_bezahlt[]    = array_merge($m, ['status' => $st]);
    elseif ($st === 2) $m_verspaetet[] = array_merge($m, ['status' => $st]);
    else               $m_offen[]      = array_merge($m, ['status' => $st]);
}

function he2(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function fmt_chk(float $v): string { return number_format($v, 2, ',', '.') . ' €'; }

function sort_btns_z(int $id): string {
    $csrf = csrf_field();
    return '<form method="POST" action="?page=checkliste" class="form-inline">'.$csrf.'<input type="hidden" name="act" value="zahlung_reorder"><input type="hidden" name="id" value="'.$id.'"><input type="hidden" name="dir" value="up"><button type="submit" class="btn-sort">▲</button></form>
    <form method="POST" action="?page=checkliste" class="form-inline">'.$csrf.'<input type="hidden" name="act" value="zahlung_reorder"><input type="hidden" name="id" value="'.$id.'"><input type="hidden" name="dir" value="down"><button type="submit" class="btn-sort">▼</button></form>';
}

function sort_btns_m(int $id): string {
    $csrf = csrf_field();
    return '<form method="POST" action="?page=checkliste" class="form-inline">'.$csrf.'<input type="hidden" name="act" value="miete_reorder"><input type="hidden" name="id" value="'.$id.'"><input type="hidden" name="dir" value="up"><button type="submit" class="btn-sort">▲</button></form>
    <form method="POST" action="?page=checkliste" class="form-inline">'.$csrf.'<input type="hidden" name="act" value="miete_reorder"><input type="hidden" name="id" value="'.$id.'"><input type="hidden" name="dir" value="down"><button type="submit" class="btn-sort">▼</button></form>';
}
?>

<div class="finance-topbar">
    <div class="tab-bar">
        <a href="?page=checkliste&tab=zahlungen&monat=<?= $monat_param ?>&person=<?= $person ?>"  class="tab-link <?= $tab==='zahlungen'?'active':'' ?>">💳 Zahlungen</a>
        <a href="?page=checkliste&tab=mieten&monat=<?= $monat_param ?>&person=<?= $person ?>"     class="tab-link <?= $tab==='mieten'?'active':'' ?>">🏠 Mieteinnahmen</a>
        <a href="?page=checkliste&tab=uebersicht&person=<?= $person ?>"                           class="tab-link <?= $tab==='uebersicht'?'active':'' ?>">📊 Übersicht</a>
        <a href="?page=checkliste&tab=verwaltung&person=<?= $person ?>"                           class="tab-link <?= $tab==='verwaltung'?'active':'' ?>">⚙ Verwaltung</a>
    </div>
    <div class="person-switcher">
        <?php foreach ($person_options as $p): ?>
        <a href="?page=checkliste&tab=<?= $tab ?>&monat=<?= $monat_param ?>&person=<?= $p ?>" class="person-btn <?= $person===$p?'active':'' ?>"><?= $p ?></a>
        <?php endforeach; ?>
    </div>
</div>

<?php if ($tab !== 'uebersicht' && $tab !== 'verwaltung'): ?>
<div class="chk-monat-nav">
    <a href="?page=checkliste&tab=<?= $tab ?>&monat=<?= $prev_monat ?>&person=<?= $person ?>" class="btn btn-ghost btn-sm">‹</a>
    <span class="monat-label"><?= $monat_label ?></span>
    <a href="?page=checkliste&tab=<?= $tab ?>&monat=<?= $next_monat ?>&person=<?= $person ?>" class="btn btn-ghost btn-sm">›</a>
</div>
<?php endif; ?>

<?php if ($tab === 'zahlungen'): ?>
<!-- ════ ZAHLUNGEN ════ -->

<div class="kpi-grid kpi-grid--4 mt-4">
    <div class="kpi-card"><div class="kpi-label">Offen</div><div class="kpi-value"><?= count($z_offen) ?></div></div>
    <div class="kpi-card <?= count($z_bezahlt)===count($zahlungen)&&count($zahlungen)>0?'kpi-card--info':'' ?>"><div class="kpi-label">Bezahlt</div><div class="kpi-value text-green"><?= count($z_bezahlt) ?></div></div>
    <div class="kpi-card <?= count($z_klaerung)>0?'kpi-card--alert':'' ?>"><div class="kpi-label">In Klärung</div><div class="kpi-value <?= count($z_klaerung)>0?'text-red':'' ?>"><?= count($z_klaerung) ?></div></div>
    <div class="kpi-card"><div class="kpi-label">Gesamtbetrag</div><div class="kpi-value kpi-value--md"><?= fmt_chk(array_sum(array_column($zahlungen,'betrag'))) ?></div></div>
</div>

<?php if (!empty($z_offen)): ?>
<div class="card mt-4">
    <div class="card-head card-head--amber"><h2 class="card-title">Offene Zahlungen – <?= $monat_label ?></h2><span class="badge badge-warning"><?= count($z_offen) ?> offen</span></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Bezeichnung</th><th>Betrag</th><th>Person</th><th>Turnus</th><th>Aktionen</th></tr></thead>
        <tbody>
        <?php foreach ($z_offen as $z): ?>
        <tr>
            <td><?= he2($z['bezeichnung']) ?></td>
            <td class="fw-700"><?= fmt_chk((float)$z['betrag']) ?></td>
            <td><?= he2($z['person']) ?></td>
            <td><span class="badge badge-neutral"><?= he2($z['turnus']) ?></span></td>
            <td class="actions-cell">
                <form method="POST" action="?page=checkliste" class="form-inline"><?= csrf_field() ?><input type="hidden" name="act" value="set_status"><input type="hidden" name="zahlung_id" value="<?= $z['id'] ?>"><input type="hidden" name="monat" value="<?= $monat_param ?>"><input type="hidden" name="person_filter" value="<?= he2($person) ?>"><input type="hidden" name="status" value="1"><button type="submit" class="btn btn-ok btn-xs">✓ Bezahlt</button></form>
                <form method="POST" action="?page=checkliste" class="form-inline"><?= csrf_field() ?><input type="hidden" name="act" value="set_status"><input type="hidden" name="zahlung_id" value="<?= $z['id'] ?>"><input type="hidden" name="monat" value="<?= $monat_param ?>"><input type="hidden" name="person_filter" value="<?= he2($person) ?>"><input type="hidden" name="status" value="3"><button type="submit" class="btn btn-ghost btn-xs">? Klärung</button></form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php else: ?>
<div class="card mt-4"><div class="card-head card-head--green"><h2 class="card-title">Offene Zahlungen – <?= $monat_label ?></h2></div><p class="empty-state">✓ Alle Zahlungen erledigt!</p></div>
<?php endif; ?>

<?php if (!empty($z_klaerung)): ?>
<div class="card mt-4">
    <div class="card-head card-head--red"><h2 class="card-title">In Klärung</h2><span class="badge badge-danger"><?= count($z_klaerung) ?> in Klärung</span></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Bezeichnung</th><th>Betrag</th><th>Person</th><th>Aktionen</th></tr></thead>
        <tbody>
        <?php foreach ($z_klaerung as $z): ?>
        <tr>
            <td><?= he2($z['bezeichnung']) ?></td><td class="fw-700"><?= fmt_chk((float)$z['betrag']) ?></td><td><?= he2($z['person']) ?></td>
            <td class="actions-cell">
                <form method="POST" action="?page=checkliste" class="form-inline"><?= csrf_field() ?><input type="hidden" name="act" value="set_status"><input type="hidden" name="zahlung_id" value="<?= $z['id'] ?>"><input type="hidden" name="monat" value="<?= $monat_param ?>"><input type="hidden" name="person_filter" value="<?= he2($person) ?>"><input type="hidden" name="status" value="1"><button type="submit" class="btn btn-ok btn-xs">✓ Bezahlt</button></form>
                <form method="POST" action="?page=checkliste" class="form-inline"><?= csrf_field() ?><input type="hidden" name="act" value="set_status"><input type="hidden" name="zahlung_id" value="<?= $z['id'] ?>"><input type="hidden" name="monat" value="<?= $monat_param ?>"><input type="hidden" name="person_filter" value="<?= he2($person) ?>"><input type="hidden" name="status" value="0"><button type="submit" class="btn btn-ghost btn-xs">↩ Zurück</button></form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>

<?php if (!empty($z_bezahlt)): ?>
<div class="card mt-4">
    <div class="card-head card-head--green"><h2 class="card-title">Erledigte Zahlungen</h2><span class="badge badge-ok"><?= count($z_bezahlt) ?> bezahlt</span></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Bezeichnung</th><th>Betrag</th><th>Person</th><th>Aktionen</th></tr></thead>
        <tbody>
        <?php foreach ($z_bezahlt as $z): ?>
        <tr class="row-done">
            <td><?= he2($z['bezeichnung']) ?></td><td class="fw-700"><?= fmt_chk((float)$z['betrag']) ?></td><td><?= he2($z['person']) ?></td>
            <td class="actions-cell">
                <form method="POST" action="?page=checkliste" class="form-inline"><?= csrf_field() ?><input type="hidden" name="act" value="set_status"><input type="hidden" name="zahlung_id" value="<?= $z['id'] ?>"><input type="hidden" name="monat" value="<?= $monat_param ?>"><input type="hidden" name="person_filter" value="<?= he2($person) ?>"><input type="hidden" name="status" value="0"><button type="submit" class="btn btn-ghost btn-xs">↩ Offen</button></form>
                <form method="POST" action="?page=checkliste" class="form-inline"><?= csrf_field() ?><input type="hidden" name="act" value="set_status"><input type="hidden" name="zahlung_id" value="<?= $z['id'] ?>"><input type="hidden" name="monat" value="<?= $monat_param ?>"><input type="hidden" name="person_filter" value="<?= he2($person) ?>"><input type="hidden" name="status" value="3"><button type="submit" class="btn btn-ghost btn-xs">? Klärung</button></form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>

<?php elseif ($tab === 'mieten'): ?>
<!-- ════ MIETEINNAHMEN ════ -->

<div class="kpi-grid kpi-grid--4 mt-4">
    <div class="kpi-card"><div class="kpi-label">Offen</div><div class="kpi-value"><?= count($m_offen) ?></div></div>
    <div class="kpi-card <?= count($m_verspaetet)>0?'kpi-card--alert':'' ?>"><div class="kpi-label">Verspätet</div><div class="kpi-value <?= count($m_verspaetet)>0?'text-red':'' ?>"><?= count($m_verspaetet) ?></div></div>
    <div class="kpi-card <?= count($m_bezahlt)===count($mieten)&&count($mieten)>0?'kpi-card--info':'' ?>"><div class="kpi-label">Eingegangen</div><div class="kpi-value text-green"><?= count($m_bezahlt) ?></div></div>
    <div class="kpi-card"><div class="kpi-label">Gesamt</div><div class="kpi-value kpi-value--md"><?= count($mieten) ?></div></div>
</div>

<?php if (!empty($m_offen)): ?>
<div class="card mt-4">
    <div class="card-head card-head--amber"><h2 class="card-title">Ausstehend – <?= $monat_label ?></h2><span class="badge badge-warning"><?= count($m_offen) ?> ausstehend</span></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Objekt / Mieter</th><th>Typ</th><th>Aktionen</th></tr></thead>
        <tbody>
        <?php foreach ($m_offen as $m): ?>
        <tr>
            <td><?= he2($m['bezeichnung']) ?></td><td><span class="badge badge-neutral"><?= he2($m['typ']) ?></span></td>
            <td class="actions-cell">
                <form method="POST" action="?page=checkliste" class="form-inline"><?= csrf_field() ?><input type="hidden" name="act" value="set_miete_status"><input type="hidden" name="miete_id" value="<?= $m['id'] ?>"><input type="hidden" name="monat" value="<?= $monat_param ?>"><input type="hidden" name="person_filter" value="<?= he2($person) ?>"><input type="hidden" name="status" value="1"><button type="submit" class="btn btn-ok btn-xs">✓ Eingegangen</button></form>
                <form method="POST" action="?page=checkliste" class="form-inline"><?= csrf_field() ?><input type="hidden" name="act" value="set_miete_status"><input type="hidden" name="miete_id" value="<?= $m['id'] ?>"><input type="hidden" name="monat" value="<?= $monat_param ?>"><input type="hidden" name="person_filter" value="<?= he2($person) ?>"><input type="hidden" name="status" value="2"><button type="submit" class="btn btn-danger btn-xs">! Verspätet</button></form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php else: ?>
<div class="card mt-4"><div class="card-head card-head--green"><h2 class="card-title">Ausstehend – <?= $monat_label ?></h2></div><p class="empty-state">✓ Alle Mieteinnahmen eingegangen!</p></div>
<?php endif; ?>

<?php if (!empty($m_verspaetet)): ?>
<div class="card mt-4">
    <div class="card-head card-head--red"><h2 class="card-title">Verspätet</h2><span class="badge badge-danger"><?= count($m_verspaetet) ?></span></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Objekt / Mieter</th><th>Typ</th><th>Aktionen</th></tr></thead>
        <tbody>
        <?php foreach ($m_verspaetet as $m): ?>
        <tr>
            <td><?= he2($m['bezeichnung']) ?></td><td><span class="badge badge-danger"><?= he2($m['typ']) ?></span></td>
            <td class="actions-cell">
                <form method="POST" action="?page=checkliste" class="form-inline"><?= csrf_field() ?><input type="hidden" name="act" value="set_miete_status"><input type="hidden" name="miete_id" value="<?= $m['id'] ?>"><input type="hidden" name="monat" value="<?= $monat_param ?>"><input type="hidden" name="person_filter" value="<?= he2($person) ?>"><input type="hidden" name="status" value="1"><button type="submit" class="btn btn-ok btn-xs">✓ Eingegangen</button></form>
                <form method="POST" action="?page=checkliste" class="form-inline"><?= csrf_field() ?><input type="hidden" name="act" value="set_miete_status"><input type="hidden" name="miete_id" value="<?= $m['id'] ?>"><input type="hidden" name="monat" value="<?= $monat_param ?>"><input type="hidden" name="person_filter" value="<?= he2($person) ?>"><input type="hidden" name="status" value="0"><button type="submit" class="btn btn-ghost btn-xs">↩ Zurück</button></form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>

<?php if (!empty($m_bezahlt)): ?>
<div class="card mt-4">
    <div class="card-head card-head--green"><h2 class="card-title">Eingegangen</h2><span class="badge badge-ok"><?= count($m_bezahlt) ?> eingegangen</span></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Objekt / Mieter</th><th>Typ</th><th>Aktionen</th></tr></thead>
        <tbody>
        <?php foreach ($m_bezahlt as $m): ?>
        <tr class="row-done">
            <td><?= he2($m['bezeichnung']) ?></td><td><span class="badge badge-ok"><?= he2($m['typ']) ?></span></td>
            <td class="actions-cell">
                <form method="POST" action="?page=checkliste" class="form-inline"><?= csrf_field() ?><input type="hidden" name="act" value="set_miete_status"><input type="hidden" name="miete_id" value="<?= $m['id'] ?>"><input type="hidden" name="monat" value="<?= $monat_param ?>"><input type="hidden" name="person_filter" value="<?= he2($person) ?>"><input type="hidden" name="status" value="0"><button type="submit" class="btn btn-ghost btn-xs">↩ Zurück</button></form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>

<?php elseif ($tab === 'uebersicht'): ?>
<!-- ════ ÜBERSICHT ════ -->

<?php
$monate_ue = [];
for ($i = 9; $i >= 0; $i--) $monate_ue[] = date('Y-m', strtotime("first day of -$i month"));
for ($i = 1; $i <= 3; $i++)  $monate_ue[] = date('Y-m', strtotime("first day of +$i month"));
$monat_dates_ue = array_map(function($m) { return $m.'-01'; }, $monate_ue);

$alle_status_ue = [];
if (!empty($zahlungen)) {
    $zids = implode(',', array_column($zahlungen, 'id'));
    $in   = implode(',', array_fill(0, count($monat_dates_ue), '?'));
    $stmt = $db->prepare("SELECT zahlung_id, monat, status FROM checkliste_status WHERE monat IN ($in) AND zahlung_id IN ($zids)");
    $stmt->execute($monat_dates_ue);
    foreach ($stmt->fetchAll() as $r) { $mk=substr($r['monat'],0,7); $alle_status_ue[$r['zahlung_id']][$mk]=(int)$r['status']; }
}

$alle_status_m_ue = [];
if (!empty($mieten)) {
    $mids = implode(',', array_column($mieten, 'id'));
    $in   = implode(',', array_fill(0, count($monat_dates_ue), '?'));
    $stmt = $db->prepare("SELECT miete_id, monat, status FROM mieten_status WHERE monat IN ($in) AND miete_id IN ($mids)");
    $stmt->execute($monat_dates_ue);
    foreach ($stmt->fetchAll() as $r) { $mk=substr($r['monat'],0,7); $alle_status_m_ue[$r['miete_id']][$mk]=(int)$r['status']; }
}

$start_z = 0;
foreach ($monate_ue as $idx => $m) {
    if ($m > date('Y-m')) break;
    foreach ($zahlungen as $z) { if (($alle_status_ue[$z['id']][$m]??0)!==1){$start_z=$idx;break 2;} }
}
$sichtbar_z = array_slice($monate_ue, $start_z);

$start_m = 0;
foreach ($monate_ue as $idx => $m) {
    if ($m > date('Y-m')) break;
    foreach ($mieten as $mi) { if (($alle_status_m_ue[$mi['id']][$m]??0)!==1){$start_m=$idx;break 2;} }
}
$sichtbar_m = array_slice($monate_ue, $start_m);

$monate_kurz = ['01'=>'Jan','02'=>'Feb','03'=>'Mrz','04'=>'Apr','05'=>'Mai','06'=>'Jun',
                '07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Dez'];
?>

<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">📊 Zahlungsübersicht</h2><span class="badge badge-neutral"><?= count($sichtbar_z) ?> Monate</span></div>
    <div class="table-wrap"><table class="data-table chk-overview-table">
        <thead><tr><th class="chk-col-label">Zahlung</th>
        <?php foreach ($sichtbar_z as $m): [$y,$mo]=explode('-',$m); ?><th><?= $monate_kurz[$mo] ?> <?= substr($y,2) ?></th><?php endforeach; ?>
        </tr></thead>
        <tbody>
        <?php foreach ($zahlungen as $z): ?>
        <tr><td class="chk-col-label"><?= he2($z['bezeichnung']) ?></td>
        <?php foreach ($sichtbar_z as $m):
            $st=$alle_status_ue[$z['id']][$m]??-1; $is_future=$m>date('Y-m');
            if ($st===1)                              {$cls='chk-cell--ok';$lbl='✓';}
            elseif ($st===3)                          {$cls='chk-cell--warn';$lbl='?';}
            elseif ($is_future&&($st===-1||$st===0))  {$cls='chk-cell--neutral';$lbl='–';}
            else                                      {$cls='chk-cell--open';$lbl='✕';}
        ?><td class="chk-cell <?= $cls ?>"><?= $lbl ?></td><?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>

<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">📊 Mieteinnahmen-Übersicht</h2><span class="badge badge-neutral"><?= count($sichtbar_m) ?> Monate</span></div>
    <div class="table-wrap"><table class="data-table chk-overview-table">
        <thead><tr><th class="chk-col-label">Mieteinnahme</th>
        <?php foreach ($sichtbar_m as $m): [$y,$mo]=explode('-',$m); ?><th><?= $monate_kurz[$mo] ?> <?= substr($y,2) ?></th><?php endforeach; ?>
        </tr></thead>
        <tbody>
        <?php foreach ($mieten as $mi): ?>
        <tr><td class="chk-col-label"><?= he2($mi['bezeichnung']) ?></td>
        <?php foreach ($sichtbar_m as $m):
            $st=$alle_status_m_ue[$mi['id']][$m]??-1; $is_future=$m>date('Y-m');
            if ($st===1)                              {$cls='chk-cell--ok';$lbl='✓';}
            elseif ($st===2)                          {$cls='chk-cell--warn';$lbl='!';}
            elseif ($is_future&&($st===-1||$st===0))  {$cls='chk-cell--neutral';$lbl='–';}
            else                                      {$cls='chk-cell--open';$lbl='✕';}
        ?><td class="chk-cell <?= $cls ?>"><?= $lbl ?></td><?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>

<?php elseif ($tab === 'verwaltung'): ?>
<!-- ════ VERWALTUNG ════ -->

<?php
$turnusse_chk = ['Monatlich','Quartalsweise','Jährlich'];
$typen_chk    = ['Kaltmiete','Warmmiete','Garage','Sonstiges'];
function sel_chk(array $opts, string $cur): string {
    $out = '';
    foreach ($opts as $o) $out .= '<option value="'.htmlspecialchars($o,ENT_QUOTES).'"'.($o===$cur?' selected':'').'>'.htmlspecialchars($o,ENT_QUOTES).'</option>';
    return $out;
}
// Alle Zahlungen für Verwaltung (nicht person-gefiltert) laden
global $db, $uid;
$alle_zahlungen = $db->prepare('SELECT * FROM checkliste_zahlungen WHERE user_id=? AND aktiv=1 ORDER BY sort_order');
$alle_zahlungen->execute([$uid]); $alle_zahlungen = $alle_zahlungen->fetchAll();
$alle_mieten = $db->prepare('SELECT * FROM mieten_checkliste WHERE user_id=? AND aktiv=1 ORDER BY sort_order');
$alle_mieten->execute([$uid]); $alle_mieten = $alle_mieten->fetchAll();
?>

<form id="frm-zv-bulk" method="POST" action="?page=checkliste">
    <?= csrf_field() ?><input type="hidden" name="act" value="zahlungen_bulk_save"><input type="hidden" name="person_filter" value="<?= he2($person) ?>">
    <?php foreach ($alle_zahlungen as $z): ?><input type="hidden" name="ids[]" value="<?= $z['id'] ?>"><?php endforeach; ?>
</form>

<div class="card mt-4" id="card-z-bulk">
    <div class="card-head">
        <div class="card-head-left">
            <h2 class="card-title">💳 Zahlungen verwalten</h2>
            <span class="card-sum"><?= count($alle_zahlungen) ?> Einträge · <?= fmt_chk(array_sum(array_column($alle_zahlungen,'betrag'))) ?>/Mon.</span>
        </div>
        <div class="bulk-bar">
            <button type="button" class="btn btn-ghost btn-sm" id="btn-edit-zv">✏ Bearbeiten</button>
            <button type="submit" form="frm-zv-bulk" class="btn btn-primary btn-sm btn-hidden" id="btn-save-zv">✓ Speichern</button>
        </div>
    </div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th class="col-sort"></th><th>Bezeichnung</th><th>Betrag</th><th>Person</th><th>Kategorie</th><th>Turnus</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($alle_zahlungen as $z): $zid=$z['id']; ?>
        <tr>
            <td class="col-sort"><?= sort_btns_z($zid) ?></td>
            <td><span class="ft-bulk"><?= he2($z['bezeichnung']) ?></span><input class="inline-input fi-bulk" form="frm-zv-bulk" name="rows[<?= $zid ?>][bezeichnung]" value="<?= he2($z['bezeichnung']) ?>"></td>
            <td><span class="ft-bulk fw-700"><?= fmt_chk((float)$z['betrag']) ?></span><input class="inline-input fi-bulk input-narrow" form="frm-zv-bulk" name="rows[<?= $zid ?>][betrag]" value="<?= he2(number_format((float)$z['betrag'],2,',','.')) ?>"></td>
            <td>
                <span class="ft-bulk"><?= he2($z['person']) ?></span>
                <select class="inline-input fi-bulk" form="frm-zv-bulk" name="rows[<?= $zid ?>][person]"><?= sel_chk($person_options, $z['person']) ?></select>
            </td>
            <td><span class="ft-bulk"><span class="badge badge-neutral"><?= he2($z['kategorie']??'–') ?></span></span><input class="inline-input fi-bulk" form="frm-zv-bulk" name="rows[<?= $zid ?>][kategorie]" value="<?= he2($z['kategorie']??'') ?>"></td>
            <td><span class="ft-bulk"><?= he2($z['turnus']) ?></span><select class="inline-input fi-bulk" form="frm-zv-bulk" name="rows[<?= $zid ?>][turnus]"><?= sel_chk($turnusse_chk,$z['turnus']) ?></select></td>
            <td class="col-actions">
                <form id="frm-del-z-<?= $zid ?>" method="POST" action="?page=checkliste" hidden><?= csrf_field() ?><input type="hidden" name="act" value="zahlung_delete"><input type="hidden" name="id" value="<?= $zid ?>"><input type="hidden" name="person_filter" value="<?= he2($person) ?>"></form>
                <button type="submit" form="frm-del-z-<?= $zid ?>" class="btn btn-danger btn-xs fi-bulk btn-delete-confirm">✕</button>
            </td>
        </tr>
        <?php endforeach; ?>
        <tr class="new-row-label"><td colspan="7"><span class="new-label">Neue Zahlung</span></td></tr>
        <tr class="new-row">
            <td></td>
            <td><input class="inline-input new-input" form="frm-z-new" name="bezeichnung" placeholder="Bezeichnung" required></td>
            <td><input class="inline-input new-input input-narrow" form="frm-z-new" name="betrag" placeholder="0,00"></td>
            <td><select class="inline-input new-input" form="frm-z-new" name="person"><?= sel_chk($person_options,$def_person) ?></select></td>
            <td><input class="inline-input new-input" form="frm-z-new" name="kategorie" placeholder="z.B. Wohnen"></td>
            <td><select class="inline-input new-input" form="frm-z-new" name="turnus"><?= sel_chk($turnusse_chk,'Monatlich') ?></select></td>
            <td class="col-actions">
                <form id="frm-z-new" method="POST" action="?page=checkliste" hidden><?= csrf_field() ?><input type="hidden" name="act" value="zahlung_create"><input type="hidden" name="person_filter" value="<?= he2($person) ?>"></form>
                <button type="button" class="btn btn-primary btn-xs" id="btn-new-z">+ Hinzufügen</button>
            </td>
        </tr>
        </tbody>
    </table></div>
</div>

<form id="frm-mv-bulk" method="POST" action="?page=checkliste">
    <?= csrf_field() ?><input type="hidden" name="act" value="mieten_bulk_save"><input type="hidden" name="person_filter" value="<?= he2($person) ?>">
    <?php foreach ($alle_mieten as $m): ?><input type="hidden" name="ids[]" value="<?= $m['id'] ?>"><?php endforeach; ?>
</form>

<div class="card mt-4" id="card-m-bulk">
    <div class="card-head">
        <div class="card-head-left">
            <h2 class="card-title">🏠 Mieteinnahmen verwalten</h2>
            <span class="card-sum"><?= count($alle_mieten) ?> Einträge</span>
        </div>
        <div class="bulk-bar">
            <button type="button" class="btn btn-ghost btn-sm" id="btn-edit-mv">✏ Bearbeiten</button>
            <button type="submit" form="frm-mv-bulk" class="btn btn-primary btn-sm btn-hidden" id="btn-save-mv">✓ Speichern</button>
        </div>
    </div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th class="col-sort"></th><th>Bezeichnung</th><th>Typ</th><th>Person</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($alle_mieten as $m): $mid=$m['id']; ?>
        <tr>
            <td class="col-sort"><?= sort_btns_m($mid) ?></td>
            <td><span class="ft-bulk"><?= he2($m['bezeichnung']) ?></span><input class="inline-input fi-bulk" form="frm-mv-bulk" name="rows[<?= $mid ?>][bezeichnung]" value="<?= he2($m['bezeichnung']) ?>"></td>
            <td><span class="ft-bulk"><span class="badge badge-neutral"><?= he2($m['typ']) ?></span></span><select class="inline-input fi-bulk" form="frm-mv-bulk" name="rows[<?= $mid ?>][typ]"><?= sel_chk($typen_chk,$m['typ']) ?></select></td>
            <td>
                <span class="ft-bulk"><?= he2($m['person']??'Beide') ?></span>
                <select class="inline-input fi-bulk" form="frm-mv-bulk" name="rows[<?= $mid ?>][person]"><?= sel_chk($person_options,$m['person']??$def_person) ?></select>
            </td>
            <td class="col-actions">
                <form id="frm-del-m-<?= $mid ?>" method="POST" action="?page=checkliste" hidden><?= csrf_field() ?><input type="hidden" name="act" value="miete_delete"><input type="hidden" name="id" value="<?= $mid ?>"><input type="hidden" name="person_filter" value="<?= he2($person) ?>"></form>
                <button type="submit" form="frm-del-m-<?= $mid ?>" class="btn btn-danger btn-xs fi-bulk btn-delete-confirm">✕</button>
            </td>
        </tr>
        <?php endforeach; ?>
        <tr class="new-row-label"><td colspan="5"><span class="new-label">Neue Mieteinnahme</span></td></tr>
        <tr class="new-row">
            <td></td>
            <td><input class="inline-input new-input" form="frm-mc-new" name="bezeichnung" placeholder="z.B. Hannover" required></td>
            <td><select class="inline-input new-input" form="frm-mc-new" name="typ"><?= sel_chk($typen_chk,'Kaltmiete') ?></select></td>
            <td><select class="inline-input new-input" form="frm-mc-new" name="person"><?= sel_chk($person_options,$def_person) ?></select></td>
            <td class="col-actions">
                <form id="frm-mc-new" method="POST" action="?page=checkliste" hidden><?= csrf_field() ?><input type="hidden" name="act" value="miete_create"><input type="hidden" name="immobilien_id" value="0"><input type="hidden" name="person_filter" value="<?= he2($person) ?>"></form>
                <button type="button" class="btn btn-primary btn-xs" id="btn-new-mc">+ Hinzufügen</button>
            </td>
        </tr>
        </tbody>
    </table></div>
</div>

<?php endif; ?>