<?php
$db     = get_db();
$person = $_GET['person'] ?? 'Marcel';
if (!in_array($person, ['Marcel','Kim','Beide'], true)) $person = 'Marcel';

function db_sum(PDO $db, string $table, string $person, string $extra = ''): float {
    if ($person === 'Beide') {
        return (float)$db->query("SELECT COALESCE(SUM(betrag),0) FROM $table WHERE aktiv=1 $extra")->fetchColumn();
    }
    $s = $db->prepare("SELECT COALESCE(SUM(betrag),0) FROM $table WHERE person=? AND aktiv=1 $extra");
    $s->execute([$person]);
    return (float)$s->fetchColumn();
}

$einnahmen = db_sum($db, 'einnahmen', $person, "AND turnus='Monatlich'");
$ausgaben  = db_sum($db, 'ausgaben',  $person, "AND turnus='Monatlich'");
$ueberschuss = $einnahmen - $ausgaben;
$sparquote   = $einnahmen > 0 ? $ueberschuss / $einnahmen : 0;

$immo_cashflow = (float)$db->query("SELECT COALESCE(SUM(kaltmiete + nebenkosten - fixkosten - kreditkosten),0) FROM immobilien WHERE aktiv=1")->fetchColumn();
$investments_monat  = (float)$db->query("SELECT COALESCE(SUM(betrag),0) FROM investments WHERE DATE_FORMAT(datum,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m')")->fetchColumn();
$investments_gesamt = (float)$db->query("SELECT COALESCE(SUM(betrag),0) FROM investments")->fetchColumn();
$schulden_gesamt    = (float)$db->query("SELECT COALESCE(SUM(restsumme),0) FROM verbindlichkeiten")->fetchColumn();

$invest_bereiche = $db->query("SELECT bereich, SUM(betrag) as gesamt FROM investments GROUP BY bereich ORDER BY gesamt DESC")->fetchAll();
$invest_total    = array_sum(array_column($invest_bereiche, 'gesamt'));

$open_tasks    = (int)$db->query("SELECT COUNT(*) FROM tasks WHERE status='Offen'")->fetchColumn();
$overdue_tasks = (int)$db->query("SELECT COUNT(*) FROM tasks WHERE status='Offen' AND due_date < CURDATE()")->fetchColumn();

$db->exec("UPDATE maintenance SET status = CASE
    WHEN next_due < CURDATE() THEN 'Überfällig'
    WHEN next_due >= CURDATE() AND next_due <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Bald fällig'
    ELSE 'OK' END WHERE next_due IS NOT NULL");
$overdue_maint  = (int)$db->query("SELECT COUNT(*) FROM maintenance WHERE status='Überfällig'")->fetchColumn();
$ziele          = $db->query("SELECT * FROM ziele ORDER BY zieltermin ASC")->fetchAll();
$next_tasks     = $db->query("SELECT task, category, priority, due_date FROM tasks WHERE status='Offen' ORDER BY due_date ASC LIMIT 5")->fetchAll();
$next_maint_all = $db->query("SELECT object_name, task, next_due, status FROM maintenance WHERE next_due IS NOT NULL ORDER BY next_due ASC LIMIT 5")->fetchAll();

function fmt(float $v, bool $sign = false): string {
    $s = number_format(abs($v), 2, ',', '.');
    if ($sign) return ($v >= 0 ? '+' : '–') . $s . ' €';
    return ($v < 0 ? '–' : '') . $s . ' €';
}
function pct(float $v): string { return number_format($v * 100, 1, ',', '.') . '%'; }
function pct_raw(float $v): string { return number_format($v * 100, 1, ',', '.'); }
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

<div class="dashboard-person-bar">
    <div class="person-switcher">
        <?php foreach (['Marcel','Kim','Beide'] as $p): ?>
        <a href="?page=dashboard&person=<?= $p ?>" class="person-btn <?= $person===$p?'active':'' ?>"><?= $p ?></a>
        <?php endforeach; ?>
    </div>
</div>

<div class="kpi-grid kpi-grid--6">
    <div class="kpi-card">
        <div class="kpi-label">📥 Einnahmen<?= $person!=='Beide'?' '.$person:'' ?></div>
        <div class="kpi-value kpi-value--md text-green"><?= fmt($einnahmen) ?></div>
        <div class="kpi-sub">monatlich</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">📤 Ausgaben<?= $person!=='Beide'?' '.$person:'' ?></div>
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
            <h2 class="card-title">🎯 Ziele & Fortschritt</h2>
            <a href="?page=ziele" class="link-subtle">Alle bearbeiten →</a>
        </div>
        <div class="goals-list">
            <?php foreach ($ziele as $z):
                $range = abs((float)$z['zielwert'] - (float)$z['startwert']);
                $curr  = abs((float)$z['aktueller_wert'] - (float)$z['startwert']);
                $prog  = $range > 0 ? min(1, $curr / $range) : 0;
                $pcls  = progress_class($prog);
            ?>
            <div class="goal-row">
                <div class="goal-header">
                    <div class="goal-name"><?= htmlspecialchars($z['ziel']) ?></div>
                    <div class="goal-meta">
                        <span class="goal-pct <?= $pcls ?>"><?= pct($prog) ?></span>
                        <?php if ($z['zieltermin']): ?><span class="goal-date"><?= days_left($z['zieltermin']) ?></span><?php endif; ?>
                    </div>
                </div>
                <div class="goal-bar-track">
                    <div class="goal-bar-fill <?= $pcls ?>" data-width="<?= pct_raw($prog) ?>"></div>
                </div>
                <div class="goal-values">
                    <span><?= number_format((float)$z['aktueller_wert'],0,',','.') ?></span>
                    <span class="text-muted">von <?= number_format((float)$z['zielwert'],0,',','.') ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($ziele)): ?><p class="empty-state">Noch keine Ziele.</p><?php endif; ?>
        </div>
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
            <a href="?page=tasks&action=new" class="btn btn-primary btn-sm">+ Neu</a>
        </div>
        <?php if (empty($next_tasks)): ?>
            <p class="empty-state">Keine offenen Aufgaben.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Aufgabe</th><th>Priorität</th><th>Fällig</th></tr></thead>
                <tbody>
                <?php foreach ($next_tasks as $t): ?>
                <tr><td><?= htmlspecialchars($t['task']) ?></td><td><?= priority_badge($t['priority']) ?></td><td><?= format_date($t['due_date']) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
            <a href="?page=maintenance&action=new" class="btn btn-primary btn-sm">+ Neu</a>
        </div>
        <?php if (empty($next_maint_all)): ?>
            <p class="empty-state">Keine Wartungen.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Objekt</th><th>Aufgabe</th><th>Fällig</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($next_maint_all as $m): ?>
                <tr><td><?= htmlspecialchars($m['object_name']) ?></td><td><?= htmlspecialchars($m['task']) ?></td><td><?= format_date($m['next_due']) ?></td><td><?= status_badge_m($m['status']) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>