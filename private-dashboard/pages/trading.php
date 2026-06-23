<?php
// ════════════════════════════════════════════════
// trading.php – Trading Journal
// user_id Filter: alle Queries auf eingeloggten User beschränkt
// ════════════════════════════════════════════════
$db  = get_db();
$uid = current_user_id();
$tab = $_GET['tab'] ?? 'uebersicht';

// ── Tabelle anlegen falls nicht vorhanden ──
$db->exec("CREATE TABLE IF NOT EXISTS trading_trades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL DEFAULT 0,
    datum DATE NOT NULL,
    symbol VARCHAR(20) NOT NULL DEFAULT 'XAUUSD',
    richtung ENUM('Long','Short') NOT NULL,
    einstieg DECIMAL(10,5) NOT NULL,
    ausstieg DECIMAL(10,5),
    lots DECIMAL(10,3) NOT NULL DEFAULT 0.01,
    gewinn DECIMAL(10,2),
    status ENUM('Offen','Geschlossen') DEFAULT 'Offen',
    strategie VARCHAR(100),
    notiz TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// user_id Spalte nachrüsten falls alte Tabelle ohne
(function() use ($db, $uid) {
    try {
        $has = $db->query("SHOW COLUMNS FROM trading_trades LIKE 'user_id'")->rowCount();
        if (!$has) {
            $db->exec("ALTER TABLE trading_trades ADD COLUMN user_id INT NOT NULL DEFAULT 0 AFTER id");
            $db->exec("UPDATE trading_trades SET user_id=$uid WHERE user_id=0");
        }
    } catch (PDOException $e) {}
})();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';

    if ($act === 'save') {
        $datum  = $_POST['datum'] ?? date('Y-m-d');
        $symbol = trim($_POST['symbol'] ?? 'XAUUSD');
        $richt  = $_POST['richtung'] ?? 'Long';
        $ein    = str_replace(',', '.', $_POST['einstieg'] ?? '0');
        $aus    = ($_POST['ausstieg'] ?? '') !== '' ? str_replace(',', '.', $_POST['ausstieg']) : null;
        $lots   = str_replace(',', '.', $_POST['lots'] ?? '0.01');
        $gewinn = ($_POST['gewinn'] ?? '') !== '' ? str_replace(',', '.', $_POST['gewinn']) : null;
        $status = $_POST['status'] ?? 'Offen';
        $strat  = trim($_POST['strategie'] ?? '');
        $notiz  = trim($_POST['notiz'] ?? '');
        $id     = (int)($_POST['edit_id'] ?? 0);
        if ($id > 0) {
            $db->prepare("UPDATE trading_trades SET datum=?,symbol=?,richtung=?,einstieg=?,ausstieg=?,lots=?,gewinn=?,status=?,strategie=?,notiz=? WHERE id=? AND user_id=?")
               ->execute([$datum,$symbol,$richt,$ein,$aus,$lots,$gewinn,$status,$strat,$notiz,$id,$uid]);
        } else {
            $db->prepare("INSERT INTO trading_trades (user_id,datum,symbol,richtung,einstieg,ausstieg,lots,gewinn,status,strategie,notiz) VALUES (?,?,?,?,?,?,?,?,?,?,?)")
               ->execute([$uid,$datum,$symbol,$richt,$ein,$aus,$lots,$gewinn,$status,$strat,$notiz]);
        }
        header("Location: ?page=trading&msg=saved"); exit;
    }

    if ($act === 'delete') {
        $db->prepare("DELETE FROM trading_trades WHERE id=? AND user_id=?")->execute([(int)$_POST['id'], $uid]);
        header("Location: ?page=trading&msg=saved"); exit;
    }
}
if (defined('HANDLE_POST_ONLY')) return;

$s = $db->prepare("SELECT * FROM trading_trades WHERE user_id=? ORDER BY datum DESC");
$s->execute([$uid]); $trades = $s->fetchAll();

$geschlossen = []; $offen_trades = [];
foreach ($trades as $t) {
    if ($t['status'] === 'Geschlossen') $geschlossen[] = $t;
    else $offen_trades[] = $t;
}
$wins = []; $losses = [];
foreach ($geschlossen as $t) {
    if ((float)$t['gewinn'] > 0) $wins[] = $t;
    else $losses[] = $t;
}
$gesamt_gew = array_sum(array_column($geschlossen, 'gewinn'));
$winrate    = count($geschlossen) > 0 ? count($wins) / count($geschlossen) * 100 : 0;

