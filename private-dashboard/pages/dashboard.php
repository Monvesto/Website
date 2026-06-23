<?php
// ════════════════════════════════════════════════
// dashboard.php – Übersichts-Dashboard
// Alle Queries gefiltert nach user_id + Person-Profil
// ════════════════════════════════════════════════
$db  = get_db();
$uid = current_user_id();

// ── Person-Filter aus URL, validiert gegen User-Profile ──
$person_options = get_person_options();
$person = $_GET['person'] ?? ($person_options[0] ?? 'Marcel');
if (!in_array($person, $person_options, true)) $person = $person_options[0] ?? 'Marcel';
$is_all = person_is_all($person);

// ── Hilfsfunktion: Summe einer Tabelle gefiltert nach user_id + person ──
function db_sum(PDO $db, string $table, bool $is_all, string $person, int $uid, string $extra = ''): float {
    if ($is_all) {
        $s = $db->prepare("SELECT COALESCE(SUM(betrag),0) FROM $table WHERE user_id=? AND aktiv=1 $extra");
        $s->execute([$uid]);
    } else {
        $s = $db->prepare("SELECT COALESCE(SUM(betrag),0) FROM $table WHERE user_id=? AND person=? AND aktiv=1 $extra");
        $s->execute([$uid, $person]);
    }
    return (float)$s->fetchColumn();
}

$einnahmen   = db_sum($db, 'einnahmen', $is_all, $person, $uid);
$ausgaben    = db_sum($db, 'ausgaben',  $is_all, $person, $uid);
$ueberschuss = $einnahmen - $ausgaben;
$sparquote   = $einnahmen > 0 ? $ueberschuss / $einnahmen : 0;

// ── Immo-Cashflow ──
if ($is_all) {
    $s = $db->prepare("SELECT COALESCE(SUM(kaltmiete + nebenkosten - fixkosten - kreditkosten),0) FROM immobilien WHERE user_id=? AND aktiv=1");
    $s->execute([$uid]);
} else {
    $s = $db->prepare("SELECT COALESCE(SUM(kaltmiete + nebenkosten - fixkosten - kreditkosten),0) FROM immobilien WHERE user_id=? AND aktiv=1 AND (person=? OR person='Beide')");
    $s->execute([$uid, $person]);
}
$immo_cashflow = (float)$s->fetchColumn();

// ── Investments ──
if ($is_all) {
    $s = $db->prepare("SELECT COALESCE(SUM(betrag),0) FROM investments WHERE user_id=? AND DATE_FORMAT(datum,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m')");
    $s->execute([$uid]); $investments_monat = (float)$s->fetchColumn();
    $s = $db->prepare("SELECT COALESCE(SUM(betrag),0) FROM investments WHERE user_id=?");
    $s->execute([$uid]); $investments_gesamt = (float)$s->fetchColumn();
    $s = $db->prepare("SELECT bereich, SUM(betrag) as gesamt FROM investments WHERE user_id=? GROUP BY bereich ORDER BY gesamt DESC");
    $s->execute([$uid]); $invest_bereiche = $s->fetchAll();
} else {
    $s = $db->prepare("SELECT COALESCE(SUM(betrag),0) FROM investments WHERE user_id=? AND DATE_FORMAT(datum,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m') AND (person=? OR person='Beide')");
    $s->execute([$uid,$person]); $investments_monat = (float)$s->fetchColumn();
    $s = $db->prepare("SELECT COALESCE(SUM(betrag),0) FROM investments WHERE user_id=? AND (person=? OR person='Beide')");
    $s->execute([$uid,$person]); $investments_gesamt = (float)$s->fetchColumn();
    $s = $db->prepare("SELECT bereich, SUM(betrag) as gesamt FROM investments WHERE user_id=? AND (person=? OR person='Beide') GROUP BY bereich ORDER BY gesamt DESC");
    $s->execute([$uid,$person]); $invest_bereiche = $s->fetchAll();
}
$invest_total = array_sum(array_column($invest_bereiche, 'gesamt'));

// ── Schulden ──
if ($is_all) {
    $s = $db->prepare("SELECT COALESCE(SUM(restsumme),0) FROM verbindlichkeiten WHERE user_id=?");
    $s->execute([$uid]);
} else {
    $s = $db->prepare("SELECT COALESCE(SUM(restsumme),0) FROM verbindlichkeiten WHERE user_id=? AND (person=? OR person='Beide')");
    $s->execute([$uid,$person]);
}
$schulden_gesamt = (float)$s->fetchColumn();

