<?php
// ════════════════════════════════════════════════
// pages/roboforex.php – RoboForex Partner Dashboard
// Zugänglich für Admin + Partner
// ════════════════════════════════════════════════

if (!is_partner()) {
    echo '<div class="card"><div class="empty-state">Zugriff verweigert.</div></div>';
    return;
}
if (defined('HANDLE_POST_ONLY')) return;

$db          = get_db();
$currentRole = get_current_role();
$currentUid  = uid();

// ── Partner-Konten laden – Admin sieht alle, Partner nur eigene ───────────────
if ($currentRole === 'admin') {
    $stmt = $db->prepare("SELECT * FROM roboforex_accounts WHERE active=1 ORDER BY sort_order ASC, id ASC");
    $stmt->execute();
} else {
    $stmt = $db->prepare("SELECT * FROM roboforex_accounts WHERE active=1 AND user_id=? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$currentUid]);
}
$rfAccounts   = $stmt->fetchAll(PDO::FETCH_ASSOC);
$firstAccount = $rfAccounts[0] ?? null;
?>

<div id="rf-message" hidden></div>

<!-- ── Header mit Konto-Auswahl ──────────────────────────────────────────── -->
<div class="card rf-header-card">
    <div class="card-head">
        <span class="card-title">RoboForex Partner <span class="rf-sync-time text-muted" id="rf-sync-time"></span></span>
        <div class="tr-card-head-actions">
            <?php if (empty($rfAccounts)): ?>
            <span class="badge badge--warning">Keine Konten konfiguriert</span>
            <?php else: ?>
            <select id="rf-account-select" class="input-sm">
                <?php foreach ($rfAccounts as $acc): ?>
                <option value="<?= htmlspecialchars($acc['account_id']) ?>">
                    <?= htmlspecialchars($acc['label'] ?: $acc['account_id']) ?>
                    (<?= htmlspecialchars($acc['account_id']) ?>)
                </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
            <button class="btn btn-ghost btn-sm" id="btn-rf-refresh" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="13" height="13">
                    <polyline points="1 4 1 10 7 10"/>
                    <path d="M3.51 15a9 9 0 1 0 .49-4.5"/>
                </svg>
                Aktualisieren
            </button>
        </div>
    </div>
</div>

<!-- ── Tabs ──────────────────────────────────────────────────────────────── -->
<div class="card rf-tabs-card">
    <div class="rf-tabs">
        <button class="rf-tab rf-tab--active" data-tab="overview">Übersicht</button>
        <button class="rf-tab" data-tab="clients">Clients</button>
        <button class="rf-tab" data-tab="tree">Partner-Baum</button>
        <button class="rf-tab" data-tab="commission">Provisionen</button>
        <?php if ($currentRole === 'admin'): ?>
        <button class="rf-tab" data-tab="settings">Konten</button>
        <?php elseif ($currentRole === 'partner'): ?>
        <button class="rf-tab" data-tab="settings">Konten</button>
        <?php endif; ?>
    </div>
</div>

<!-- ── Tab: Übersicht ─────────────────────────────────────────────────────── -->
<div id="rf-tab-overview" class="rf-tab-content">
    <!-- Client-Übersicht -->
    <div class="card">
        <div class="card-head"><span class="card-title">Partner-Übersicht</span></div>
        <div class="kpi-grid kpi-grid--4">
            <div class="kpi-card">
                <div class="kpi-label">Aktive Clients</div>
                <div class="kpi-value--md text-green" id="rf-active-clients">–</div>
                <div class="kpi-sub" id="rf-active-clients-sub">diesen Monat</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Kunden mit Einzahlungen</div>
                <div class="kpi-value--md text-green" id="rf-deposited-clients">–</div>
                <div class="kpi-sub">Einzahlung über Schwellenwert</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Neue Registrierungen</div>
                <div class="kpi-value--md text-green" id="rf-new-clients">–</div>
                <div class="kpi-sub" id="rf-new-clients-sub">diesen Monat</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Konten gesamt</div>
                <div class="kpi-value--md text-green" id="rf-total-clients">–</div>
                <div class="kpi-sub">alle Referral-Konten</div>
            </div>
        </div>
    </div>

    <!-- Provisionen Schnellübersicht -->
    <div class="card">
        <div class="card-head"><span class="card-title">Provisionen – Schnellübersicht</span></div>
        <div class="kpi-grid kpi-grid--4">
            <div class="kpi-card">
                <div class="kpi-label">Heute gutgeschrieben</div>
                <div class="kpi-value--md text-green" id="rf-commission-today">–</div>
                <div class="kpi-sub" id="rf-commission-today-date">Trades von gestern</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Für morgen vorgemerkt</div>
                <div class="kpi-value--md text-green" id="rf-commission-tomorrow">–</div>
                <div class="kpi-sub" id="rf-commission-tomorrow-date">Trades von heute</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Diese Woche <span class="text-muted">(seit Montag)</span></div>
                <div class="kpi-value--md text-green" id="rf-commission-week">–</div>
                <div class="kpi-sub" id="rf-commission-week-date">–</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label" id="rf-commission-month-label">Diesen Monat</div>
                <div class="kpi-value--md text-green" id="rf-commission-month">–</div>
                <div class="kpi-sub" id="rf-commission-month-date">–</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Gesamt (90 Tage)</div>
                <div class="kpi-value--md text-green" id="rf-commission-total">–</div>
                <div class="kpi-sub" id="rf-commission-total-date">–</div>
            </div>
        </div>
    </div>

    <!-- Provisionen nach Symbol -->
    <div class="card">
        <div class="card-head">
            <span class="card-title">Provisionen nach Symbol</span>
            <div class="tr-card-head-actions">
                <input type="date" id="rf-symbol-from" value="<?= date('Y-m-01') ?>">
                <span class="text-muted">bis</span>
                <input type="date" id="rf-symbol-to" value="<?= date('Y-m-d') ?>">
                <button class="btn btn-primary btn-sm" id="btn-rf-symbol-load">Laden</button>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data-table" id="rf-symbol-table">
                <thead>
                    <tr>
                        <th>Symbol</th>
                        <th>Trades</th>
                        <th>Volumen (Lot)</th>
                        <th>Provision (USD)</th>
                        <th>Ø pro Lot</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="5" class="empty-state">Zeitraum wählen und laden.</td></tr>
                </tbody>
            </table>
        </div>
        <div id="rf-symbol-total" class="rf-commission-summary" hidden></div>
    </div>
</div>

<!-- ── Tab: Clients ───────────────────────────────────────────────────────── -->
<div id="rf-tab-clients" class="rf-tab-content" hidden>
    <div class="card">
        <div class="card-head">
            <span class="card-title">Handelskonten in Partner-Gruppe</span>
            <div class="tr-card-head-actions">
                <input type="text" id="rf-clients-search" placeholder="Konto-Nr. oder Name suchen..." class="input-sm">
                <span class="text-muted" id="rf-clients-count"></span>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data-table" id="rf-clients-table">
                <thead>
                    <tr>
                        <th class="rf-sortable" data-col="client_account_id">Kontonummer <span class="rf-sort-icon">↕</span></th>
                        <th class="rf-sortable" data-col="label">Name <span class="rf-sort-icon">↕</span></th>
                        <th class="rf-sortable" data-col="account_type">Kontotyp <span class="rf-sort-icon">↕</span></th>
                        <th class="rf-sortable" data-col="reg_date">Registriert <span class="rf-sort-icon">↕</span></th>
                        <th class="rf-sortable" data-col="has_reached_deposit_threshold">Deposit-Schwelle <span class="rf-sort-icon">↕</span></th>
                        <th class="rf-sortable" data-col="is_active_accrual_of_commission">Provision aktiv <span class="rf-sort-icon">↕</span></th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="6" class="empty-state">Lade Daten...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="rf-pagination" id="rf-clients-pagination"></div>
    </div>
</div>

<!-- ── Tab: Partner-Baum ─────────────────────────────────────────────────── -->
<div id="rf-tab-tree" class="rf-tab-content" hidden>
    <div class="card">
        <div class="card-head">
            <span class="card-title">Partner-Baum</span>
            <div class="tr-card-head-actions">
                <input type="text" id="rf-tree-search" placeholder="Konto-Nr. suchen..." class="input-sm">
                <button class="btn btn-ghost btn-sm" id="btn-rf-tree-search">Suchen</button>
            </div>
        </div>
        <div id="rf-tree-container" class="rf-tree-container">
            <div class="empty-state">Lade Partner-Baum...</div>
        </div>
    </div>
</div>

<!-- ── Tab: Provisionen ───────────────────────────────────────────────────── -->
<div id="rf-tab-commission" class="rf-tab-content" hidden>
    <div class="card">
        <div class="card-head">
            <span class="card-title">Provisions-Details nach Tag</span>
            <div class="tr-card-head-actions">
                <input type="date" id="rf-commission-date" value="<?= date('Y-m-d') ?>">
                <button class="btn btn-primary btn-sm" id="btn-rf-commission-load">Laden</button>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data-table" id="rf-commission-table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th class="rf-sortable" data-col="login">Login <span class="rf-sort-icon">↕</span></th>
                        <th class="rf-sortable" data-col="name">Name <span class="rf-sort-icon">↕</span></th>
                        <th class="rf-sortable" data-col="symbol">Symbol <span class="rf-sort-icon">↕</span></th>
                        <th class="rf-sortable" data-col="volume">Volumen <span class="rf-sort-icon">↕</span></th>
                        <th class="rf-sortable" data-col="close_time">Geschlossen <span class="rf-sort-icon">↕</span></th>
                        <th>Server</th>
                        <th class="rf-sortable" data-col="level">Level <span class="rf-sort-icon">↕</span></th>
                        <th class="rf-sortable" data-col="amount">Provision (USD) <span class="rf-sort-icon">↕</span></th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="8" class="empty-state">Datum wählen und laden.</td></tr>
                </tbody>
            </table>
        </div>
        <div id="rf-commission-summary" class="rf-commission-summary" hidden>
            <strong>Gesamt:</strong> <span id="rf-commission-sum">0.00</span> USD
            &nbsp;|&nbsp;
            <span id="rf-commission-rows">0</span> Transaktionen
        </div>
        <div class="rf-pagination" id="rf-commission-pagination"></div>
    </div>
</div>

<!-- ── Tab: Konten-Verwaltung (Admin + Partner) ───────────────────────────── -->
<?php if (is_partner()): ?>
<div id="rf-tab-settings" class="rf-tab-content" hidden>
    <div class="card">
        <div class="card-head">
            <span class="card-title">Meine Partner-Konten</span>
            <button class="btn btn-primary btn-sm" id="btn-rf-add-account2">+ Konto hinzufügen</button>
        </div>
        <div class="table-wrap">
            <table class="data-table" id="rf-settings-table">
                <thead>
                    <tr>
                        <th>Bezeichnung</th>
                        <th>Konto-ID</th>
                        <?php if ($currentRole === 'admin'): ?>
                        <th>Zugewiesen an</th>
                        <?php endif; ?>
                        <th>API-Key</th>
                        <th>Reihenfolge</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($currentRole === 'admin') {
                    $stmtAll = $db->prepare("
                        SELECT ra.*, u.username, u.display_name
                        FROM roboforex_accounts ra
                        LEFT JOIN users u ON u.id = ra.user_id
                        ORDER BY ra.sort_order ASC
                    ");
                    $stmtAll->execute();
                } else {
                    $stmtAll = $db->prepare("
                        SELECT ra.*, NULL as username, NULL as display_name
                        FROM roboforex_accounts ra
                        WHERE ra.user_id = ?
                        ORDER BY ra.sort_order ASC
                    ");
                    $stmtAll->execute([$currentUid]);
                }
                foreach ($stmtAll->fetchAll(PDO::FETCH_ASSOC) as $acc):
                ?>
                <tr data-id="<?= $acc['id'] ?>">
                    <td class="fw-700"><?= htmlspecialchars($acc['label']) ?></td>
                    <td><?= htmlspecialchars($acc['account_id']) ?></td>
                    <?php if ($currentRole === 'admin'): ?>
                    <td><?php
                        if ($acc['user_id']) {
                            echo '<span class="badge badge--success">' . htmlspecialchars($acc['display_name'] ?: $acc['username']) . '</span>';
                        } else {
                            echo '<span class="badge badge--muted">Admin</span>';
                        }
                    ?></td>
                    <?php endif; ?>
                    <td class="text-muted"><?= substr($acc['api_key'], 0, 8) ?>••••••••</td>
                    <td><?= (int)$acc['sort_order'] ?></td>
                    <td class="col-actions">
                        <button class="btn btn-xs btn-ghost btn-rf-edit-account"
                                data-id="<?= $acc['id'] ?>"
                                data-label="<?= htmlspecialchars($acc['label']) ?>"
                                data-account-id="<?= htmlspecialchars($acc['account_id']) ?>"
                                data-api-key="<?= htmlspecialchars($acc['api_key']) ?>"
                                data-sort="<?= (int)$acc['sort_order'] ?>"
                                data-user-id="<?= (int)($acc['user_id'] ?? 0) ?>">
                            Bearbeiten
                        </button>
                        <?php
                        // Löschen: Admin darf alles, Partner nur eigene
                        $canDelete = $currentRole === 'admin' || (int)($acc['user_id'] ?? 0) === $currentUid;
                        if ($canDelete):
                        ?>
                        <button class="btn btn-xs btn-ghost btn-rf-delete-account"
                                data-id="<?= $acc['id'] ?>"
                                data-label="<?= htmlspecialchars($acc['label']) ?>">
                            Löschen
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── Konto-Modal (Admin + Partner) ─────────────────────────────────────── -->
<?php if (is_partner()): ?>
<div id="rf-account-modal" hidden>
    <div id="confirm-backdrop"></div>
    <div id="confirm-box" class="tr-modal-box">
        <h3 class="tr-modal-title" id="rf-modal-title">Konto hinzufügen</h3>
        <div class="form-group tr-modal-field">
            <label for="rf-modal-label">Bezeichnung</label>
            <input type="text" id="rf-modal-label" placeholder="z.B. Hauptkonto">
        </div>
        <div class="form-group tr-modal-field">
            <label for="rf-modal-account-id">Partner-Konto-ID</label>
            <input type="text" id="rf-modal-account-id" placeholder="z.B. 7026711">
        </div>
        <div class="form-group tr-modal-field">
            <label for="rf-modal-api-key">API-Key</label>
            <input type="text" id="rf-modal-api-key" placeholder="RoboForex Partner API-Key">
        </div>
        <?php if ($currentRole === 'admin'): ?>
        <div class="form-group tr-modal-field">
            <label for="rf-modal-user">Zuweisen an User</label>
            <select id="rf-modal-user" class="input-sm">
                <option value="">– Admin (kein User) –</option>
                <?php
                $partnerUsers = $db->prepare("SELECT id, username, display_name FROM users WHERE role IN ('admin','partner') AND aktiv=1 ORDER BY display_name");
                $partnerUsers->execute();
                foreach ($partnerUsers->fetchAll(PDO::FETCH_ASSOC) as $u):
                ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['display_name'] ?: $u['username']) ?> (<?= htmlspecialchars($u['username']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="form-group tr-modal-field-last">
            <label for="rf-modal-sort">Reihenfolge</label>
            <input type="number" id="rf-modal-sort" value="0" min="0">
        </div>
        <input type="hidden" id="rf-modal-id" value="">
        <div id="confirm-btns">
            <button class="btn btn-ghost btn-sm" id="rf-modal-cancel">Abbrechen</button>
            <button class="btn btn-primary btn-sm" id="rf-modal-save">Speichern</button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── Label-Modal ────────────────────────────────────────────────────────── -->
<div id="rf-label-modal" hidden>
    <div id="confirm-backdrop"></div>
    <div id="confirm-box" class="tr-modal-box">
        <h3 class="tr-modal-title">Name bearbeiten</h3>
        <p class="text-muted rf-label-modal-subtitle">Konto: <strong id="rf-label-modal-id"></strong></p>
        <div class="form-group tr-modal-field-last">
            <label for="rf-label-input">Name</label>
            <input type="text" id="rf-label-input" placeholder="z.B. Max Mustermann">
        </div>
        <div id="confirm-btns">
            <button class="btn btn-ghost btn-sm" id="rf-label-cancel">Abbrechen</button>
            <button class="btn btn-primary btn-sm" id="rf-label-save">Speichern</button>
        </div>
    </div>
</div>

<!-- Konfiguration als JSON -->
<script type="application/json" id="rf-config"><?php
echo json_encode([
    'base'            => 'roboforex/',
    'configured'      => !empty($rfAccounts),
    'isAdmin'         => $currentRole === 'admin',
    'accounts'        => array_map(function($a) {
        return ['id' => $a['id'], 'account_id' => $a['account_id'], 'label' => $a['label'] ?: $a['account_id']];
    }, $rfAccounts),
    'firstAccountId'  => $firstAccount ? $firstAccount['account_id'] : '',
]);
?></script>
<script src="assets/roboforex.js"></script>