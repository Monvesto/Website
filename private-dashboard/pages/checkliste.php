<?php
$db  = get_db();
$tab = $_GET['tab'] ?? 'zahlungen';

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
    $act = $_POST['act'] ?? '';
    $mo  = $_POST['monat'] ?? $monat_param;

    if ($act === 'set_status') {
        $stmt = $db->prepare('INSERT INTO checkliste_status (zahlung_id, monat, status)
            VALUES (?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status)');
        $stmt->execute([(int)$_POST['zahlung_id'], $mo.'-01', (int)$_POST['status']]);
        header("Location: ?page=checkliste&tab=zahlungen&monat=$mo"); exit;
    }

    if ($act === 'set_miete_status') {
        $stmt = $db->prepare('INSERT INTO mieten_status (miete_id, monat, status)
            VALUES (?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status)');
        $stmt->execute([(int)$_POST['miete_id'], $mo.'-01', (int)$_POST['status']]);
        header("Location: ?page=checkliste&tab=mieten&monat=$mo"); exit;
    }

    if ($act === 'zahlung_create') {
        $bez = trim($_POST['bezeichnung'] ?? '');
        $bet = str_replace(',','.',$_POST['betrag'] ?? '0');
        $per = $_POST['person'] ?? 'Marcel';
        $kat = trim($_POST['kategorie'] ?? '');
        $tur = $_POST['turnus'] ?? 'Monatlich';
        if ($bez !== '') {
            $max = $db->query('SELECT COALESCE(MAX(sort_order),0)+1 FROM checkliste_zahlungen')->fetchColumn();
            $db->prepare('INSERT INTO checkliste_zahlungen (bezeichnung,betrag,person,kategorie,turnus,sort_order) VALUES (?,?,?,?,?,?)')->execute([$bez,$bet,$per,$kat,$tur,$max]);
        }
        header("Location: ?page=checkliste&tab=zahlungen&monat=$monat_param"); exit;
    }

    if ($act === 'zahlung_delete') {
        $db->prepare('DELETE FROM checkliste_zahlungen WHERE id=?')->execute([(int)$_POST['id']]);
        $db->prepare('DELETE FROM checkliste_status WHERE zahlung_id=?')->execute([(int)$_POST['id']]);
        header("Location: ?page=checkliste&tab=zahlungen&monat=$monat_param"); exit;
    }
}

if (defined('HANDLE_POST_ONLY')) return;

$zahlungen = $db->query('SELECT * FROM checkliste_zahlungen WHERE aktiv=1 ORDER BY sort_order')->fetchAll();
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

$mieten = $db->query('SELECT * FROM mieten_checkliste WHERE aktiv=1 ORDER BY sort_order')->fetchAll();
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
?>

<!-- TABS + MONATSNAVIGATION -->
<div class="tab-bar">
    <a href="?page=checkliste&tab=zahlungen&monat=<?= $monat_param ?>"    class="tab-link <?= $tab==='zahlungen'?'active':'' ?>">💳 Zahlungen</a>
    <a href="?page=checkliste&tab=mieten&monat=<?= $monat_param ?>"       class="tab-link <?= $tab==='mieten'?'active':'' ?>">🏠 Mieteinnahmen</a>
    <a href="?page=checkliste&tab=uebersicht"                              class="tab-link <?= $tab==='uebersicht'?'active':'' ?>">📊 Übersicht</a>
</div>
<?php if ($tab !== 'uebersicht'): ?>
<div class="chk-monat-nav">
    <a href="?page=checkliste&tab=<?= $tab ?>&monat=<?= $prev_monat ?>" class="btn btn-ghost btn-sm">‹</a>
    <span class="monat-label"><?= $monat_label ?></span>
    <a href="?page=checkliste&tab=<?= $tab ?>&monat=<?= $next_monat ?>" class="btn btn-ghost btn-sm">›</a>
</div>
<?php endif; ?>

<?php if ($tab === 'zahlungen'): ?>

<div class="kpi-grid kpi-grid--4 mt-4">
    <div class="kpi-card">
        <div class="kpi-label">Offen</div>
        <div class="kpi-value"><?= count($z_offen) ?></div>
    </div>
    <div class="kpi-card <?= count($z_bezahlt)===count($zahlungen)&&count($zahlungen)>0?'kpi-card--info':'' ?>">
        <div class="kpi-label">Bezahlt</div>
        <div class="kpi-value text-green"><?= count($z_bezahlt) ?></div>
    </div>
    <div class="kpi-card <?= count($z_klaerung)>0?'kpi-card--alert':'' ?>">
        <div class="kpi-label">In Klärung</div>
        <div class="kpi-value <?= count($z_klaerung)>0?'text-red':'' ?>"><?= count($z_klaerung) ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Gesamtbetrag</div>
        <div class="kpi-value kpi-value--md"><?= fmt_chk(array_sum(array_column($zahlungen,'betrag'))) ?></div>
    </div>
</div>

<?php if (!empty($z_offen)): ?>
<div class="card mt-4">
    <div class="card-head card-head--amber">
        <h2 class="card-title">Offene Zahlungen – <?= $monat_label ?></h2>
        <span class="badge badge-warning"><?= count($z_offen) ?> offen</span>
    </div>
    <div class="table-wrap">
        <table class="data-table data-table--compact">
            <thead><tr><th>Bezeichnung</th><th>Betrag</th><th>Person</th><th>Turnus</th><th>Aktionen</th></tr></thead>
            <tbody>
            <?php foreach ($z_offen as $z): ?>
            <tr>
                <td><?= he2($z['bezeichnung']) ?></td>
                <td class="fw-700"><?= fmt_chk((float)$z['betrag']) ?></td>
                <td><?= he2($z['person']) ?></td>
                <td><span class="badge badge-neutral"><?= he2($z['turnus']) ?></span></td>
                <td class="actions-cell">
                    <form method="POST" action="?page=checkliste" class="form-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="set_status">
                        <input type="hidden" name="zahlung_id" value="<?= $z['id'] ?>">
                        <input type="hidden" name="monat" value="<?= $monat_param ?>">
                        <input type="hidden" name="status" value="1">
                        <button type="submit" class="btn btn-ok btn-xs">✓ Bezahlt</button>
                    </form>
                    <form method="POST" action="?page=checkliste" class="form-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="set_status">
                        <input type="hidden" name="zahlung_id" value="<?= $z['id'] ?>">
                        <input type="hidden" name="monat" value="<?= $monat_param ?>">
                        <input type="hidden" name="status" value="3">
                        <button type="submit" class="btn btn-ghost btn-xs">? Klärung</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="card mt-4">
    <div class="card-head card-head--green">
        <h2 class="card-title">Offene Zahlungen – <?= $monat_label ?></h2>
    </div>
    <p class="empty-state">✓ Alle Zahlungen erledigt!</p>
</div>
<?php endif; ?>

<?php if (!empty($z_klaerung)): ?>
<div class="card mt-4">
    <div class="card-head card-head--red">
        <h2 class="card-title">In Klärung</h2>
        <span class="badge badge-warning"><?= count($z_klaerung) ?></span>
    </div>
    <div class="table-wrap">
        <table class="data-table data-table--compact">
            <thead><tr><th>Bezeichnung</th><th>Betrag</th><th>Person</th><th>Aktionen</th></tr></thead>
            <tbody>
            <?php foreach ($z_klaerung as $z): ?>
            <tr>
                <td><?= he2($z['bezeichnung']) ?></td>
                <td class="fw-700"><?= fmt_chk((float)$z['betrag']) ?></td>
                <td><?= he2($z['person']) ?></td>
                <td class="actions-cell">
                    <form method="POST" action="?page=checkliste" class="form-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="set_status">
                        <input type="hidden" name="zahlung_id" value="<?= $z['id'] ?>">
                        <input type="hidden" name="monat" value="<?= $monat_param ?>">
                        <input type="hidden" name="status" value="1">
                        <button type="submit" class="btn btn-ok btn-xs">✓ Bezahlt</button>
                    </form>
                    <form method="POST" action="?page=checkliste" class="form-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="set_status">
                        <input type="hidden" name="zahlung_id" value="<?= $z['id'] ?>">
                        <input type="hidden" name="monat" value="<?= $monat_param ?>">
                        <input type="hidden" name="status" value="0">
                        <button type="submit" class="btn btn-ghost btn-xs">↩ Zurück</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($z_bezahlt)): ?>
