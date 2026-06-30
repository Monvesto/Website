<?php
/**
 * api/myfxbook.php – MyFxBook API Wrapper
 * =========================================
 * Vollständiger Stub für alle relevanten MyFxBook API-Endpunkte.
 * Dokumentation: https://www.myfxbook.com/api
 *
 * Verwendung:
 *   require_once __DIR__ . '/api/myfxbook.php';
 *   $api = new MyfxbookApi('dein@email.com', 'deinPasswort');
 *   $api->login();
 *   $accounts = $api->getMyAccounts();
 *   $gain     = $api->getDailyGain($accountId, '2026-06-01', '2026-06-24');
 *
 * Alle Methoden geben ein Array zurück:
 *   ['success' => true/false, 'data' => ..., 'message' => '...']
 *
 * Fehlerbehandlung: Ausnahmen werden intern gefangen, nie nach oben geworfen.
 * Logging: Fehler werden per error_log() geschrieben (kein Output).
 */

class MyfxbookApi
{
    // ── Konfiguration ─────────────────────────────────────────────────────────
    private const BASE_URL = 'https://www.myfxbook.com/api';
    private const TIMEOUT  = 15; // Sekunden

    private string $email;
    private string $password;
    private ?string $session = null; // Session-Token nach Login

    // ── Konstruktor ───────────────────────────────────────────────────────────
    public function __construct(string $email, string $password, ?string $existingSession = null)
    {
        $this->email    = $email;
        $this->password = $password;
        $this->session  = $existingSession; // wiederverwendbare Session übergeben
    }

    // =========================================================================
    // AUTH
    // =========================================================================

    /**
     * Login – erhält Session-Token von MyFxBook.
     * Muss einmal pro Session aufgerufen werden.
     * Session-Token sollte gecacht werden (z.B. in $_SESSION oder DB).
     *
     * Endpunkt: GET /login.json
     * Doku:     https://www.myfxbook.com/api#login
     *
     * @return array ['success' => bool, 'session' => string|null, 'message' => string]
     */
    public function login(): array
    {
        $result = $this->get('/login.json', [
            'email'    => $this->email,
            'password' => $this->password,
        ]);

        if ($result['success'] && isset($result['data']['session'])) {
            // MyFxBook liefert den Session-Token bereits URL-kodiert zurück.
            // Dekodieren, damit http_build_query() ihn später nicht doppelt kodiert.
            $this->session = urldecode($result['data']['session']);
            return [
                'success' => true,
                'session' => $this->session,
                'message' => 'Login erfolgreich.',
            ];
        }

        return [
            'success' => false,
            'session' => null,
            'message' => $result['data']['message'] ?? 'Login fehlgeschlagen.',
        ];
    }

    /**
     * Logout – invalidiert die aktuelle Session.
     *
     * Endpunkt: GET /logout.json
     */
    public function logout(): array
    {
        $result = $this->get('/logout.json');
        $this->session = null;
        return $result;
    }

    /**
     * Gibt die aktuelle Session-ID zurück (z.B. für Caching).
     */
    public function getSession(): ?string
    {
        return $this->session;
    }

    // =========================================================================
    // ACCOUNTS
    // =========================================================================

    /**
     * Alle eigenen Accounts abrufen.
     *
     * Endpunkt: GET /get-my-accounts.json
     * Doku:     https://www.myfxbook.com/api#getMyAccounts
     *
     * Rückgabe-Felder je Account:
     *   id, name, description, accountId, gain, absGain, daily, monthly,
     *   withdrawals, deposits, interest, profit, balance, drawdown,
     *   equity, equityPercent, demo, lastUpdateDate, creationDate,
     *   firstTradeDate, tracking, views, commission, currency, profitFactor,
     *   pips, invitationUrl, server { name }
     */
    public function getMyAccounts(): array
    {
        return $this->authenticatedGet('/get-my-accounts.json');
    }

    /**
     * Öffentliche Statistiken eines Accounts abrufen.
     *
     * Endpunkt: GET /get-data-daily.json (Variante: get-my-accounts für eigene)
     *
     * @param int $accountId  MyFxBook Account-ID
     */
    public function getAccountStats(int $accountId): array
    {
        return $this->authenticatedGet('/get-my-accounts.json');
        // TODO: Filtern nach $accountId aus der Rückgabe, da kein dedizierter Endpunkt
    }