// ── Ziele ──
if ($is_all) {
    $s = $db->prepare("SELECT * FROM ziele WHERE user_id=? ORDER BY zieltermin ASC");
    $s->execute([$uid]);
} else {
    $s = $db->prepare("SELECT * FROM ziele WHERE user_id=? AND (person=? OR person='Beide') ORDER BY zieltermin ASC");
    $s->execute([$uid,$person]);
}
$ziele = $s->fetchAll();

// ── Aufgaben ──
if ($is_all) {
    $s = $db->prepare("SELECT COUNT(*) FROM tasks WHERE user_id=? AND status='Offen'");
    $s->execute([$uid]); $open_tasks = (int)$s->fetchColumn();
    $s = $db->prepare("SELECT COUNT(*) FROM tasks WHERE user_id=? AND status='Offen' AND due_date < CURDATE()");
    $s->execute([$uid]); $overdue_tasks = (int)$s->fetchColumn();
    $s = $db->prepare("SELECT task, category, priority, due_date FROM tasks WHERE user_id=? AND status='Offen' ORDER BY due_date ASC LIMIT 5");
    $s->execute([$uid]); $next_tasks = $s->fetchAll();
} else {
    $s = $db->prepare("SELECT COUNT(*) FROM tasks WHERE user_id=? AND status='Offen' AND (person=? OR person='Beide')");
    $s->execute([$uid,$person]); $open_tasks = (int)$s->fetchColumn();
    $s = $db->prepare("SELECT COUNT(*) FROM tasks WHERE user_id=? AND status='Offen' AND due_date < CURDATE() AND (person=? OR person='Beide')");
    $s->execute([$uid,$person]); $overdue_tasks = (int)$s->fetchColumn();
    $s = $db->prepare("SELECT task, category, priority, due_date FROM tasks WHERE user_id=? AND status='Offen' AND (person=? OR person='Beide') ORDER BY due_date ASC LIMIT 5");
    $s->execute([$uid,$person]); $next_tasks = $s->fetchAll();
}

