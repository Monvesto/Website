<?php
// ════════════════════════════════════════════════
// pages/tradingergebnisse.php – Trading Tagesupdate
// Eingebunden über index.php (?page=tradingergebnisse)
// Nur für Admins zugänglich.
// ════════════════════════════════════════════════

if (!is_admin()) {
    echo '<div class="card"><div class="empty-state">Zugriff verweigert.</div></div>';
    return;
}
if (defined('HANDLE_POST_ONLY')) return;

$db = get_db();

$tradingStartDate = '2026-06-24';

// ── Hilfsfunktionen ───────────────────────────────────────────────────────────
function calcTradingDay(string $date): int
{
    $start  = new DateTime('2026-06-24');
    $target = new DateTime($date);
    $diff   = (int) $start->diff($target)->format('%r%a');
    return max(1, $diff + 1);
}

function calcCumulativeReturn(array $returns): ?float
{
    if (empty($returns)) return null;
    $factor = 1.0;
    foreach ($returns as $r) $factor *= (1 + $r / 100);
    return round(($factor - 1) * 100, 4);
}

function fmtReturn(?float $val): string
{
    if ($val === null) return '<span class="text-muted">–</span>';
    $cls  = $val >= 0 ? 'text-green' : 'text-red';
    $sign = $val >= 0 ? '+' : '';
    return '<span class="' . $cls . ' fw-700">'
         . $sign . number_format($val, 2, '.', '') . '%</span>';
}

function fmtReturnPlain(?float $val): string
{
    if ($val === null) return '–';
    return ($val >= 0 ? '+' : '') . number_format($val, 2, '.', '') . '%';
}

function fmtMoney(?float $val, string $currency = ''): string
{
    if ($val === null) return '–';
    return number_format($val, 2, '.', ',') . ($currency ? ' ' . $currency : '');
}

// ── Datum & Handelstag ────────────────────────────────────────────────────────
$today      = date('Y-m-d');
$tradingDay = calcTradingDay($today);
$lastMonday = date('Y-m-d', strtotime('monday this week'));
if ($lastMonday > $today) $lastMonday = date('Y-m-d', strtotime('monday last week'));