    // =========================================================================
    // GAIN / RETURNS
    // =========================================================================

    /**
     * Tägliche Rendite für einen Account in einem Zeitraum.
     *
     * Endpunkt: GET /get-data-daily.json
     * Doku:     https://www.myfxbook.com/api#getDataDaily
     *
     * @param int    $accountId  MyFxBook Account-ID
     * @param string $start      Y-m-d
     * @param string $end        Y-m-d
     *
     * Rückgabe-Felder je Tag:
     *   date, balance, pips, lots, floatingPL, profit, growthEquity,
     *   floatingPips
     */
    public function getDailyGain(int $accountId, string $start, string $end): array
    {
        return $this->authenticatedGet('/get-data-daily.json', [
            'id'    => $accountId,
            'start' => $start,
            'end'   => $end,
        ]);
    }

    /**
     * Gesamt-Gain (kumulativ) für einen Account.
     * Berechnet aus dem gain-Feld von getMyAccounts().
     *
     * @param int $accountId  MyFxBook Account-ID
     */
    public function getTotalGain(int $accountId): array
    {
        $result = $this->getMyAccounts();
        if (!$result['success']) return $result;

        $accounts = $result['data']['accounts'] ?? [];
        foreach ($accounts as $acc) {
            if ((int) $acc['id'] === $accountId) {
                return [
                    'success' => true,
                    'data'    => [
                        'gain'     => $acc['gain']     ?? null,
                        'absGain'  => $acc['absGain']  ?? null,
                        'daily'    => $acc['daily']    ?? null,
                        'monthly'  => $acc['monthly']  ?? null,
                        'profit'   => $acc['profit']   ?? null,
                        'balance'  => $acc['balance']  ?? null,
                        'drawdown' => $acc['drawdown'] ?? null,
                    ],
                    'message' => 'OK',
                ];
            }
        }

        return ['success' => false, 'data' => null, 'message' => 'Account nicht gefunden.'];
    }

    // =========================================================================
    // HISTORY / TRADES
    // =========================================================================

    /**
     * Geschlossene Trades abrufen.
     *
     * Endpunkt: GET /get-history.json
     * Doku:     https://www.myfxbook.com/api#getHistory
     *
     * @param int $accountId  MyFxBook Account-ID
     *
     * Rückgabe-Felder je Trade:
     *   openTime, closeTime, symbol, sizing { type, value },
     *   openPrice, stopLoss, takeProfit, closePrice, profit,
     *   pips, swap, magic, comment, id
     */
    public function getHistory(int $accountId): array
    {
        return $this->authenticatedGet('/get-history.json', ['id' => $accountId]);
    }

    /**
     * Offene Trades abrufen.
     *
     * Endpunkt: GET /get-open-trades.json
     * Doku:     https://www.myfxbook.com/api#getOpenTrades
     *
     * @param int $accountId  MyFxBook Account-ID
     */
    public function getOpenTrades(int $accountId): array
    {
        return $this->authenticatedGet('/get-open-trades.json', ['id' => $accountId]);
    }

    /**
     * Offene Orders (ausstehend) abrufen.
     *
     * Endpunkt: GET /get-open-orders.json
     * Doku:     https://www.myfxbook.com/api#getOpenOrders
     *
     * @param int $accountId  MyFxBook Account-ID
     */
    public function getOpenOrders(int $accountId): array
    {
        return $this->authenticatedGet('/get-open-orders.json', ['id' => $accountId]);
    }

    // =========================================================================
    // ANALYSE / REPORTS
    // =========================================================================

    /**
     * Handels-Statistiken (Performance-Kennzahlen).
     *
     * Endpunkt: GET /get-my-accounts.json (Felder: profitFactor, pips, commission etc.)
     * Für detailliertere Analysen: MyFxBook Community-API (nicht immer öffentlich).
     *
     * @param int $accountId
     */
    public function getPerformanceStats(int $accountId): array
    {
        // TODO: Detailliertere Stats über Community-API wenn Account öffentlich ist
        return $this->getTotalGain($accountId);
    }