// ── Wartungen ──
$db->prepare("UPDATE maintenance SET status = CASE
    WHEN next_due < CURDATE() THEN 'Überfällig'
    WHEN next_due >= CURDATE() AND next_due <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Bald fällig'
    ELSE 'OK' END WHERE next_due IS NOT NULL AND user_id=?")->execute([$uid]);

if ($is_all) {
    $s = $db->prepare("SELECT COUNT(*) FROM maintenance WHERE user_id=? AND status='Überfällig'");
    $s->execute([$uid]); $overdue_maint = (int)$s->fetchColumn();
    $s = $db->prepare("SELECT object_name, task, next_due, status FROM maintenance WHERE user_id=? AND next_due IS NOT NULL ORDER BY next_due ASC LIMIT 5");
    $s->execute([$uid]); $next_maint_all = $s->fetchAll();
} else {
    $s = $db->prepare("SELECT COUNT(*) FROM maintenance WHERE user_id=? AND status='Überfällig' AND (person=? OR person='Beide')");
    $s->execute([$uid,$person]); $overdue_maint = (int)$s->fetchColumn();
    $s = $db->prepare("SELECT object_name, task, next_due, status FROM maintenance WHERE user_id=? AND next_due IS NOT NULL AND (person=? OR person='Beide') ORDER BY next_due ASC LIMIT 5");
    $s->execute([$uid,$person]); $next_maint_all = $s->fetchAll();
}

// ── Hilfsfunktionen ──
function fmt(float $v, bool $sign = false): string {
    $s = number_format(abs($v), 2, ',', '.');
    if ($sign) return ($v >= 0 ? '+' : '–') . $s . ' €';
    return ($v < 0 ? '–' : '') . $s . ' €';
}
function pct(float $v): string { return number_format($v * 100, 1, ',', '.') . '%'; }
function pct_raw(float $v): string { return number_format($v * 100, 1, '.', ''); }
function progress_class(float $p): string {
    if ($p >= 0.75) return 'progress-green';
    if ($p >= 0.4)  return 'progress-amber';
    return 'progress-red';
}
function days_left(?string $d): string {
    if (!$d) return '';
    $diff = (strtotime($d) - strtotime('today')) / 86400;
    if ($diff < 0)  return '<span class="date-overdue">überfällig</span>';
    if ($diff == 0) return '<span class="date-overdue">heute</span>';
    if ($diff <= 7) return '<span class="date-soon">' . (int)$diff . ' Tage</span>';
    return '<span class="text-muted">' . date('d.m.Y', strtotime($d)) . '</span>';
}
function priority_badge(string $p): string {
    $map = ['Hoch'=>'badge-danger','Mittel'=>'badge-warning','Niedrig'=>'badge-neutral'];
    return '<span class="badge '.($map[$p]??'badge-neutral').'">'.htmlspecialchars($p).'</span>';
}
function status_badge_m(string $s): string {
    $map = ['OK'=>'badge-ok','Bald fällig'=>'badge-warning','Überfällig'=>'badge-danger'];
    return '<span class="badge '.($map[$s]??'badge-neutral').'">'.htmlspecialchars($s).'</span>';
}
function format_date(?string $d): string {
    if (!$d) return '<span class="text-muted">–</span>';
    $ts = strtotime($d); $diff = ($ts - strtotime('today')) / 86400; $fmt = date('d.m.Y', $ts);
    if ($diff < 0)  return '<span class="date-overdue">'.$fmt.'</span>';
    if ($diff <= 7) return '<span class="date-soon">'.$fmt.'</span>';
    return $fmt;
}
$bereich_icons = ['Grid EA'=>'📈','Affiliate'=>'🔗','P2P'=>'💸','Tagesgeld'=>'🏦','Krypto'=>'₿','Copy Trading'=>'📊'];
?>

<!-- Person-Switcher aus User-Profilen -->
<div class="dashboard-person-bar">
    <div class="person-switcher">
        <?php foreach ($person_options as $p): ?>
        <a href="?page=dashboard&person=<?= $p ?>" class="person-btn <?= $person===$p?'active':'' ?>"><?= $p ?></a>
        <?php endforeach; ?>
    </div>
</div>

<div class="kpi-grid kpi-grid--6">
    <div class="kpi-card">
        <div class="kpi-label">📥 Einnahmen<?= !$is_all?' '.$person:'' ?></div>
        <div class="kpi-value kpi-value--md text-green"><?= fmt($einnahmen) ?></div>
        <div class="kpi-sub">monatlich</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">📤 Ausgaben<?= !$is_all?' '.$person:'' ?></div>
        <div class="kpi-value kpi-value--md text-red"><?= fmt($ausgaben) ?></div>
        <div class="kpi-sub">monatlich</div>
    </div>
    <div class="kpi-card <?= $ueberschuss>=0?'kpi-card--info':'kpi-card--alert' ?>">
        <div class="kpi-label">💰 Überschuss</div>
        <div class="kpi-value kpi-value--md <?= $ueberschuss>=0?'text-green':'text-red' ?>"><?= fmt($ueberschuss, true) ?></div>
        <div class="kpi-sub">Sparquote <?= pct($sparquote) ?></div>
    </div>
    <div class="kpi-card <?= $immo_cashflow>=0?'':'kpi-card--alert' ?>">
        <div class="kpi-label">🏠 Immo-Cashflow</div>
        <div class="kpi-value kpi-value--md <?= $immo_cashflow>=0?'text-green':'text-red' ?>"><?= fmt($immo_cashflow, true) ?></div>
        <div class="kpi-sub">monatlich</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">📈 Investments</div>
        <div class="kpi-value kpi-value--md"><?= fmt($investments_monat) ?></div>
        <div class="kpi-sub">Gesamt <?= fmt($investments_gesamt) ?></div>
    </div>
    <div class="kpi-card kpi-card--alert">
        <div class="kpi-label">🏦 Schulden</div>
        <div class="kpi-value kpi-value--md text-red"><?= fmt($schulden_gesamt) ?></div>
        <div class="kpi-sub"><a href="?page=finanzen&tab=schulden" class="link-subtle">Details →</a></div>
    </div>
</div>

<div class="dashboard-row mt-4">
    <div class="card">
        <div class="card-head">
            <div>
                <h2 class="card-title">✅ Aufgaben</h2>
                <div class="badge-row">
                    <span class="badge badge-neutral"><?= $open_tasks ?> offen</span>
                    <?php if ($overdue_tasks > 0): ?><span class="badge badge-danger"><?= $overdue_tasks ?> überfällig</span><?php endif; ?>
                </div>
            </div>
            <a href="?page=tasks" class="btn btn-primary btn-sm">+ Neu</a>
        </div>
        <?php if (empty($next_tasks)): ?>
            <p class="empty-state">Keine offenen Aufgaben.</p>
        <?php else: ?>
        <div class="table-wrap"><table class="data-table">
            <thead><tr><th>Aufgabe</th><th>Priorität</th><th>Fällig</th></tr></thead>
            <tbody>
            <?php foreach ($next_tasks as $t): ?>
            <tr><td><?= htmlspecialchars($t['task']) ?></td><td><?= priority_badge($t['priority']) ?></td><td><?= format_date($t['due_date']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <h2 class="card-title">🔧 Wartungen</h2>
                <?php if ($overdue_maint > 0): ?>
                <div class="badge-row"><span class="badge badge-danger"><?= $overdue_maint ?> überfällig</span></div>
                <?php endif; ?>
            </div>
            <a href="?page=maintenance" class="btn btn-primary btn-sm">+ Neu</a>
        </div>
        <?php if (empty($next_maint_all)): ?>
            <p class="empty-state">Keine Wartungen.</p>
        <?php else: ?>
        <div class="table-wrap"><table class="data-table">
            <thead><tr><th>Objekt</th><th>Aufgabe</th><th>Fällig</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($next_maint_all as $m): ?>
            <tr><td><?= htmlspecialchars($m['object_name']) ?></td><td><?= htmlspecialchars($m['task']) ?></td><td><?= format_date($m['next_due']) ?></td><td><?= status_badge_m($m['status']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>
</div>

<div class="dashboard-row mt-4">
    <div class="card">
        <div class="card-head">
            <h2 class="card-title">🎯 Ziele & Fortschritt</h2>
            <a href="?page=ziele" class="link-subtle">Alle bearbeiten →</a>
        </div>
        <div class="table-wrap"><table class="data-table">
            <thead><tr>
                <th>Ziel</th><th>Kategorie</th><th>Fortschritt</th>
                <th class="col-right">Aktuell</th><th class="col-right">Zielwert</th><th>Termin</th>
            </tr></thead>
            <tbody>
            <?php foreach ($ziele as $z):
                $range = abs((float)$z['zielwert'] - (float)$z['startwert']);
                $curr  = abs((float)$z['aktueller_wert'] - (float)$z['startwert']);
                $prog  = $range > 0 ? min(1, $curr / $range) : 0;
                $pcls  = progress_class($prog);
                $pct_r = number_format($prog * 100, 1, '.', '');
            ?>
            <tr>
                <td><?= htmlspecialchars($z['ziel']) ?></td>
                <td><?php if ($z['kategorie']): ?><span class="badge badge-neutral"><?= htmlspecialchars($z['kategorie']) ?></span><?php endif; ?></td>
                <td>
                    <div class="progress-wrap">
                        <div class="progress-track"><div class="progress-fill <?= $pcls ?>" data-width="<?= $pct_r ?>"></div></div>
                        <span class="progress-label <?= $pcls ?>"><?= $pct_r ?>%</span>
                    </div>
                </td>
                <td class="col-right fw-700"><?= number_format((float)$z['aktueller_wert'],0,',','.') ?></td>
                <td class="col-right text-muted"><?= number_format((float)$z['zielwert'],0,',','.') ?></td>
                <td><?php if ($z['zieltermin']): ?><span class="<?= strtotime($z['zieltermin']) < time() ? 'date-overdue' : '' ?>"><?= days_left($z['zieltermin']) ?></span><?php endif; ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($ziele)): ?>
            <tr><td colspan="6" class="empty-state">Noch keine Ziele.</td></tr>
            <?php endif; ?>
            </tbody>
        </table></div>
    </div>

    <div class="card">
        <div class="card-head">
            <h2 class="card-title">📊 Investments nach Bereich</h2>
            <a href="?page=investments" class="link-subtle">Details →</a>
        </div>
        <div class="invest-list">
            <?php foreach ($invest_bereiche as $b):
                $anteil = $invest_total > 0 ? ($b['gesamt'] / $invest_total) : 0;
                $icon   = $bereich_icons[$b['bereich']] ?? '💼';
            ?>
            <div class="invest-row">
                <div class="invest-label"><span class="invest-icon"><?= $icon ?></span><span><?= htmlspecialchars($b['bereich']) ?></span></div>
                <div class="invest-bar-wrap"><div class="invest-bar" data-width="<?= pct_raw($anteil) ?>"></div></div>
                <div class="invest-value"><?= fmt((float)$b['gesamt']) ?></div>
            </div>
            <?php endforeach; ?>
            <?php if (!empty($invest_bereiche)): ?>
            <div class="invest-total"><span>Gesamt</span><span><?= fmt($invest_total) ?></span></div>
            <?php endif; ?>
            <?php if (empty($invest_bereiche)): ?><p class="empty-state">Keine Daten.</p><?php endif; ?>
        </div>
    </div>
</div>