<?php
/**
 * roboforex/proxy.php – RoboForex Partner API Proxy
 * Mit Cache (DB) + Label-Verwaltung
 */

require_once __DIR__ . '/../config/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
set_exception_handler(function ($e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
});
set_error_handler(function ($errno, $errstr) {
    echo json_encode(['success' => false, 'message' => $errstr]);
    exit;
});

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Zugriff verweigert.']);
    exit;
}

$db       = get_db();
$action   = $_REQUEST['action'] ?? 'referralinfo';
$baseUrl  = 'https://my.roboforex.com';
$cacheHours = 6; // Cache-Gültigkeit in Stunden

// ── Konto aus DB laden (nach Rolle gefiltert) ─────────────────────────────────
$requestedAccountId = $_REQUEST['account_id'] ?? '';
$currentUid         = uid();
$currentRole        = get_current_role();

if ($requestedAccountId) {
    if ($currentRole === 'admin') {
        $stmt = $db->prepare("SELECT account_id, api_key FROM roboforex_accounts WHERE account_id=? AND active=1");
        $stmt->execute([$requestedAccountId]);
    } else {
        // Partner darf nur eigene Konten abfragen
        $stmt = $db->prepare("SELECT account_id, api_key FROM roboforex_accounts WHERE account_id=? AND active=1 AND user_id=?");
        $stmt->execute([$requestedAccountId, $currentUid]);
    }
} else {
    if ($currentRole === 'admin') {
        $stmt = $db->prepare("SELECT account_id, api_key FROM roboforex_accounts WHERE active=1 ORDER BY sort_order ASC LIMIT 1");
        $stmt->execute();
    } else {
        $stmt = $db->prepare("SELECT account_id, api_key FROM roboforex_accounts WHERE active=1 AND user_id=? ORDER BY sort_order ASC LIMIT 1");
        $stmt->execute([$currentUid]);
    }
}
$noAccountActions = ['save_account','delete_account','list_accounts','save_label','delete_label','get_labels'];
$account = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$account && !in_array($action, $noAccountActions)) {
    echo json_encode(['success' => false, 'message' => 'Kein RoboForex-Konto konfiguriert.']);
    exit;
}

$accountId = $account['account_id'] ?? '';
$apiKey    = $account['api_key']    ?? '';

// ── HTTP-Helper ───────────────────────────────────────────────────────────────
function rfGet(string $url): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT      => 'MonvestoDashboard/1.0',
    ]);
    $raw  = curl_exec($ch);
    $err  = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false || $err) return ['success' => false, 'message' => 'cURL: ' . $err];
    if ($code !== 200) return ['success' => false, 'message' => 'HTTP ' . $code, 'raw' => $raw];
    return ['success' => true, 'raw' => $raw];
}

function rfXml(string $xml): ?array
{
    $x = @simplexml_load_string($xml);
    if (!$x) return null;
    return json_decode(json_encode($x), true);
}

// ── Cache-Prüfung ─────────────────────────────────────────────────────────────
function isCacheValid(PDO $db, string $accountId, string $type, int $hours): bool
{
    $stmt = $db->prepare("SELECT synced_at FROM roboforex_sync_log WHERE rf_account_id=? AND sync_type=?");
    $stmt->execute([$accountId, $type]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) return false;
    $synced = new DateTime($row['synced_at']);
    $now    = new DateTime();
    return ($now->getTimestamp() - $synced->getTimestamp()) < ($hours * 3600);
}

function updateSyncLog(PDO $db, string $accountId, string $type, int $records): void
{
    $db->prepare("INSERT INTO roboforex_sync_log (rf_account_id, sync_type, synced_at, records)
                  VALUES (?, ?, NOW(), ?)
                  ON DUPLICATE KEY UPDATE synced_at=NOW(), records=VALUES(records)")
       ->execute([$accountId, $type, $records]);
}

// ── Labels laden (für alle Actions verfügbar) ─────────────────────────────────
function getLabels(PDO $db): array
{
    $stmt = $db->prepare("SELECT client_account_id, label, notes FROM roboforex_client_labels");
    $stmt->execute();
    $labels = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $labels[$row['client_account_id']] = ['label' => $row['label'], 'notes' => $row['notes']];
    }
    return $labels;
}