<div class="card mt-4">
    <div class="card-head card-head--green">
        <h2 class="card-title">Erledigte Zahlungen</h2>
        <span class="badge badge-ok"><?= count($z_bezahlt) ?> bezahlt</span>
    </div>
    <div class="table-wrap">
        <table class="data-table data-table--compact">
            <thead><tr><th>Bezeichnung</th><th>Betrag</th><th>Person</th><th>Aktionen</th></tr></thead>
            <tbody>
            <?php foreach ($z_bezahlt as $z): ?>
            <tr class="row-done">
                <td><?= he2($z['bezeichnung']) ?></td>
                <td class="fw-700"><?= fmt_chk((float)$z['betrag']) ?></td>
                <td><?= he2($z['person']) ?></td>
                <td class="actions-cell">
                    <form method="POST" action="?page=checkliste" class="form-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="set_status">
                        <input type="hidden" name="zahlung_id" value="<?= $z['id'] ?>">
                        <input type="hidden" name="monat" value="<?= $monat_param ?>">
                        <input type="hidden" name="status" value="0">
                        <button type="submit" class="btn btn-ghost btn-xs">↩ Offen</button>
                    </form>
                    <form method="POST" action="?page=checkliste" class="form-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="set_status">
                        <input type="hidden" name="zahlung_id" value="<?= $z['id'] ?>">
                        <input type="hidden" name="monat" value="<?= $monat_param ?>">
                        <input type="hidden" name="status" value="3">
                        <button type="submit" class="btn btn-ghost btn-xs">? Klärung</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="card mt-4">
    <div class="card-head">
        <h2 class="card-title">Neue Zahlung hinzufügen</h2>
    </div>
    <div class="table-wrap">
        <table class="data-table data-table--compact">
            <thead><tr><th>Bezeichnung</th><th>Betrag</th><th>Person</th><th>Kategorie</th><th>Turnus</th><th></th></tr></thead>
            <tbody>
            <tr class="new-row">
                <td><input class="inline-input new-input" form="frm-z-new" name="bezeichnung" placeholder="z.B. Miete" required></td>
                <td><input class="inline-input new-input input-narrow" form="frm-z-new" name="betrag" placeholder="0,00"></td>
                <td><select class="inline-input new-input" form="frm-z-new" name="person"><option>Marcel</option><option>Kim</option><option>Beide</option></select></td>
                <td><input class="inline-input new-input" form="frm-z-new" name="kategorie" placeholder="z.B. Wohnen"></td>
                <td><select class="inline-input new-input" form="frm-z-new" name="turnus"><option>Monatlich</option><option>Quartalsweise</option><option>Jährlich</option></select></td>
                <td class="col-actions">
                    <form id="frm-z-new" method="POST" action="?page=checkliste" class="form-hidden">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="zahlung_create">
                    </form>
                    <button type="button" class="btn btn-primary btn-xs" id="btn-new-z">+ Hinzufügen</button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($tab === 'mieten'): ?>