    /**
     * Drawdown-Daten abrufen (Zeitreihe).
     * Wird über tägliche Daten aus getDailyGain berechnet.
     *
     * @param int    $accountId
     * @param string $start  Y-m-d
     * @param string $end    Y-m-d
     */
    public function getDrawdownSeries(int $accountId, string $start, string $end): array
    {
        // Die rohen Tagesdaten enthalten floatingPL – daraus kann DD berechnet werden
        return $this->getDailyGain($accountId, $start, $end);
    }

    // =========================================================================
    // WATCHERS (Community-Zugriff auf fremde Accounts)
    // =========================================================================

    /**
     * Beobachtete/verfolgte Accounts abrufen.
     *
     * Endpunkt: GET /get-watchers.json
     * Doku:     https://www.myfxbook.com/api#getWatchers
     */
    public function getWatchers(): array
    {
        return $this->authenticatedGet('/get-watchers.json');
    }

    // =========================================================================
    // HILFSMETHODEN (private)
    // =========================================================================

    /**
     * Authenticated GET: hängt automatisch session= an.
     */
    private function authenticatedGet(string $endpoint, array $params = []): array
    {
        if (!$this->session) {
            return ['success' => false, 'data' => null, 'message' => 'Nicht eingeloggt. Bitte zuerst login() aufrufen.'];
        }
        $params['session'] = $this->session;
        return $this->get($endpoint, $params);
    }

    /**
     * HTTP GET via cURL.
     *
     * @param  string $endpoint  z.B. '/login.json'
     * @param  array  $params    Query-Parameter
     * @return array  ['success' => bool, 'data' => array|null, 'message' => string, 'http_code' => int]
     */
    private function get(string $endpoint, array $params = []): array
    {
        $url = self::BASE_URL . $endpoint;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT      => 'Monvesto-Dashboard/1.0',
            CURLOPT_FOLLOWLOCATION => false,
        ]);

        $raw      = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $curlErr) {
            error_log('[MyfxbookApi] cURL-Fehler: ' . $curlErr);
            return [
                'success'   => false,
                'data'      => null,
                'message'   => 'Netzwerkfehler: ' . $curlErr,
                'http_code' => 0,
            ];
        }

        $decoded = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('[MyfxbookApi] JSON-Fehler: ' . json_last_error_msg() . ' | Response: ' . substr($raw, 0, 200));
            return [
                'success'   => false,
                'data'      => null,
                'message'   => 'Ungültige API-Antwort (kein JSON).',
                'http_code' => $httpCode,
            ];
        }

        // MyFxBook gibt error:true bei Fehlern zurück
        if (!empty($decoded['error'])) {
            return [
                'success'   => false,
                'data'      => $decoded,
                'message'   => $decoded['message'] ?? 'API-Fehler.',
                'http_code' => $httpCode,
            ];
        }

        return [
            'success'   => true,
            'data'      => $decoded,
            'message'   => 'OK',
            'http_code' => $httpCode,
        ];
    }
}


// =============================================================================
// KONFIGURATION (Beispiel – in bootstrap.php oder .env definieren)
// =============================================================================
//
// define('MYFXBOOK_EMAIL',    'dein@email.com');
// define('MYFXBOOK_PASSWORD', 'deinPasswort');
// define('MYFXBOOK_ACCOUNT_MAIN',      123456);  // Account-ID Main
// define('MYFXBOOK_ACCOUNT_EA',        234567);  // Account-ID EA
// define('MYFXBOOK_ACCOUNT_CHALLENGE', 345678);  // Account-ID Challenge
//
// Beispiel-Verwendung:
//
//   $api = new MyfxbookApi(MYFXBOOK_EMAIL, MYFXBOOK_PASSWORD);
//   $login = $api->login();
//   if ($login['success']) {
//       $_SESSION['myfxbook_session'] = $api->getSession(); // cachen
//       $accounts = $api->getMyAccounts();
//       $gain     = $api->getDailyGain(MYFXBOOK_ACCOUNT_EA, '2026-06-01', date('Y-m-d'));
//   }
//
// Session wiederverwenden (Login nicht jedes Mal nötig):
//
//   $api = new MyfxbookApi(MYFXBOOK_EMAIL, MYFXBOOK_PASSWORD, $_SESSION['myfxbook_session'] ?? null);
//   if (!$api->getSession()) $api->login();