<?php
// ════════════════════════════════════════════════
// banking.php – Open Banking via GoCardless
//
// ARCHITEKTUR ÜBERSICHT:
// ─────────────────────────────────────────────────
// GoCardless (ehemals Nordigen) bietet eine kostenlose
// Open Banking API (PSD2) für den Zugriff auf Kontodaten.
//
// ABLAUF:
//   1. API-Zugangsdaten (Secret ID + Key) in config.php hinterlegen
//   2. Token holen: POST /token/new/ → Access Token (24h gültig)
//   3. Institution suchen: GET /institutions/?country=DE
//   4. Requisition erstellen: POST /requisitions/ → Einwilligungs-Link
//   5. User wird zur Bank weitergeleitet (OAuth)
//   6. Nach Rückkehr: GET /requisitions/{id}/ → Account-IDs
//   7. Transaktionen abrufen: GET /accounts/{id}/transactions/
//
// DATENBANK:
//   - banking_tokens:       Access Token + Ablaufzeit
//   - banking_requisitions: Verknüpfte Bankzugänge
//   - banking_accounts:     Konten (IBAN, Name, etc.)
//   - banking_transactions: Transaktionen (gecacht)
//
// KOSTEN:
//   - GoCardless Free Tier: 50 API-Anfragen/Tag
//   - Transaktionen werden lokal gecacht (1x täglich aktualisieren)
//
// SETUP:
//   1. Account erstellen: https://bankaccountdata.gocardless.com
//   2. Secret ID + Key generieren
//   3. In config.php eintragen:
//      define('GOCARDLESS_SECRET_ID', 'xxx');
//      define('GOCARDLESS_SECRET_KEY', 'xxx');
// ════════════════════════════════════════════════

$db = get_db();

// ── Konfiguration prüfen ──
// Diese Konstanten müssen in config.php definiert sein
$gc_configured = defined('GOCARDLESS_SECRET_ID') && defined('GOCARDLESS_SECRET_KEY')
    && GOCARDLESS_SECRET_ID !== ''
    && GOCARDLESS_SECRET_KEY !== '';