// ════════════════════════════════════════════════════════════════════════════
// KONTO-VERWALTUNG
// ════════════════════════════════════════════════════════════════════════════
if ($action === 'list_accounts') {
    $stmt = $db->prepare("SELECT id, account_id, label, sort_order, active FROM roboforex_accounts ORDER BY sort_order ASC");
    $stmt->execute();
    echo json_encode(['success' => true, 'accounts' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

if ($action === 'save_account') {
    if (!is_partner()) { echo json_encode(['success' => false, 'message' => 'Kein Zugriff.']); exit; }

    $id     = (int)($_POST['id']         ?? 0);
    $accId  = trim($_POST['account_id']  ?? '');
    $label  = trim($_POST['label']       ?? '');
    $key    = trim($_POST['api_key']     ?? '');
    $sort   = (int)($_POST['sort_order'] ?? 0);

    // user_id: Admin kann frei zuweisen, Partner bekommt immer seine eigene
    if (get_current_role() === 'admin') {
        $userId = (int)($_POST['user_id'] ?? 0) ?: null;
    } else {
        $userId = uid(); // Partner → immer eigene user_id
    }

    if (!$accId) { echo json_encode(['success' => false, 'message' => 'Konto-ID ist Pflicht.']); exit; }

    if ($id > 0) {
        // Prüfen ob Partner sein eigenes Konto bearbeitet
        if (get_current_role() !== 'admin') {
            $check = $db->prepare("SELECT user_id FROM roboforex_accounts WHERE id=?");
            $check->execute([$id]);
            $owner = $check->fetchColumn();
            if ((int)$owner !== uid()) {
                echo json_encode(['success' => false, 'message' => 'Kein Zugriff auf dieses Konto.']);
                exit;
            }
        }
        if ($key && !str_contains($key, '•')) {
            $db->prepare("UPDATE roboforex_accounts SET account_id=?,label=?,api_key=?,sort_order=?,user_id=? WHERE id=?")
               ->execute([$accId, $label, $key, $sort, $userId, $id]);
        } else {
            $db->prepare("UPDATE roboforex_accounts SET account_id=?,label=?,sort_order=?,user_id=? WHERE id=?")
               ->execute([$accId, $label, $sort, $userId, $id]);
        }
    } else {
        if (!$key) { echo json_encode(['success' => false, 'message' => 'API-Key ist Pflicht.']); exit; }
        $db->prepare("INSERT INTO roboforex_accounts (account_id,label,api_key,sort_order,user_id) VALUES (?,?,?,?,?)
                      ON DUPLICATE KEY UPDATE label=VALUES(label),api_key=VALUES(api_key),sort_order=VALUES(sort_order),user_id=VALUES(user_id)")
           ->execute([$accId, $label, $key, $sort, $userId]);
    }
    echo json_encode(['success' => true, 'message' => 'Konto gespeichert.']);
    exit;
}

if ($action === 'delete_account') {
    if (!is_partner()) { echo json_encode(['success' => false, 'message' => 'Kein Zugriff.']); exit; }
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) { echo json_encode(['success' => false, 'message' => 'Ungültige ID.']); exit; }
    // Partner darf nur eigene Konten löschen
    if (get_current_role() !== 'admin') {
        $check = $db->prepare("SELECT user_id FROM roboforex_accounts WHERE id=?");
        $check->execute([$id]);
        $owner = $check->fetchColumn();
        if ((int)$owner !== uid()) {
            echo json_encode(['success' => false, 'message' => 'Kein Zugriff auf dieses Konto.']);
            exit;
        }
    }
    $db->prepare("DELETE FROM roboforex_accounts WHERE id=?")->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

// ════════════════════════════════════════════════════════════════════════════
// LABEL-VERWALTUNG
// ════════════════════════════════════════════════════════════════════════════
if ($action === 'get_labels') {
    echo json_encode(['success' => true, 'labels' => getLabels($db)]);
    exit;
}

if ($action === 'save_label') {
    $clientId = trim($_POST['client_account_id'] ?? '');
    $label    = trim($_POST['label']             ?? '');
    $notes    = trim($_POST['notes']             ?? '');
    if (!$clientId) { echo json_encode(['success' => false, 'message' => 'Konto-ID fehlt.']); exit; }
    $db->prepare("INSERT INTO roboforex_client_labels (client_account_id, label, notes)
                  VALUES (?, ?, ?)
                  ON DUPLICATE KEY UPDATE label=VALUES(label), notes=VALUES(notes), updated_at=NOW()")
       ->execute([$clientId, $label, $notes]);
    echo json_encode(['success' => true, 'message' => 'Label gespeichert.']);
    exit;
}

if ($action === 'delete_label') {
    $clientId = trim($_POST['client_account_id'] ?? '');
    if (!$clientId) { echo json_encode(['success' => false, 'message' => 'Konto-ID fehlt.']); exit; }
    $db->prepare("DELETE FROM roboforex_client_labels WHERE client_account_id=?")->execute([$clientId]);
    echo json_encode(['success' => true]);
    exit;
}

// ════════════════════════════════════════════════════════════════════════════
// API-AKTIONEN MIT CACHE
// ════════════════════════════════════════════════════════════════════════════

// ── Alle Übersichts-Daten auf einmal (für schnelles Laden) ───────────────────
if ($action === 'overview') {
    $forceRefresh = ($_GET['refresh'] ?? '0') === '1';
    $today        = date('Y-m-d');
    $yesterday    = date('Y-m-d', strtotime('-1 day'));
    $monday       = date('Y-m-d', strtotime('monday this week'));
    if ($monday > $today) $monday = date('Y-m-d', strtotime('monday last week'));
    $monthStart   = date('Y-m-01');
    $ago90        = date('Y-m-d', strtotime('-90 days'));

    $ranges = [
        'today'    => [$yesterday,  $yesterday],  // heute gutgeschrieben = Trades von gestern
        'tomorrow' => [$today,      $today],       // morgen vorgemerkt = Trades von heute
        'week'     => [$monday,     $yesterday],   // diese Woche gutgeschrieben (bis gestern)
        'month'    => [$monthStart, $yesterday],   // diesen Monat gutgeschrieben (bis gestern)
        'total'    => [$ago90,      $yesterday],   // 90 Tage gutgeschrieben (bis gestern)
    ];

    $result = [];

    if (!$forceRefresh) {
        // ── Aus DB-Cache laden ────────────────────────────────────────────────
        // Referral Info
        $stmt = $db->prepare("SELECT cache_key, amount FROM roboforex_commission_cache WHERE rf_account_id=? AND cache_key IN ('active_clients_in_one_month','deposited_clients','registrations','all_referral_count')");
        $stmt->execute([$accountId]);
        $ri = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) $ri[$r['cache_key']] = (int)$r['amount'];
        if (!empty($ri)) $result['referralinfo'] = $ri;

        // Provisions
        $stmt2 = $db->prepare("SELECT cache_key, amount FROM roboforex_commission_cache WHERE rf_account_id=? AND cache_key IN ('today','tomorrow','week','month','total')");
        $stmt2->execute([$accountId]);
        $co = [];
        foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $r) $co[$r['cache_key']] = (float)$r['amount'];
        if (!empty($co)) $result['commission'] = $co;

        if (!empty($co) && isset($ri['active_clients_in_one_month'])) {
            if (!empty($ri)) $result['referralinfo'] = $ri;
            $result['commission'] = $co;
            echo json_encode(['success' => true, 'source' => 'cache', 'data' => $result]);
            exit;
        }
    }

    // ── Von API laden (force refresh oder Cache leer) ─────────────────────────
    // Referral Info
    $r = rfGet($baseUrl . '/api/partners/referralinfo?account_id=' . $accountId . '&api_key=' . $apiKey);
    if ($r['success']) {
        $data = rfXml($r['raw']);
        $result['referralinfo'] = $data;
        foreach (['active_clients_in_one_month','deposited_clients','registrations','all_referral_count'] as $f) {
            if (isset($data[$f])) {
                $db->prepare("INSERT INTO roboforex_commission_cache (rf_account_id,cache_key,amount,date_from,date_to) VALUES (?,?,?,CURDATE(),CURDATE()) ON DUPLICATE KEY UPDATE amount=VALUES(amount),synced_at=NOW()")
                   ->execute([$accountId, $f, (int)$data[$f]]);
            }
        }
        updateSyncLog($db, $accountId, 'referralinfo', 1);
    }

    // Provisions-Ranges
    $co = [];
    foreach ($ranges as $key => [$from, $to]) {
        $start   = new DateTime($from);
        $end     = new DateTime($to);
        $total   = 0.0;
        $current = clone $start;
        while ($current <= $end) {
            $d = $current->format('Y-m-d');
            $r = rfGet($baseUrl . '/api/commission/get?account_id=' . $accountId . '&api_key=' . $apiKey . '&date=' . $d . '&page=1&on_page=400');
            if ($r['success']) {
                $data    = rfXml($r['raw']);
                $entries = $data['ticket'] ?? [];
                if (isset($entries['amount'])) $entries = [$entries];
                foreach ((array)$entries as $e) $total += (float)($e['amount'] ?? 0);
            }
            $current->modify('+1 day');
        }
        $total  = round($total, 4);
        $co[$key] = $total;
        $db->prepare("INSERT INTO roboforex_commission_cache (rf_account_id,cache_key,amount,date_from,date_to) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE amount=VALUES(amount),date_from=VALUES(date_from),date_to=VALUES(date_to),synced_at=NOW()")
           ->execute([$accountId, $key, $total, $from, $to]);
    }
    $result['commission'] = $co;

    echo json_encode(['success' => true, 'source' => 'api', 'data' => $result]);
    exit;
}
if ($action === 'referralinfo') {
    $forceRefresh = ($_GET['refresh'] ?? '0') === '1';

    if (!$forceRefresh && isCacheValid($db, $accountId, 'referralinfo', 3)) {
        $stmt = $db->prepare("SELECT records FROM roboforex_sync_log WHERE rf_account_id=? AND sync_type='referralinfo'");
        $stmt->execute([$accountId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // Gecachte Referral-Daten aus commission_cache holen
        $stmt2 = $db->prepare("SELECT cache_key, amount FROM roboforex_commission_cache WHERE rf_account_id=? AND cache_key IN ('active_clients_in_one_month','deposited_clients','registrations','all_referral_count')");
        $stmt2->execute([$accountId]);
        $cached = [];
        foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $r) $cached[$r['cache_key']] = $r['amount'];
        if (!empty($cached)) {
            echo json_encode(['success' => true, 'source' => 'cache', 'data' => [
                'active_clients_in_one_month' => (int)($cached['active_clients_in_one_month'] ?? 0),
                'deposited_clients'           => (int)($cached['deposited_clients']           ?? 0),
                'registrations'               => (int)($cached['registrations']               ?? 0),
                'all_referral_count'          => (int)($cached['all_referral_count']          ?? 0),
            ]]);
            exit;
        }
    }

    $r = rfGet($baseUrl . '/api/partners/referralinfo?account_id=' . $accountId . '&api_key=' . $apiKey);
    if (!$r['success']) { echo json_encode(['success' => false, 'message' => $r['message']]); exit; }
    $data = rfXml($r['raw']);

    // In Cache speichern
    $fields = ['active_clients_in_one_month','deposited_clients','registrations','all_referral_count'];
    foreach ($fields as $f) {
        if (isset($data[$f])) {
            $db->prepare("INSERT INTO roboforex_commission_cache (rf_account_id, cache_key, amount, date_from, date_to)
                          VALUES (?, ?, ?, CURDATE(), CURDATE())
                          ON DUPLICATE KEY UPDATE amount=VALUES(amount), synced_at=NOW()")
               ->execute([$accountId, $f, (int)$data[$f]]);
        }
    }
    updateSyncLog($db, $accountId, 'referralinfo', 1);

    echo json_encode(['success' => true, 'source' => 'api', 'data' => $data]);
    exit;
}

// ── Clients (mit Cache) ───────────────────────────────────────────────────────
if ($action === 'partners') {
    $forceRefresh = ($_GET['refresh'] ?? '0') === '1';

    // Labels immer laden
    $labels = getLabels($db);

    // Cache prüfen
    if (!$forceRefresh && isCacheValid($db, $accountId, 'clients', $cacheHours)) {
        // Aus DB lesen
        $stmt = $db->prepare("
            SELECT c.client_account_id, c.account_type, c.reg_date,
                   c.has_reached_deposit_threshold, c.is_active_accrual_of_commission,
                   l.label, l.notes
            FROM roboforex_clients c
            LEFT JOIN roboforex_client_labels l ON l.client_account_id = c.client_account_id
            WHERE c.rf_account_id = ?
            ORDER BY c.reg_date ASC
        ");
        $stmt->execute([$accountId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success'  => true,
            'source'   => 'cache',
            'count'    => count($rows),
            'clients'  => $rows,
            'labels'   => $labels,
        ]);
        exit;
    }

    // Alle Seiten von API laden
    $allClients = [];
    $page = 1;
    do {
        $r = rfGet($baseUrl . '/api/partners?account_id=' . $accountId . '&api_key=' . $apiKey . '&page=' . $page . '&on_page=400');
        if (!$r['success']) break;
        $data  = rfXml($r['raw']);
        $meta  = $data['@attributes'] ?? [];
        $items = $data['account'] ?? [];
        $list  = is_array($items) ? $items : ($items ? [$items] : []);
        foreach ($list as $item) {
            $allClients[] = $item;
        }
        $totalPages = (int)($meta['pages'] ?? 1);
        $page++;
    } while ($page <= $totalPages);

    // In DB speichern (INSERT OR UPDATE)
    $insertStmt = $db->prepare("
        INSERT INTO roboforex_clients
            (rf_account_id, client_account_id, account_type, reg_date,
             has_reached_deposit_threshold, is_active_accrual_of_commission, synced_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            account_type                    = VALUES(account_type),
            reg_date                        = VALUES(reg_date),
            has_reached_deposit_threshold   = VALUES(has_reached_deposit_threshold),
            is_active_accrual_of_commission = VALUES(is_active_accrual_of_commission),
            synced_at                       = NOW()
    ");

    foreach ($allClients as $c) {
        $cid  = $c['@attributes']['id'] ?? '';
        $type = $c['type'] ?? '';
        $reg  = $c['reg_date'] ?? null;
        $dep  = (int)($c['has_reached_deposit_threshold']   ?? 0);
        $com  = (int)($c['is_active_accrual_of_commission'] ?? 0);
        if ($cid) $insertStmt->execute([$accountId, $cid, $type, $reg, $dep, $com]);
    }

    updateSyncLog($db, $accountId, 'clients', count($allClients));

    // Mit Labels anreichern
    $result = array_map(function ($c) use ($labels) {
        $cid = $c['@attributes']['id'] ?? '';
        return [
            'client_account_id'               => $cid,
            'account_type'                    => $c['type'] ?? '',
            'reg_date'                        => $c['reg_date'] ?? '',
            'has_reached_deposit_threshold'   => $c['has_reached_deposit_threshold']   ?? '0',
            'is_active_accrual_of_commission' => $c['is_active_accrual_of_commission'] ?? '0',
            'label'                           => $labels[$cid]['label'] ?? '',
            'notes'                           => $labels[$cid]['notes'] ?? '',
        ];
    }, $allClients);

    echo json_encode([
        'success' => true,
        'source'  => 'api',
        'count'   => count($result),
        'clients' => $result,
        'labels'  => $labels,
    ]);
    exit;
}

// ── Partner-Baum (mit Cache) ──────────────────────────────────────────────────
if ($action === 'tree') {
    $forceRefresh = ($_GET['refresh'] ?? '0') === '1';
    $labels       = getLabels($db);

    if (!$forceRefresh && isCacheValid($db, $accountId, 'tree', $cacheHours)) {
        // Aus DB lesen – Baum rekonstruieren
        $stmt = $db->prepare("
            SELECT t.parent_id, t.child_id, t.depth,
                   c.account_type,
                   l.label
            FROM roboforex_tree t
            LEFT JOIN roboforex_clients c ON c.client_account_id = t.child_id AND c.rf_account_id = t.rf_account_id
            LEFT JOIN roboforex_client_labels l ON l.client_account_id = t.child_id
            WHERE t.rf_account_id = ?
            ORDER BY t.depth ASC, t.parent_id ASC
        ");
        $stmt->execute([$accountId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'source'  => 'cache',
            'tree'    => $rows,
            'labels'  => $labels,
            'root'    => $accountId,
        ]);
        exit;
    }

    // Von API laden – SimpleXML direkt nutzen für korrekte Tiefe
    $r = rfGet($baseUrl . '/api/partners/tree?account_id=' . $accountId . '&api_key=' . $apiKey);
    if (!$r['success']) { echo json_encode(['success' => false, 'message' => $r['message']]); exit; }

    $xml = @simplexml_load_string($r['raw']);
    if (!$xml) { echo json_encode(['success' => false, 'message' => 'XML parse error']); exit; }

    // Baum in DB speichern
    $db->prepare("DELETE FROM roboforex_tree WHERE rf_account_id=?")->execute([$accountId]);
    $insertTree = $db->prepare("INSERT IGNORE INTO roboforex_tree (rf_account_id, parent_id, child_id, depth) VALUES (?, ?, ?, ?)");

    $treeRows = 0;
    $rootId   = (string)($xml['id'] ?? $accountId);
    $rootType = (string)($xml->type ?? '');

    // Rekursiv SimpleXML-Objekt traversieren
    function parseXmlTreeNode($xmlNode, $parentId, $depth, $accountId, $insertTree, &$treeRows) {
        $result = [];
        if (!isset($xmlNode->referrals->account)) return $result;
        foreach ($xmlNode->referrals->account as $child) {
            $childId   = (string)$child['id'];
            $childType = (string)$child->type;
            if (!$childId) continue;
            $insertTree->execute([$accountId, $parentId, $childId, $depth]);
            $treeRows++;
            $subChildren = parseXmlTreeNode($child, $childId, $depth + 1, $accountId, $insertTree, $treeRows);
            $result[] = ['id' => $childId, 'type' => $childType, 'children' => $subChildren];
        }
        return $result;
    }

    $treeData = parseXmlTreeNode($xml, $rootId, 1, $accountId, $insertTree, $treeRows);


    updateSyncLog($db, $accountId, 'tree', $treeRows);

    echo json_encode([
        'success'  => true,
        'source'   => 'api',
        'treeData' => ['id' => $rootId, 'type' => $rootType, 'children' => $treeData],
        'labels'   => $labels,
        'root'     => $rootId,
    ]);
    exit;
}

// ── Baum-Suche ────────────────────────────────────────────────────────────────
if ($action === 'tree_search') {
    $ref = trim($_GET['referral'] ?? '');
    if (!$ref) { echo json_encode(['success' => false, 'message' => 'Bitte Kontonummer eingeben.']); exit; }
    $labels = getLabels($db);
    $r = rfGet($baseUrl . '/api/partners/tree?account_id=' . $accountId . '&api_key=' . $apiKey . '&referral_account_id=' . urlencode($ref));
    if (!$r['success']) { echo json_encode(['success' => false, 'message' => $r['message']]); exit; }
    echo json_encode(['success' => true, 'data' => rfXml($r['raw']), 'labels' => $labels]);
    exit;
}

// ── Provisionen ───────────────────────────────────────────────────────────────
if ($action === 'commission') {
    $date = $_GET['date'] ?? date('Y-m-d');
    $page = max(1, (int)($_GET['page'] ?? 1));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        echo json_encode(['success' => false, 'message' => 'Ungültiges Datum.']); exit;
    }
    $r = rfGet($baseUrl . '/api/commission/get?account_id=' . $accountId . '&api_key=' . $apiKey . '&date=' . $date . '&page=' . $page . '&on_page=400');
    if (!$r['success']) { echo json_encode(['success' => false, 'message' => $r['message']]); exit; }
    echo json_encode(['success' => true, 'data' => rfXml($r['raw']), 'date' => $date]);
    exit;
}

// ── Provisions-Range (mit DB-Cache) ──────────────────────────────────────────
if ($action === 'commission_range') {
    $from         = $_GET['from']    ?? date('Y-m-d');
    $to           = $_GET['to']      ?? date('Y-m-d');
    $cacheKey     = $_GET['cache_key'] ?? ''; // z.B. 'today', 'week', 'month', 'total'
    $forceRefresh = ($_GET['refresh'] ?? '0') === '1';
    $today        = date('Y-m-d');

    // Cache nur für feste Zeiträume nutzen (nicht wenn $to in der Vergangenheit liegt)
    $useCache = $cacheKey !== '' && !$forceRefresh;

    // Prüfen ob gecachter Wert noch gültig ist
    // Heute/Morgen: 1 Stunde; Woche/Monat: 3 Stunden; Total: 6 Stunden
    $cacheTTL = ['today' => 1, 'tomorrow' => 1, 'week' => 3, 'month' => 3, 'total' => 6];
    $ttlHours = $cacheTTL[$cacheKey] ?? 3;

    if ($useCache) {
        $stmt = $db->prepare("SELECT amount, synced_at FROM roboforex_commission_cache WHERE rf_account_id=? AND cache_key=? AND date_from=? AND date_to=?");
        $stmt->execute([$accountId, $cacheKey, $from, $to]);
        $cached = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($cached) {
            $age = (time() - strtotime($cached['synced_at'])) / 3600;
            if ($age < $ttlHours) {
                echo json_encode(['success' => true, 'from' => $from, 'to' => $to, 'total' => (float)$cached['amount'], 'rows' => 0, 'source' => 'cache']);
                exit;
            }
        }
    }

    // Von API laden
    $start   = new DateTime($from);
    $end     = new DateTime($to);
    $total   = 0.0;
    $rows    = 0;
    $current = clone $start;
    while ($current <= $end) {
        $date = $current->format('Y-m-d');
        $r    = rfGet($baseUrl . '/api/commission/get?account_id=' . $accountId . '&api_key=' . $apiKey . '&date=' . $date . '&page=1&on_page=400');
        if ($r['success']) {
            $data    = rfXml($r['raw']);
            $entries = $data['ticket'] ?? [];
            if (isset($entries['amount'])) $entries = [$entries];
            foreach ((array)$entries as $e) { $total += (float)($e['amount'] ?? 0); $rows++; }
        }
        $current->modify('+1 day');
    }

    $total = round($total, 4);

    // In Cache speichern
    if ($useCache) {
        $db->prepare("INSERT INTO roboforex_commission_cache (rf_account_id, cache_key, amount, date_from, date_to, synced_at)
                      VALUES (?, ?, ?, ?, ?, NOW())
                      ON DUPLICATE KEY UPDATE amount=VALUES(amount), date_from=VALUES(date_from), date_to=VALUES(date_to), synced_at=NOW()")
           ->execute([$accountId, $cacheKey, $total, $from, $to]);
    }

    echo json_encode(['success' => true, 'from' => $from, 'to' => $to, 'total' => $total, 'rows' => $rows, 'source' => 'api']);
    exit;
}

// ── Symbol-Auswertung ─────────────────────────────────────────────────────────
if ($action === 'commission_by_symbol') {
    $from    = $_GET['from'] ?? date('Y-m-01');
    $to      = $_GET['to']   ?? date('Y-m-d');
    $symbols = [];
    $current = clone (new DateTime($from));
    $end     = new DateTime($to);

    while ($current <= $end) {
        $date = $current->format('Y-m-d');
        $r    = rfGet($baseUrl . '/api/commission/get?account_id=' . $accountId . '&api_key=' . $apiKey . '&date=' . $date . '&page=1&on_page=400');
        if ($r['success']) {
            $data    = rfXml($r['raw']);
            $entries = $data['ticket'] ?? [];
            if (isset($entries['amount'])) $entries = [$entries];
            foreach ((array)$entries as $e) {
                $sym = $e['symbol'] ?? 'Unknown';
                if (!isset($symbols[$sym])) $symbols[$sym] = ['trades' => 0, 'volume' => 0.0, 'commission' => 0.0];
                $symbols[$sym]['trades']++;
                $symbols[$sym]['volume']     += (float)($e['volume'] ?? 0);
                $symbols[$sym]['commission'] += (float)($e['amount'] ?? 0);
            }
        }
        $current->modify('+1 day');
    }

    uasort($symbols, function ($a, $b) { return $b['commission'] <=> $a['commission']; });
    $result = [];
    foreach ($symbols as $sym => $d) {
        $result[] = [
            'symbol'     => $sym,
            'trades'     => $d['trades'],
            'volume'     => round($d['volume'],     2),
            'commission' => round($d['commission'], 4),
            'per_lot'    => $d['volume'] > 0 ? round($d['commission'] / $d['volume'], 4) : 0,
        ];
    }
    echo json_encode(['success' => true, 'symbols' => $result, 'from' => $from, 'to' => $to]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unbekannte Aktion: ' . $action]);