// ── Account-Einstellungen ─────────────────────────────────────────────────────
$settingsStmt = $db->prepare("
    SELECT account_key, label, start_balance, start_date, calc_basis, currency, myfxbook_id
    FROM trading_account_settings
");
$settingsStmt->execute();
$accountSettings = [];
foreach ($settingsStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $accountSettings[$row['account_key']] = $row;
}

// ── Letzter gespeicherter Kontostand je Konto (für Kontrollfeld) ─────────────
$stmtLastBal = $db->prepare("
    SELECT main_account_balance, ea_account_balance, challenge_account_balance
    FROM trading_daily_updates
    ORDER BY entry_date DESC LIMIT 1
");
$stmtLastBal->execute();
$lastBalances = $stmtLastBal->fetch(PDO::FETCH_ASSOC) ?: [];

// ── Statistiken ───────────────────────────────────────────────────────────────
$stmtAll = $db->prepare("
    SELECT entry_date, main_account_return, ea_account_return, challenge_account_return
    FROM trading_daily_updates ORDER BY entry_date ASC
");
$stmtAll->execute();
$allEntries = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

$buckets = [
    'main'      => ['all' => [], 'week' => []],
    'ea'        => ['all' => [], 'week' => []],
    'challenge' => ['all' => [], 'week' => []],
];
foreach ($allEntries as $row) {
    $d = $row['entry_date'];
    foreach (['main' => 'main_account_return', 'ea' => 'ea_account_return', 'challenge' => 'challenge_account_return'] as $k => $col) {
        if ($row[$col] !== null) {
            $v = (float) $row[$col];
            $buckets[$k]['all'][] = $v;
            if ($d >= $lastMonday) $buckets[$k]['week'][] = $v;
        }
    }
}
$stats = [];
foreach ($buckets as $k => $b) {
    $stats[$k] = ['all' => calcCumulativeReturn($b['all']), 'week' => calcCumulativeReturn($b['week'])];
}

// ── Letzte 10 Einträge ────────────────────────────────────────────────────────
$stmtLast = $db->prepare("
    SELECT id, entry_date, trading_day,
           main_account_return,  ea_account_return,  challenge_account_return,
           main_account_profit,  ea_account_profit,  challenge_account_profit,
           main_account_balance, ea_account_balance, challenge_account_balance,
           updated_at
    FROM trading_daily_updates ORDER BY entry_date DESC LIMIT 10
");
$stmtLast->execute();
$lastEntries = $stmtLast->fetchAll(PDO::FETCH_ASSOC);

$tradingBase = 'trading/';

$accounts = [
    'main'      => 'Main Account',
    'ea'        => 'Monvesto EA',
    'challenge' => 'Road to 100k',
];
$balanceCols = [
    'main'      => 'main_account_balance',
    'ea'        => 'ea_account_balance',
    'challenge' => 'challenge_account_balance',
];
?>

<!-- ── Meldung ────────────────────────────────────────────────────────────── -->
<div id="trading-message" hidden></div>

<!-- ── Statistik-Karten ──────────────────────────────────────────────────── -->
<div class="kpi-grid kpi-grid--4" id="trading-stats">
<?php foreach ($accounts as $key => $label):
    $allVal    = $stats[$key]['all'];
    $weekVal   = $stats[$key]['week'];
    $aCls      = ($allVal  ?? 0) >= 0 ? 'text-green' : 'text-red';
    $wCls      = ($weekVal ?? 0) >= 0 ? 'text-green' : 'text-red';
    $cfg       = $accountSettings[$key] ?? [];
    $startBal  = isset($cfg['start_balance']) && $cfg['start_balance'] !== null ? (float) $cfg['start_balance'] : null;
    $startDate = $cfg['start_date'] ?? null;
    $calcBasis = isset($cfg['calc_basis'])    && $cfg['calc_basis']    !== null ? (float) $cfg['calc_basis']    : null;
    $currency  = $cfg['currency']    ?? 'USD';
    $mfxId     = $cfg['myfxbook_id'] ?? '';
?>
    <div class="kpi-card">
        <!-- Karten-Header mit Bearbeiten-Button -->
        <div class="tr-kpi-head">
            <div class="kpi-label"><?= $label ?></div>
            <button class="btn btn-xs btn-ghost btn-edit-startbal"
                    type="button"
                    data-key="<?= $key ?>"
                    data-currency="<?= htmlspecialchars($currency) ?>"
                    data-mfxid="<?= htmlspecialchars($mfxId) ?>"
                    data-startbal="<?= $startBal ?? '' ?>"
                    data-startdate="<?= htmlspecialchars($startDate ?? '') ?>"
                    data-calcbasis="<?= $calcBasis ?? '' ?>"
                    title="Einstellungen bearbeiten">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="10" height="10">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
            </button>
        </div>

        <div class="kpi-value--md <?= $aCls ?>"><?= fmtReturnPlain($allVal) ?></div>
        <div class="kpi-sub">seit Start (Tag <?= $tradingDay ?>)</div>
        <div class="kpi-sub tr-week">
            Woche: <strong class="<?= $wCls ?>"><?= fmtReturnPlain($weekVal) ?></strong>
        </div>
        <?php if ($startBal): ?>
        <div class="kpi-sub tr-stat-start">
            <strong>Start:</strong> <?= fmtMoney($startBal, $currency) ?>
            <?= $startDate ? '(' . date('d.m.Y', strtotime($startDate)) . ')' : '' ?>
        </div>
        <?php endif; ?>
        <?php if ($calcBasis): ?>
        <div class="kpi-sub tr-stat-start">
            <strong>Akt. Berechnungsgrundlage:</strong> <?= fmtMoney($calcBasis, $currency) ?>
            <button class="btn btn-xs btn-ghost btn-edit-startbal"
                    type="button"
                    data-key="<?= $key ?>"
                    data-currency="<?= htmlspecialchars($currency) ?>"
                    data-mfxid="<?= htmlspecialchars($mfxId) ?>"
                    data-startbal="<?= $startBal ?? '' ?>"
                    data-startdate="<?= htmlspecialchars($startDate ?? '') ?>"
                    data-calcbasis="<?= $calcBasis ?? '' ?>"
                    data-mode="calcbasis"
                    title="Berechnungsgrundlage ändern">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="10" height="10">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
            </button>
        </div>
        <?php else: ?>
        <div class="kpi-sub tr-stat-start text-muted">
            Berechnungsgrundlage: nicht gesetzt
        </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
</div>

<!-- ── Formular-Card ─────────────────────────────────────────────────────── -->
<div class="card tr-form-card" id="trading-form-card">

    <div class="card-head">
        <span class="card-title" id="form-headline">Neuer Eintrag</span>
        <div class="tr-card-head-actions">
            <button class="btn btn-ghost btn-sm" id="btn-gd-test" type="button">GD-Test</button>
            <button class="btn btn-ghost btn-sm" id="btn-myfxbook" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="13" height="13">
                    <polyline points="1 4 1 10 7 10"/>
                    <path d="M3.51 15a9 9 0 1 0 .49-4.5"/>
                </svg>
                MyFxBook laden
            </button>
        </div>
    </div>

    <div id="tr-grid" class="tr-form-grid">

        <!-- Datum + Handelstag (Mobile: eigene Zeile) -->
        <div class="form-group tr-date-row-item">
            <label for="entry_date">Datum</label>
            <input type="date" id="entry_date" value="<?= $today ?>" max="<?= $today ?>">
        </div>

        <div class="form-group tr-date-row-item">
            <label>Handelstag</label>
            <input type="text" id="trading-day-display" value="Tag <?= $tradingDay ?>" disabled>
        </div>

        <?php
        $accountKeys   = ['main', 'ea', 'challenge'];
        $accountLabels = ['main' => 'Main Account', 'ea' => 'Monvesto EA', 'challenge' => 'Road to 100k'];
        foreach ($accountKeys as $key):
            $cfg       = $accountSettings[$key] ?? [];
            $startBal  = isset($cfg['start_balance']) && $cfg['start_balance'] !== null ? (float) $cfg['start_balance'] : null;
            $calcBasis = isset($cfg['calc_basis'])    && $cfg['calc_basis']    !== null ? (float) $cfg['calc_basis']    : null;
            $startDate = $cfg['start_date']  ?? null;
            $currency  = $cfg['currency']    ?? 'USD';
            $mfxId     = $cfg['myfxbook_id'] ?? '';
            $label     = $accountLabels[$key];
            $lastBal   = isset($lastBalances[$balanceCols[$key]]) && $lastBalances[$balanceCols[$key]] !== null
                         ? fmtMoney((float)$lastBalances[$balanceCols[$key]], $currency) : '–';
        ?>

        <!-- Konto: <?= $label ?> -->
        <div class="tr-account-block-wrap">
            <div class="tr-account-label"><?= $label ?></div>

            <!-- Basis-Zeile mit Bearbeiten-Button -->
            <div class="tr-startbal-row">
                <span>Basis:</span>
                <span id="calcbasis-display-<?= $key ?>" class="tr-startbal-val">
                    <?= $calcBasis ? fmtMoney($calcBasis, $currency) : '<span class="text-muted">–</span>' ?>
                </span>
                <button class="btn btn-xs btn-ghost btn-edit-startbal"
                        type="button"
                        data-key="<?= $key ?>"
                        data-currency="<?= htmlspecialchars($currency) ?>"
                        data-mfxid="<?= htmlspecialchars($mfxId) ?>"
                        data-startbal="<?= $startBal ?? '' ?>"
                        data-startdate="<?= htmlspecialchars($startDate ?? '') ?>"
                        data-calcbasis="<?= $calcBasis ?? '' ?>"
                        data-mode="calcbasis"
                        title="Berechnungsgrundlage ändern">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="10" height="10">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </button>
            </div>

            <!-- Gewinn + Rendite: Mobile 2-spaltig -->
            <div class="tr-account-fields">
                <div class="tr-field form-group">
                    <label class="tr-label-profit" for="<?= $key ?>_profit">Gewinn / Verlust (<?= $currency ?>)</label>
                    <input type="text" inputmode="decimal"
                           id="<?= $key ?>_profit"
                           data-account="<?= $key ?>" data-type="profit"
                           placeholder="z.B. 125.50">
                </div>
                <div class="tr-field form-group">
                    <label for="<?= $key ?>_return">Rendite (%)</label>
                    <input type="text" inputmode="decimal"
                           id="<?= $key ?>_return"
                           data-account="<?= $key ?>" data-type="return"
                           placeholder="z.B. 1.25">
                </div>
            </div>

            <!-- Kontostand: readonly, volle Breite, zur Kontrolle -->
            <div class="tr-account-balance form-group">
                <label for="<?= $key ?>_balance">Kontostand (<?= $currency ?>) – Kontrolle</label>
                <input type="text" id="<?= $key ?>_balance"
                       value="<?= $lastBal ?>" disabled placeholder="–">
            </div>

            <div id="open-positions-<?= $key ?>" class="tr-positions" hidden></div>
        </div>

        <?php endforeach; ?>

    </div><!-- /tr-form-grid -->

    <!-- Versteckte Felder -->
    <input type="hidden" id="edit_id" value="">
    <input type="hidden" id="force_update" value="0">
    <input type="hidden" id="main_open_json" value="">
    <input type="hidden" id="ea_open_json" value="">
    <input type="hidden" id="challenge_open_json" value="">

    <div class="form-actions form-actions--pad">
        <button class="btn btn-primary" id="btn-save" type="button">Speichern</button>
        <button class="btn btn-ghost btn-sm" id="btn-cancel-edit" type="button" hidden>Abbrechen</button>
        <label class="tr-checkbox-label">
            <input type="checkbox" id="chk-create-image" checked>
            Grafik erstellen
        </label>
    </div>

</div><!-- /card -->

<!-- ── Letzte Einträge ───────────────────────────────────────────────────── -->
<div class="card">
    <div class="card-head">
        <span class="card-title">Letzte Einträge</span>
    </div>
    <div class="table-wrap">
        <table class="data-table" id="trading-table">
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Tag</th>
                    <th>Main %</th>
                    <th class="tr-table-hide-mobile">Main €</th>
                    <th class="tr-table-hide-mobile">EA %</th>
                    <th class="tr-table-hide-mobile">EA €</th>
                    <th class="tr-table-hide-mobile">Challenge %</th>
                    <th class="tr-table-hide-mobile">Challenge €</th>
                    <th class="tr-table-hide-mobile">Geändert</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($lastEntries)): ?>
                <tr><td colspan="10" class="empty-state">Noch keine Einträge vorhanden.</td></tr>
            <?php else: ?>
                <?php foreach ($lastEntries as $row): ?>
                <tr data-id="<?= $row['id'] ?>"
                    data-date="<?= $row['entry_date'] ?>"
                    data-main-return="<?=      htmlspecialchars($row['main_account_return']        ?? '') ?>"
                    data-ea-return="<?=        htmlspecialchars($row['ea_account_return']          ?? '') ?>"
                    data-challenge-return="<?= htmlspecialchars($row['challenge_account_return']   ?? '') ?>"
                    data-main-profit="<?=      htmlspecialchars($row['main_account_profit']        ?? '') ?>"
                    data-ea-profit="<?=        htmlspecialchars($row['ea_account_profit']          ?? '') ?>"
                    data-challenge-profit="<?= htmlspecialchars($row['challenge_account_profit']   ?? '') ?>">
                    <td><?= date('d.m.Y', strtotime($row['entry_date'])) ?></td>
                    <td>Tag <?= (int) $row['trading_day'] ?></td>
                    <td><?= fmtReturn($row['main_account_return']      !== null ? (float) $row['main_account_return']      : null) ?></td>
                    <td class="text-muted tr-table-hide-mobile"><?= $row['main_account_profit']      !== null ? number_format((float) $row['main_account_profit'],      2, '.', ',') : '–' ?></td>
                    <td class="tr-table-hide-mobile"><?= fmtReturn($row['ea_account_return']        !== null ? (float) $row['ea_account_return']        : null) ?></td>
                    <td class="text-muted tr-table-hide-mobile"><?= $row['ea_account_profit']        !== null ? number_format((float) $row['ea_account_profit'],        2, '.', ',') : '–' ?></td>
                    <td class="tr-table-hide-mobile"><?= fmtReturn($row['challenge_account_return'] !== null ? (float) $row['challenge_account_return'] : null) ?></td>
                    <td class="text-muted tr-table-hide-mobile"><?= $row['challenge_account_profit'] !== null ? number_format((float) $row['challenge_account_profit'], 2, '.', ',') : '–' ?></td>
                    <td class="text-muted tr-table-hide-mobile"><?= date('d.m. H:i', strtotime($row['updated_at'])) ?></td>
                    <td class="col-actions">
                        <button class="btn btn-xs btn-ghost btn-edit-row" type="button">Bearbeiten</button>
                        <button class="btn btn-xs btn-ok btn-create-image" type="button"
                                data-id="<?= $row['id'] ?>"
                                data-date="<?= $row['entry_date'] ?>">Grafik</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Duplikat-Modal ─────────────────────────────────────────────────────── -->
<div id="trading-modal" hidden>
    <div id="confirm-backdrop"></div>
    <div id="confirm-box">
        <p id="modal-text" class="tr-modal-text"></p>
        <div id="confirm-btns">
            <button class="btn btn-ghost btn-sm" id="modal-cancel">Abbrechen</button>
            <button class="btn btn-primary btn-sm" id="modal-confirm">Überschreiben</button>
        </div>
    </div>
</div>

<!-- ── Einstellungen-Modal ────────────────────────────────────────────────── -->
<div id="startbal-modal" hidden>
    <div id="confirm-backdrop"></div>
    <div id="confirm-box" class="tr-modal-box">
        <h3 class="tr-modal-title" id="startbal-modal-title">Kontoeinstellungen</h3>

        <!-- Startsumme – nur sichtbar wenn noch nicht gesetzt -->
        <div id="startbal-field" class="form-group tr-modal-field">
            <label for="startbal-input">Startsumme (fix, einmalig)</label>
            <input type="text" inputmode="decimal" id="startbal-input" placeholder="z.B. 10000.00">
            <span class="form-hint">Einmalig setzen – ändert sich nicht automatisch.</span>
        </div>

        <!-- Startdatum – nur sichtbar wenn noch nicht gesetzt -->
        <div id="startdate-field" class="form-group tr-modal-field">
            <label for="startdate-input">Startdatum</label>
            <input type="date" id="startdate-input">
            <span class="form-hint">Datum des ersten Handelstags für dieses Konto.</span>
        </div>

        <!-- Berechnungsgrundlage – immer sichtbar -->
        <div class="form-group tr-modal-field">
            <label for="calcbasis-input">Berechnungsgrundlage (aktuell)</label>
            <input type="text" inputmode="decimal" id="calcbasis-input" placeholder="z.B. 12500.00">
            <span class="form-hint">Basis für %-Berechnung. Manuell anpassen wenn sich Kapital ändert.</span>
        </div>

        <!-- Währung – nur sichtbar wenn noch nicht gesetzt -->
        <div id="currency-field" class="form-group tr-modal-field">
            <label for="startbal-currency">Währung</label>
            <select id="startbal-currency">
                <option value="USD">USD</option>
                <option value="EUR">EUR</option>
                <option value="GBP">GBP</option>
            </select>
        </div>

        <!-- MyFxBook-ID – nur sichtbar wenn noch nicht gesetzt -->
        <div id="myfxbook-field" class="form-group tr-modal-field-last">
            <label for="startbal-myfxbook-id">MyFxBook Account-ID</label>
            <input type="text" id="startbal-myfxbook-id" placeholder="z.B. 123456">
            <span class="form-hint">Steht in der MyFxBook-URL deines Kontos</span>
        </div>

        <input type="hidden" id="startbal-account-key" value="">
        <div id="confirm-btns">
            <button class="btn btn-ghost btn-sm" id="startbal-cancel">Abbrechen</button>
            <button class="btn btn-primary btn-sm" id="startbal-save">Speichern</button>
        </div>
    </div>
</div>

<!-- ── Grafik-Modal ───────────────────────────────────────────────────────── -->
<div id="image-modal" hidden>
    <div id="image-backdrop"></div>
    <div id="confirm-box" class="tr-modal-box">
        <h3 class="tr-modal-title">Create Image</h3>

        <div class="form-group tr-modal-field">
            <label>Accounts</label>
            <select id="img-type">
                <option value="combined">All 3 Accounts (combined)</option>
                <option value="main">Main Account</option>
                <option value="ea">Monvesto EA</option>
                <option value="challenge">Road to 100k</option>
            </select>
        </div>

        <div class="form-group tr-modal-field-last">
            <label>Format</label>
            <select id="img-format">
                <option value="feed">Feed 1080×1080</option>
                <option value="story">Story 1080×1920</option>
            </select>
        </div>

        <input type="hidden" id="img-entry-id" value="">

        <div id="img-preview" class="tr-img-preview" hidden>
            <img id="img-preview-img" src="" alt="Preview" class="tr-img-preview-img">
            <div class="tr-img-preview-actions">
                <a id="img-download-link" href="#" class="btn btn-primary btn-sm" download>
                    Download PNG
                </a>
            </div>
        </div>

        <div id="confirm-btns">
            <button class="btn btn-ghost btn-sm" id="image-modal-cancel">Close</button>
            <button class="btn btn-primary btn-sm" id="image-modal-generate">Create</button>
        </div>
    </div>
</div>

<!-- PHP-Variablen als JSON (CSP-konform) -->
<script type="application/json" id="trading-config"><?php
echo json_encode([
    'base'         => $tradingBase,
    'tradingStart' => $tradingStartDate,
    'startBalances' => [
        'main'      => isset($accountSettings['main']['start_balance'])      && $accountSettings['main']['start_balance']      !== null ? (float) $accountSettings['main']['start_balance']      : null,
        'ea'        => isset($accountSettings['ea']['start_balance'])        && $accountSettings['ea']['start_balance']        !== null ? (float) $accountSettings['ea']['start_balance']        : null,
        'challenge' => isset($accountSettings['challenge']['start_balance']) && $accountSettings['challenge']['start_balance'] !== null ? (float) $accountSettings['challenge']['start_balance'] : null,
    ],
    'calcBases' => [
        'main'      => isset($accountSettings['main']['calc_basis'])      && $accountSettings['main']['calc_basis']      !== null ? (float) $accountSettings['main']['calc_basis']      : null,
        'ea'        => isset($accountSettings['ea']['calc_basis'])        && $accountSettings['ea']['calc_basis']        !== null ? (float) $accountSettings['ea']['calc_basis']        : null,
        'challenge' => isset($accountSettings['challenge']['calc_basis']) && $accountSettings['challenge']['calc_basis'] !== null ? (float) $accountSettings['challenge']['calc_basis'] : null,
    ],
]);
?></script>
<script src="assets/tradingergebnisse.js"></script>