<div class="kpi-grid kpi-grid--4 mt-4">
    <div class="kpi-card">
        <div class="kpi-label">Offen</div>
        <div class="kpi-value <?= count($m_offen)>0?'text-red':'text-green' ?>"><?= count($m_offen) ?></div>
    </div>
    <div class="kpi-card <?= count($m_verspaetet)>0?'kpi-card--alert':'' ?>">
        <div class="kpi-label">Verspätet</div>
        <div class="kpi-value <?= count($m_verspaetet)>0?'text-red':'' ?>"><?= count($m_verspaetet) ?></div>
    </div>
    <div class="kpi-card <?= count($m_bezahlt)===count($mieten)&&count($mieten)>0?'kpi-card--info':'' ?>">
        <div class="kpi-label">Eingegangen</div>
        <div class="kpi-value text-green"><?= count($m_bezahlt) ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Gesamt</div>
        <div class="kpi-value kpi-value--md"><?= count($mieten) ?></div>
    </div>
</div>

<?php if (!empty($m_offen)): ?>
<div class="card mt-4">
    <div class="card-head card-head--amber">
        <h2 class="card-title">Ausstehend – <?= $monat_label ?></h2>
        <span class="badge badge-warning"><?= count($m_offen) ?> ausstehend</span>
    </div>
    <div class="table-wrap">
        <table class="data-table data-table--compact">
            <thead><tr><th>Objekt / Mieter</th><th>Typ</th><th>Aktionen</th></tr></thead>
            <tbody>
            <?php foreach ($m_offen as $m): ?>
            <tr>
                <td><?= he2($m['bezeichnung']) ?></td>
                <td><span class="badge badge-neutral"><?= he2($m['typ']) ?></span></td>
                <td class="actions-cell">
                    <form method="POST" action="?page=checkliste" class="form-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="set_miete_status">
                        <input type="hidden" name="miete_id" value="<?= $m['id'] ?>">
                        <input type="hidden" name="monat" value="<?= $monat_param ?>">
                        <input type="hidden" name="status" value="1">
                        <button type="submit" class="btn btn-ok btn-xs">✓ Eingegangen</button>
                    </form>
                    <form method="POST" action="?page=checkliste" class="form-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="set_miete_status">
                        <input type="hidden" name="miete_id" value="<?= $m['id'] ?>">
                        <input type="hidden" name="monat" value="<?= $monat_param ?>">
                        <input type="hidden" name="status" value="2">
                        <button type="submit" class="btn btn-danger btn-xs">! Verspätet</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="card mt-4">
    <div class="card-head card-head--green">
        <h2 class="card-title">Ausstehend – <?= $monat_label ?></h2>
    </div>
    <p class="empty-state">✓ Alle Mieteinnahmen eingegangen!</p>