// ── Automatische Migration: Banking-Tabellen anlegen ──
(function() use ($db) {
    try {
        // Tokens speichern (Access Token hat 24h Laufzeit)
        $db->exec("CREATE TABLE IF NOT EXISTS banking_tokens (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            access     TEXT NOT NULL,              -- JWT Access Token
            refresh    TEXT NOT NULL,              -- JWT Refresh Token
            access_exp DATETIME NOT NULL,          -- Access Token Ablauf
            refresh_exp DATETIME NOT NULL,         -- Refresh Token Ablauf
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Requisitions = genehmigte Bankverbindungen
        $db->exec("CREATE TABLE IF NOT EXISTS banking_requisitions (
            id             INT AUTO_INCREMENT PRIMARY KEY,
            requisition_id VARCHAR(100) NOT NULL UNIQUE, -- GoCardless ID
            institution_id VARCHAR(100) NOT NULL,        -- z.B. 'SPARKASSE_DE'
            institution_name VARCHAR(100) NOT NULL,
            status         VARCHAR(20) NOT NULL DEFAULT 'CREATED',
            -- Status-Werte: CREATED, GIVING_CONSENT, UNDERGOING_AUTHENTICATION,
            --               SELECTING_ACCOUNTS, LINKED, SUSPENDED, EXPIRED
            link           TEXT,                         -- Einwilligungs-URL
            person         VARCHAR(20) NOT NULL DEFAULT 'Marcel',
            created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Konten (werden nach erfolgreicher Requisition gefüllt)
        $db->exec("CREATE TABLE IF NOT EXISTS banking_accounts (
            id             INT AUTO_INCREMENT PRIMARY KEY,
            account_id     VARCHAR(100) NOT NULL UNIQUE, -- GoCardless Account-ID
            requisition_id VARCHAR(100) NOT NULL,        -- Zugehörige Requisition
            iban           VARCHAR(34),
            name           VARCHAR(100),
            currency       VARCHAR(3) DEFAULT 'EUR',
            product        VARCHAR(100),                 -- z.B. 'Girokonto'
            last_synced    DATETIME DEFAULT NULL,        -- Letzter Sync
            created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Transaktionen (gecacht, täglich aktualisieren)
        $db->exec("CREATE TABLE IF NOT EXISTS banking_transactions (
            id                  INT AUTO_INCREMENT PRIMARY KEY,
            account_id          VARCHAR(100) NOT NULL,   -- GoCardless Account-ID
            transaction_id      VARCHAR(200) NOT NULL,   -- GoCardless Transaction-ID
            booking_date        DATE NOT NULL,
            value_date          DATE,
            amount              DECIMAL(12,2) NOT NULL,  -- Positiv = Eingang, Negativ = Ausgang
            currency            VARCHAR(3) DEFAULT 'EUR',
            creditor_name       VARCHAR(200),            -- Empfänger (bei Ausgabe)
            debtor_name         VARCHAR(200),            -- Absender (bei Eingang)
            remittance_info     TEXT,                    -- Verwendungszweck
            kategorie           VARCHAR(50) DEFAULT '',  -- Manuelle Kategorie
            created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_tx (account_id, transaction_id)
        )");

    } catch (PDOException $e) {
        error_log('Migration banking: ' . $e->getMessage());
    }
})();

// ── GoCardless API Basis-URL ──
const GC_API = 'https://bankaccountdata.gocardless.com/api/v2';

// ════════════════════════════════════════════════
// HILFSFUNKTIONEN
// ════════════════════════════════════════════════

/**
 * HTTP-Request an GoCardless API senden.
 * Gibt dekodiertes JSON-Array zurück oder false bei Fehler.
 *
 * @param string $endpoint  API-Pfad, z.B. '/token/new/'
 * @param string $method    HTTP-Methode: GET, POST
 * @param array  $body      POST-Body als Array (wird zu JSON)
 * @param string $token     Access Token (leer = kein Auth-Header)
 */
function gc_request(string $endpoint, string $method = 'GET', array $body = [], string $token = '') {
    $headers = ['Content-Type: application/json', 'Accept: application/json'];
    if ($token !== '') $headers[] = 'Authorization: Bearer ' . $token;

    $ctx = stream_context_create(['http' => [
        'method'  => $method,
        'header'  => implode("\r\n", $headers),
        'content' => $method === 'POST' ? json_encode($body) : null,
        'ignore_errors' => true,
    ]]);

    $result = @file_get_contents(GC_API . $endpoint, false, $ctx);
    if ($result === false) return false;
    return json_decode($result, true) ?? false;
}

/**
 * Gültigen Access Token holen.
 * Prüft zuerst DB-Cache, holt sonst neuen Token via API.
 * Gibt Token-String zurück oder false bei Fehler.
 */
function gc_get_token(PDO $db) {
    if (!defined('GOCARDLESS_SECRET_ID')) return false;

    // Prüfen ob noch gültiger Token in DB
    $row = $db->query("SELECT access, access_exp FROM banking_tokens ORDER BY id DESC LIMIT 1")->fetch();
    if ($row && strtotime($row['access_exp']) > time() + 60) {
        return $row['access']; // Noch gültig
    }

    // Neuen Token holen
    $res = gc_request('/token/new/', 'POST', [
        'secret_id'  => GOCARDLESS_SECRET_ID,
        'secret_key' => GOCARDLESS_SECRET_KEY,
    ]);

    if (!$res || !isset($res['access'])) return false;

    // Token in DB speichern
    $db->prepare("INSERT INTO banking_tokens (access, refresh, access_exp, refresh_exp) VALUES (?,?,?,?)")
       ->execute([
           $res['access'],
           $res['refresh'],
           date('Y-m-d H:i:s', time() + $res['access_expires']),
           date('Y-m-d H:i:s', time() + $res['refresh_expires']),
       ]);

    return $res['access'];
}

/**
 * Monatliche Auswertung der Transaktionen.
 * Gibt Einnahmen, Ausgaben und Saldo für einen Monat zurück.
 */
function gc_monthly_summary(PDO $db, string $account_id, string $month): array {
    $s = $db->prepare("SELECT
        SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as einnahmen,
        SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as ausgaben,
        COUNT(*) as anzahl
        FROM banking_transactions
        WHERE account_id=? AND DATE_FORMAT(booking_date,'%Y-%m')=?");
    $s->execute([$account_id, $month]);
    return $s->fetch() ?: ['einnahmen'=>0, 'ausgaben'=>0, 'anzahl'=>0];
}

// ── Hilfsfunktionen Darstellung ──
function he_bk(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function fmt_bk(float $v, bool $sign = false): string {
    $s = number_format(abs($v), 2, ',', '.') . ' €';
    if ($sign) return ($v >= 0 ? '+' : '–') . $s;
    return ($v < 0 ? '–' : '') . $s;
}

// ════════════════════════════════════════════════
// POST-HANDLER
// ════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = $_POST['act'] ?? '';

    /**
     * SCHRITT 1: Neue Bankverbindung starten
     * Erstellt eine Requisition bei GoCardless und leitet
     * den User zur Bank-Einwilligungsseite weiter.
     */
    if ($act === 'connect_bank') {
        $institution_id = trim($_POST['institution_id'] ?? '');
        $person         = trim($_POST['person'] ?? 'Marcel');
        if ($institution_id === '') {
            header("Location: ?page=banking&error=no_institution"); exit;
        }

        $token = gc_get_token($db);
        if (!$token) {
            header("Location: ?page=banking&error=token_failed"); exit;
        }

        // Redirect-URL nach Bank-Einwilligung
        $redirect = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '?page=banking&act=linked';

        // Requisition erstellen
        $res = gc_request('/requisitions/', 'POST', [
            'redirect'       => $redirect,
            'institution_id' => $institution_id,
            'reference'      => 'monvesto_' . time(),
            'agreement'      => '',
            'user_language'  => 'DE',
        ], $token);

        if (!$res || !isset($res['id'])) {
            header("Location: ?page=banking&error=requisition_failed"); exit;
        }

        // Requisition in DB speichern
        $db->prepare("INSERT INTO banking_requisitions (requisition_id, institution_id, institution_name, status, link, person) VALUES (?,?,?,?,?,?)")
           ->execute([$res['id'], $institution_id, $institution_id, $res['status'], $res['link'], $person]);

        // User zur Bank weiterleiten
        header("Location: " . $res['link']); exit;
    }

    /**
     * SCHRITT 2: Nach Bank-Einwilligung – Konten abrufen
     * Wird aufgerufen wenn der User von der Bank zurückkommt.
     * Ruft die Konto-IDs ab und speichert sie in banking_accounts.
     */
    if ($act === 'fetch_accounts') {
        $req_id = trim($_POST['requisition_id'] ?? '');
        $token  = gc_get_token($db);
        if (!$token || !$req_id) {
            header("Location: ?page=banking&error=fetch_failed"); exit;
        }

        $res = gc_request('/requisitions/' . $req_id . '/', 'GET', [], $token);
        if (!$res || !isset($res['accounts'])) {
            header("Location: ?page=banking&error=no_accounts"); exit;
        }

        // Status der Requisition aktualisieren
        $db->prepare("UPDATE banking_requisitions SET status=? WHERE requisition_id=?")
           ->execute([$res['status'], $req_id]);

        // Für jede Account-ID Detaildaten holen und speichern
        foreach ($res['accounts'] as $acc_id) {
            $details = gc_request('/accounts/' . $acc_id . '/', 'GET', [], $token);
            if (!$details) continue;

            $db->prepare("INSERT INTO banking_accounts (account_id, requisition_id, iban, name, currency, product)
                          VALUES (?,?,?,?,?,?)
                          ON DUPLICATE KEY UPDATE iban=VALUES(iban), name=VALUES(name)")
               ->execute([
                   $acc_id,
                   $req_id,
                   $details['iban'] ?? null,
                   $details['owner_name'] ?? $details['name'] ?? 'Unbekannt',
                   $details['currency'] ?? 'EUR',
                   $details['product'] ?? '',
               ]);
        }

        header("Location: ?page=banking&msg=connected"); exit;
    }

    /**
     * SCHRITT 3: Transaktionen synchronisieren
     * Lädt die letzten 90 Tage Transaktionen von GoCardless
     * und speichert sie lokal (INSERT IGNORE bei Duplikaten).
     */
    if ($act === 'sync_transactions') {
        $account_id = trim($_POST['account_id'] ?? '');
        $token      = gc_get_token($db);
        if (!$token || !$account_id) {
            header("Location: ?page=banking&error=sync_failed"); exit;
        }

        $res = gc_request('/accounts/' . $account_id . '/transactions/', 'GET', [], $token);
        if (!$res || !isset($res['transactions']['booked'])) {
            header("Location: ?page=banking&error=no_transactions"); exit;
        }

        $count = 0;
        foreach ($res['transactions']['booked'] as $tx) {
            try {
                $db->prepare("INSERT IGNORE INTO banking_transactions
                    (account_id, transaction_id, booking_date, value_date, amount, currency, creditor_name, debtor_name, remittance_info)
                    VALUES (?,?,?,?,?,?,?,?,?)")
                   ->execute([
                       $account_id,
                       $tx['transactionId'] ?? ($tx['internalTransactionId'] ?? uniqid()),
                       $tx['bookingDate'],
                       $tx['valueDate'] ?? $tx['bookingDate'],
                       (float)$tx['transactionAmount']['amount'],
                       $tx['transactionAmount']['currency'] ?? 'EUR',
                       $tx['creditorName'] ?? null,
                       $tx['debtorName'] ?? null,
                       $tx['remittanceInformationUnstructured'] ?? null,
                   ]);
                $count++;
            } catch (PDOException $e) {
                // Duplikate ignorieren
            }
        }

        // Sync-Zeitstempel aktualisieren
        $db->prepare("UPDATE banking_accounts SET last_synced=NOW() WHERE account_id=?")
           ->execute([$account_id]);

        header("Location: ?page=banking&msg=synced&count=$count"); exit;
    }

    /**
     * Bankverbindung trennen
     * Löscht Requisition, Konten und Transaktionen aus der DB.
     * Die GoCardless-Verbindung muss ggf. separat im Portal getrennt werden.
     */
    if ($act === 'disconnect') {
        csrf_verify();
        $req_id = trim($_POST['requisition_id'] ?? '');
        if ($req_id) {
            // Account-IDs dieser Requisition holen
            $accs = $db->prepare("SELECT account_id FROM banking_accounts WHERE requisition_id=?");
            $accs->execute([$req_id]);
            foreach ($accs->fetchAll() as $acc) {
                $db->prepare("DELETE FROM banking_transactions WHERE account_id=?")->execute([$acc['account_id']]);
            }
            $db->prepare("DELETE FROM banking_accounts WHERE requisition_id=?")->execute([$req_id]);
            $db->prepare("DELETE FROM banking_requisitions WHERE requisition_id=?")->execute([$req_id]);
        }
        header("Location: ?page=banking&msg=disconnected"); exit;
    }
}
if (defined('HANDLE_POST_ONLY')) return;

// ── Daten laden ──
$requisitions = $db->query("SELECT * FROM banking_requisitions ORDER BY created_at DESC")->fetchAll();
$accounts     = $db->query("SELECT * FROM banking_accounts ORDER BY name")->fetchAll();
$personen     = ['Marcel','Kim','Beide'];

// Aktuelle Monat für Auswertung
$monat = $_GET['monat'] ?? date('Y-m');
$monat_label_bk = date('F Y', strtotime($monat . '-01'));

// Transaktionen für aktuellen Monat
$transactions = [];
$tx_filter_acc = $_GET['account'] ?? '';
if (!empty($accounts)) {
    $acc_ids = $tx_filter_acc
        ? [$tx_filter_acc]
        : array_column($accounts, 'account_id');

    if (!empty($acc_ids)) {
        $placeholders = implode(',', array_fill(0, count($acc_ids), '?'));
        $stmt = $db->prepare("SELECT * FROM banking_transactions
            WHERE account_id IN ($placeholders)
            AND DATE_FORMAT(booking_date,'%Y-%m')=?
            ORDER BY booking_date DESC, id DESC");
        $stmt->execute([...$acc_ids, $monat]);
        $transactions = $stmt->fetchAll();
    }
}

// KPI für aktuellen Monat
$monat_einnahmen = 0;
$monat_ausgaben  = 0;
foreach ($transactions as $tx) {
    if ((float)$tx['amount'] > 0) $monat_einnahmen += (float)$tx['amount'];
    else                          $monat_ausgaben  += abs((float)$tx['amount']);
}

// Kategorien-Auswertung
$kat_ausgaben_bk = [];
foreach ($transactions as $tx) {
    if ((float)$tx['amount'] >= 0) continue;
    $k = $tx['kategorie'] ?: 'Unkategorisiert';
    $kat_ausgaben_bk[$k] = ($kat_ausgaben_bk[$k] ?? 0) + abs((float)$tx['amount']);
}
arsort($kat_ausgaben_bk);

// Error/Success Messages
$errors = ['token_failed'=>'API-Token konnte nicht geholt werden.','requisition_failed'=>'Bankverbindung konnte nicht erstellt werden.','no_accounts'=>'Keine Konten gefunden.','sync_failed'=>'Synchronisierung fehlgeschlagen.'];
$error_msg = isset($_GET['error']) ? ($errors[$_GET['error']] ?? 'Unbekannter Fehler.') : '';
?>

<?php if (isset($_GET['msg']) && $_GET['msg']==='connected'): ?>
<div class="alert alert-success">✓ Bank erfolgreich verbunden!</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg']==='synced'): ?>
<div class="alert alert-success">✓ <?= (int)($_GET['count']??0) ?> Transaktionen synchronisiert.</div>
<?php elseif (isset($_GET['msg']) && $_GET['msg']==='disconnected'): ?>
<div class="alert alert-success">Bankverbindung getrennt.</div>
<?php elseif ($error_msg): ?>
<div class="alert alert-error">⚠ <?= he_bk($error_msg) ?></div>
<?php endif; ?>

<div class="finance-topbar">
    <div class="tab-bar"></div>
    <div></div>
</div>

<!-- ════ SETUP-HINWEIS wenn API nicht konfiguriert ════ -->
<?php if (!$gc_configured): ?>
<div class="card mt-4">
    <div class="card-head card-head--amber">
        <h2 class="card-title">⚙ GoCardless API noch nicht konfiguriert</h2>
    </div>
    <div class="split-pad">
        <p style="margin-bottom:16px;color:var(--text-muted)">Um dein Bankkonto zu verbinden, benötigst du kostenlose API-Zugangsdaten von GoCardless.</p>
        <div class="banking-setup-steps">
            <div class="setup-step">
                <span class="setup-num">1</span>
                <div>
                    <strong>Account erstellen</strong><br>
                    <span class="text-muted">Registriere dich kostenlos auf</span>
                    <a href="https://bankaccountdata.gocardless.com" target="_blank" class="text-green">bankaccountdata.gocardless.com</a>
                </div>
            </div>
            <div class="setup-step">
                <span class="setup-num">2</span>
                <div>
                    <strong>API-Keys generieren</strong><br>
                    <span class="text-muted">Im Dashboard unter "User Secrets" → "Create new"</span>
                </div>
            </div>
            <div class="setup-step">
                <span class="setup-num">3</span>
                <div>
                    <strong>In config.php eintragen</strong><br>
                    <code style="background:var(--bg);padding:8px 12px;border-radius:6px;display:block;margin-top:6px;font-size:13px">
                        define('GOCARDLESS_SECRET_ID', 'deine-secret-id');<br>
                        define('GOCARDLESS_SECRET_KEY', 'dein-secret-key');
                    </code>
                </div>
            </div>
            <div class="setup-step">
                <span class="setup-num">4</span>
                <div>
                    <strong>Bank verbinden</strong><br>
                    <span class="text-muted">Danach erscheint hier das Verbindungsformular. Du wirst zur Bank-Website weitergeleitet um die Einwilligung zu erteilen.</span>
                </div>
            </div>
        </div>
        <div style="margin-top:20px;padding:12px 16px;background:var(--green-light);border-radius:8px;font-size:13px;color:var(--green-dark)">
            ✓ <strong>Kostenlos:</strong> Bis zu 50 API-Anfragen/Tag · 90 Tage Transaktionshistorie · Unterstützt über 2.500 Banken in Europa
        </div>
    </div>
</div>
<?php else: ?>

<!-- ════ BANK VERBINDEN ════ -->
<?php if (empty($accounts)): ?>
<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">🏦 Bank verbinden</h2></div>
    <div class="split-pad">
        <p style="margin-bottom:16px;color:var(--text-muted)">Wähle deine Bank und folge den Anweisungen zur Einwilligung.</p>
        <form method="POST" action="?page=banking">
            <?= csrf_field() ?>
            <input type="hidden" name="act" value="connect_bank">
            <div class="form-grid" style="padding:0;gap:16px">
                <div class="form-group">
                    <label>Bank / Institution</label>
                    <select name="institution_id">
                        <optgroup label="Deutschland – Häufig verwendet">
                            <option value="SPARKASSE_HANNOVER_SSPKDEHHXXX">Sparkasse Hannover</option>
                            <option value="VOLKSBANK_DE_VBDEDEM1XXX">Volksbank</option>
                            <option value="COMMERZBANK_COBADEFFXXX">Commerzbank</option>
                            <option value="DEUTSCHE_BANK_DEUTDEDBXXX">Deutsche Bank</option>
                            <option value="ING_INGDDEFFXXX">ING</option>
                            <option value="DKB_SSKMDEMMXXX">DKB</option>
                            <option value="N26_NTSBDEB1XXX">N26</option>
                            <option value="POSTBANK_PBNKDEFFXXX">Postbank</option>
                            <option value="COMDIRECT_COBADEHD">comdirect</option>
                        </optgroup>
                    </select>
                </div>
                <div class="form-group">
                    <label>Person</label>
                    <select name="person">
                        <?php foreach ($personen as $p): ?><option><?= $p ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-actions" style="margin-top:16px">
                <button type="submit" class="btn btn-primary">🔗 Bank verbinden</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ════ VERBUNDENE KONTEN ════ -->
<?php if (!empty($accounts)): ?>

<!-- KPI Cards -->
<div class="kpi-grid kpi-grid--4 mt-4">
    <div class="kpi-card kpi-card--info">
        <div class="kpi-label">Einnahmen <?= date('M Y', strtotime($monat.'-01')) ?></div>
        <div class="kpi-value kpi-value--md text-green"><?= fmt_bk($monat_einnahmen) ?></div>
    </div>
    <div class="kpi-card kpi-card--alert">
        <div class="kpi-label">Ausgaben <?= date('M Y', strtotime($monat.'-01')) ?></div>
        <div class="kpi-value kpi-value--md text-red"><?= fmt_bk($monat_ausgaben) ?></div>
    </div>
    <div class="kpi-card <?= ($monat_einnahmen-$monat_ausgaben)>=0?'':'kpi-card--alert' ?>">
        <div class="kpi-label">Saldo</div>
        <div class="kpi-value kpi-value--md <?= ($monat_einnahmen-$monat_ausgaben)>=0?'text-green':'text-red' ?>">
            <?= fmt_bk($monat_einnahmen - $monat_ausgaben, true) ?>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Transaktionen</div>
        <div class="kpi-value kpi-value--md"><?= count($transactions) ?></div>
    </div>
</div>

<!-- Verbundene Konten -->
<div class="card mt-4">
    <div class="card-head">
        <h2 class="card-title">🏦 Verbundene Konten</h2>
        <a href="?page=banking" class="btn btn-ghost btn-sm">+ Weiteres Konto</a>
    </div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Konto</th><th>IBAN</th><th>Produkt</th><th>Zuletzt sync.</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($accounts as $acc): ?>
        <tr>
            <td><?= he_bk($acc['name']) ?></td>
            <td><code style="font-size:12px"><?= he_bk($acc['iban']??'–') ?></code></td>
            <td><?= he_bk($acc['product']??'–') ?></td>
            <td><?= $acc['last_synced'] ? date('d.m.Y H:i', strtotime($acc['last_synced'])) : '<span class="text-muted">Noch nie</span>' ?></td>
            <td class="col-actions" style="display:flex;gap:6px">
                <!-- Transaktionen synchronisieren -->
                <form method="POST" action="?page=banking" class="form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="sync_transactions">
                    <input type="hidden" name="account_id" value="<?= he_bk($acc['account_id']) ?>">
                    <button type="submit" class="btn btn-primary btn-xs">↻ Sync</button>
                </form>
                <!-- Konto trennen (löscht lokale Daten) -->
                <?php
                $req = $db->prepare("SELECT requisition_id FROM banking_accounts WHERE account_id=?");
                $req->execute([$acc['account_id']]);
                $req_id = $req->fetchColumn();
                ?>
                <form method="POST" action="?page=banking" class="form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="disconnect">
                    <input type="hidden" name="requisition_id" value="<?= he_bk($req_id) ?>">
                    <button type="submit" class="btn btn-danger btn-xs btn-delete-confirm">✕ Trennen</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>

<!-- Monatsnavigation -->
<div class="chk-monat-nav mt-4">
    <?php
    $prev_m = date('Y-m', strtotime($monat . '-01 -1 month'));
    $next_m = date('Y-m', strtotime($monat . '-01 +1 month'));
    $monate_de = ['January'=>'Januar','February'=>'Februar','March'=>'März','April'=>'April','May'=>'Mai','June'=>'Juni','July'=>'Juli','August'=>'August','September'=>'September','October'=>'Oktober','November'=>'November','December'=>'Dezember'];
    $monat_label_bk = strtr(date('F Y', strtotime($monat.'-01')), $monate_de);
    ?>
    <a href="?page=banking&monat=<?= $prev_m ?>" class="btn btn-ghost btn-sm">‹</a>
    <span class="monat-label"><?= $monat_label_bk ?></span>
    <a href="?page=banking&monat=<?= $next_m ?>" class="btn btn-ghost btn-sm">›</a>
</div>

<!-- Ausgaben nach Kategorie -->
<?php if (!empty($kat_ausgaben_bk)): ?>
<div class="card mt-4">
    <div class="card-head"><h2 class="card-title">Ausgaben nach Kategorie</h2></div>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>Kategorie</th><th>Anteil</th><th class="col-right">Betrag</th></tr></thead>
        <tbody>
        <?php foreach ($kat_ausgaben_bk as $kat => $sum):
            $anteil = $monat_ausgaben > 0 ? $sum / $monat_ausgaben * 100 : 0;
        ?>
        <tr>
            <td><?= he_bk($kat) ?></td>
            <td>
                <div class="bar-wrap">
                    <div class="bar-track"><div class="bar-fill" data-width="<?= number_format($anteil,1,'.','') ?>"></div></div>
                    <span class="bar-label"><?= number_format($anteil,0) ?>%</span>
                </div>
            </td>
            <td class="col-right fw-700 text-red"><?= fmt_bk($sum) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>

<!-- Transaktionsliste -->
<div class="card mt-4">
    <div class="card-head">
        <div class="card-head-left">
            <h2 class="card-title">Transaktionen – <?= $monat_label_bk ?></h2>
            <span class="badge badge-neutral"><?= count($transactions) ?></span>
        </div>
    </div>
    <?php if (empty($transactions)): ?>
    <p class="empty-state">Keine Transaktionen für diesen Monat. Klicke "Sync" um Daten zu laden.</p>
    <?php else: ?>
    <div class="table-wrap"><table class="data-table">
        <thead><tr>
            <th>Datum</th><th>Empfänger / Absender</th><th>Verwendungszweck</th>
            <th>Kategorie</th><th class="col-right">Betrag</th>
        </tr></thead>
        <tbody>
        <?php foreach ($transactions as $tx): ?>
        <tr>
            <td><?= date('d.m.Y', strtotime($tx['booking_date'])) ?></td>
            <td>
                <?php
                $name = (float)$tx['amount'] < 0
                    ? ($tx['creditor_name'] ?? '–')
                    : ($tx['debtor_name'] ?? '–');
                echo he_bk($name);
                ?>
            </td>
            <td style="font-size:12px;color:var(--text-muted)"><?= he_bk(mb_strimwidth($tx['remittance_info']??'', 0, 60, '…')) ?></td>
            <td><?php if ($tx['kategorie']): ?><span class="badge badge-neutral"><?= he_bk($tx['kategorie']) ?></span><?php else: ?><span class="text-muted" style="font-size:12px">–</span><?php endif; ?></td>
            <td class="col-right fw-700 <?= (float)$tx['amount'] >= 0 ? 'text-green' : 'text-red' ?>">
                <?= fmt_bk((float)$tx['amount'], true) ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
</div>

<?php endif; // !empty($accounts) ?>
<?php endif; // $gc_configured ?>

<style>
/* ── Banking Setup Steps ── */
.banking-setup-steps { display: flex; flex-direction: column; gap: 16px; }
.setup-step { display: flex; align-items: flex-start; gap: 14px; }
.setup-num {
    width: 28px; height: 28px; border-radius: 50%;
    background: var(--green); color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; flex-shrink: 0; margin-top: 2px;
}
</style>