function he_tr(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function fmt_tr(float $v, bool $sign = true): string {
    $s = number_format(abs($v), 2, ',', '.') . ' €';
    if (!$sign) return $s;
    return ($v >= 0 ? '+' : '–') . $s;
}
?>

<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">Gespeichert.</div><?php endif; ?>

<div class="tab-bar">
    <a href="?page=trading&tab=uebersicht" class="tab-link <?= $tab==='uebersicht'?'active':'' ?>">Übersicht</a>
    <a href="?page=trading&tab=trades"     class="tab-link <?= $tab==='trades'?'active':'' ?>">Trades</a>
    <a href="?page=trading&tab=neu"        class="tab-link <?= $tab==='neu'?'active':'' ?>">+ Neuer Trade</a>
</div>

<?php if ($tab === 'uebersicht'): ?>
<!-- ════ ÜBERSICHT ════ -->
<div class="kpi-grid kpi-grid--4 mt-4">
    <div class="kpi-card <?= $gesamt_gew>=0?'kpi-card--info':'kpi-card--alert' ?>">
        <div class="kpi-label">Gesamtgewinn</div>
        <div class="kpi-value kpi-value--md <?= $gesamt_gew>=0?'text-green':'text-red' ?>"><?= fmt_tr($gesamt_gew) ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Winrate</div>
        <div class="kpi-value kpi-value--md"><?= number_format($winrate,1,',','.') ?>%</div>
        <div class="kpi-sub"><?= count($wins) ?> W / <?= count($losses) ?> L</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Trades gesamt</div>
        <div class="kpi-value kpi-value--md"><?= count($geschlossen) ?></div>
        <div class="kpi-sub"><?= count($offen_trades) ?> offen</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Ø Gewinn/Trade</div>
        <div class="kpi-value kpi-value--md <?= count($geschlossen)>0&&$gesamt_gew/count($geschlossen)>=0?'text-green':'text-red' ?>">
            <?= count($geschlossen)>0?fmt_tr($gesamt_gew/count($geschlossen)):'–' ?>
        </div>
    </div>
</div>

<?php if (!empty($offen_trades)): ?>
<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Offene Positionen</h2><span class="badge badge-warning"><?= count($offen_trades) ?> offen</span></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Datum</th><th>Symbol</th><th>Richtung</th><th>Einstieg</th><th>Lots</th><th>Strategie</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($offen_trades as $t): ?>
        <tr>
            <td><?= date('d.m.Y',strtotime($t['datum'])) ?></td>
            <td><span class="badge badge-neutral"><?= he_tr($t['symbol']) ?></span></td>
            <td><span class="badge <?= $t['richtung']==='Long'?'badge-ok':'badge-danger' ?>"><?= $t['richtung'] ?></span></td>
            <td><?= number_format((float)$t['einstieg'],2,',','.') ?></td>
            <td><?= $t['lots'] ?></td>
            <td><?= he_tr($t['strategie']??'–') ?></td>
            <td class="col-actions">
                <form method="POST" action="?page=trading" class="form-inline"><?= csrf_field() ?>
                    <input type="hidden" name="act" value="delete"><input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-xs btn-delete-confirm">✕</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>

<?php elseif ($tab === 'trades'): ?>
<!-- ════ ALLE TRADES ════ -->
<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Alle Trades</h2><span class="badge badge-neutral"><?= count($trades) ?> gesamt</span></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Datum</th><th>Symbol</th><th>Richtung</th><th>Einstieg</th><th>Ausstieg</th><th>Lots</th><th class="col-right">Gewinn</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($trades as $t): ?>
        <tr>
            <td><?= date('d.m.Y',strtotime($t['datum'])) ?></td>
            <td><span class="badge badge-neutral"><?= he_tr($t['symbol']) ?></span></td>
            <td><span class="badge <?= $t['richtung']==='Long'?'badge-ok':'badge-danger' ?>"><?= $t['richtung'] ?></span></td>
            <td><?= number_format((float)$t['einstieg'],2,',','.') ?></td>
            <td><?= $t['ausstieg']?number_format((float)$t['ausstieg'],2,',','.'):'–' ?></td>
            <td><?= $t['lots'] ?></td>
            <td class="col-right fw-700 <?= (float)($t['gewinn']??0)>=0?'text-green':'text-red' ?>">
                <?= $t['gewinn']!==null?fmt_tr((float)$t['gewinn']):'–' ?>
            </td>
            <td><span class="badge <?= $t['status']==='Offen'?'badge-warning':'badge-ok' ?>"><?= $t['status'] ?></span></td>
            <td class="col-actions">
                <form method="POST" action="?page=trading" class="form-inline"><?= csrf_field() ?>
                    <input type="hidden" name="act" value="delete"><input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-xs btn-delete-confirm">✕</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($trades)): ?><tr><td colspan="9" class="empty-state">Noch keine Trades erfasst.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div>

<?php elseif ($tab === 'neu'): ?>
<!-- ════ NEUER TRADE ════ -->
<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Neuer Trade</h2></div>
    <form method="POST" action="?page=trading">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="save">
        <input type="hidden" name="edit_id" value="0">
        <div class="form-grid">
            <div class="form-group"><label>Datum</label><input type="date" name="datum" value="<?= date('Y-m-d') ?>"></div>
            <div class="form-group"><label>Symbol</label>
                <select name="symbol"><option>XAUUSD</option><option>EURUSD</option><option>GBPUSD</option><option>Sonstiges</option></select>
            </div>
            <div class="form-group"><label>Richtung</label>
                <select name="richtung"><option>Long</option><option>Short</option></select>
            </div>
            <div class="form-group"><label>Einstieg</label><input type="text" name="einstieg" placeholder="0.00" required></div>
            <div class="form-group"><label>Ausstieg</label><input type="text" name="ausstieg" placeholder="0.00"></div>
            <div class="form-group"><label>Lots</label><input type="text" name="lots" placeholder="0.01" value="0.01"></div>
            <div class="form-group"><label>Gewinn/Verlust €</label><input type="text" name="gewinn" placeholder="0.00"></div>
            <div class="form-group"><label>Status</label>
                <select name="status"><option>Offen</option><option>Geschlossen</option></select>
            </div>
            <div class="form-group"><label>Strategie</label><input type="text" name="strategie" placeholder="z.B. Grid EA"></div>
            <div class="form-group fg-wide"><label>Notiz</label><textarea name="notiz" rows="2"></textarea></div>
        </div>
        <div class="form-actions form-actions--pad">
            <button type="submit" class="btn btn-primary">Trade speichern</button>
        </div>
    </form>
</div>
<?php endif; ?>