</div>
<?php endif; ?>

<?php if (!empty($m_verspaetet)): ?>
<div class="card mt-4">
    <div class="card-head card-head--red">
        <h2 class="card-title">Verspätet</h2>
        <span class="badge badge-danger"><?= count($m_verspaetet) ?></span>
    </div>
    <div class="table-wrap">
        <table class="data-table data-table--compact">
            <thead><tr><th>Objekt / Mieter</th><th>Typ</th><th>Aktionen</th></tr></thead>
            <tbody>
            <?php foreach ($m_verspaetet as $m): ?>
            <tr>
                <td><?= he2($m['bezeichnung']) ?></td>
                <td><span class="badge badge-danger"><?= he2($m['typ']) ?></span></td>
                <td class="actions-cell">
                    <form method="POST" action="?page=checkliste" class="form-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="set_miete_status">
                        <input type="hidden" name="miete_id" value="<?= $m['id'] ?>">
                        <input type="hidden" name="monat" value="<?= $monat_param ?>">
                        <input type="hidden" name="status" value="1">
                        <button type="submit" class="btn btn-ok btn-xs">✓ Eingegangen</button>
                    </form>
                    <form method="POST" action="?page=checkliste" class="form-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="set_miete_status">
                        <input type="hidden" name="miete_id" value="<?= $m['id'] ?>">
                        <input type="hidden" name="monat" value="<?= $monat_param ?>">
                        <input type="hidden" name="status" value="0">
                        <button type="submit" class="btn btn-ghost btn-xs">↩ Zurück</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($m_bezahlt)): ?>
<div class="card mt-4">
    <div class="card-head card-head--green">
        <h2 class="card-title">Eingegangen</h2>
        <span class="badge badge-ok"><?= count($m_bezahlt) ?> eingegangen</span>
    </div>
    <div class="table-wrap">
        <table class="data-table data-table--compact">
            <thead><tr><th>Objekt / Mieter</th><th>Typ</th><th>Aktionen</th></tr></thead>
            <tbody>
            <?php foreach ($m_bezahlt as $m): ?>
            <tr class="row-done">
                <td><?= he2($m['bezeichnung']) ?></td>
                <td><span class="badge badge-ok"><?= he2($m['typ']) ?></span></td>
                <td class="actions-cell">
                    <form method="POST" action="?page=checkliste" class="form-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="act" value="set_miete_status">
                        <input type="hidden" name="miete_id" value="<?= $m['id'] ?>">
                        <input type="hidden" name="monat" value="<?= $monat_param ?>">
                        <input type="hidden" name="status" value="0">
                        <button type="submit" class="btn btn-ghost btn-xs">↩ Zurück</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>

<?php if ($tab === 'uebersicht'):
    $monate = [];
        for ($i = 9; $i >= 0; $i--) {
            $monate[] = date('Y-m', strtotime("first day of -$i month"));
        }
        for ($i = 1; $i <= 3; $i++) {
            $monate[] = date('Y-m', strtotime("first day of +$i month"));
        }
    $monat_dates = [];
    foreach ($monate as $m) $monat_dates[] = $m . '-01';
    $in = implode(',', array_fill(0, count($monat_dates), '?'));
    $alle_zahlungen = $db->query('SELECT * FROM checkliste_zahlungen WHERE aktiv=1 ORDER BY sort_order')->fetchAll();
    $alle_status = [];
    if (!empty($alle_zahlungen)) {
        $zids = implode(',', array_column($alle_zahlungen, 'id'));
        $stmt = $db->prepare("SELECT zahlung_id, monat, status FROM checkliste_status WHERE monat IN ($in) AND zahlung_id IN ($zids)");
        $stmt->execute($monat_dates);
        foreach ($stmt->fetchAll() as $r) {
            $mk = substr($r['monat'], 0, 7);
            $alle_status[$r['zahlung_id']][$mk] = (int)$r['status'];
        }
    }
    // Ersten Monat mit unerledigtem Eintrag finden
    $start_idx = 0;
    foreach ($monate as $idx => $m) {
        if ($m > date('Y-m')) break; // Zukunft immer zeigen
        foreach ($alle_zahlungen as $z) {
            if (($alle_status[$z['id']][$m] ?? 0) !== 1) { $start_idx = $idx; break 2; }
        }
    }
    $sichtbare_monate = array_slice($monate, $start_idx);
    $monate_kurz = ['01'=>'Jan','02'=>'Feb','03'=>'Mrz','04'=>'Apr','05'=>'Mai','06'=>'Jun',
                    '07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Dez'];
?>
<div class="card mt-4">
    <div class="card-head">
        <h2 class="card-title">Zahlungsübersicht</h2>
        <span class="badge badge-neutral"><?= count($sichtbare_monate) ?> Monate</span>
    </div>
    <div class="table-wrap">
        <table class="data-table chk-overview-table">
            <thead>
                <tr>
                    <th class="chk-col-label">Zahlung</th>
                    <?php foreach ($sichtbare_monate as $m):
                        [$y, $mo] = explode('-', $m); ?>
                    <th class="chk-col-month"><?= $monate_kurz[$mo] ?> <?= substr($y,2) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($alle_zahlungen as $z): ?>
            <tr>
                <td class="chk-col-label"><?= he2($z['bezeichnung']) ?></td>
                <?php foreach ($sichtbare_monate as $m):
                    $st = $alle_status[$z['id']][$m] ?? -1;
                    $is_future = $m > date('Y-m');
                    if ($st === 1)              { $cls = 'chk-cell--ok';      $lbl = '✓'; }
                    elseif ($st === 3)          { $cls = 'chk-cell--warn';    $lbl = '?'; }
                    elseif ($is_future && $st === -1) { $cls = 'chk-cell--neutral'; $lbl = '–'; }
                    else                        { $cls = 'chk-cell--open';    $lbl = '✕'; }
                ?>
                <td class="chk-cell <?= $cls ?>"><?= $lbl ?></td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>