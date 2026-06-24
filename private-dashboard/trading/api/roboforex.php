<?php
/**
 * api/roboforex.php – RoboForex Partner/Trader API Wrapper
 * ==========================================================
 * Vollständiger Stub für alle relevanten RoboForex API-Endpunkte.
 *
 * RoboForex bietet zwei relevante APIs:
 *   1. Partner API  – Statistiken, Provisionen, Kontoübersicht (Partner-Bereich)
 *   2. Trader API   – Kontodaten, Trades, History (für eigene Konten)
 *
 * Dokumentation:
 *   Partner: https://www.roboforex.com/partners/api/
 *   Trader:  Direkte MT4/MT5 Integration oder WebTrader API
 *
 * HINWEIS: RoboForex stellt keine öffentlich dokumentierte REST-Trader-API
 * im klassischen Sinne bereit. Live-Kontodaten kommen typischerweise über:
 *   a) MyFxBook (bereits implementiert in myfxbook.php)
 *   b) MT4/MT5 Expert Advisor → lokale Datenbank → Dashboard
 *   c) RoboForex Partner-API (für Provisionen/Statistiken)
 *   d) cTrader Open API (für cTrader-Konten)
 *
 * Dieser Wrapper deckt alle verfügbaren Wege ab.
 *
 * Verwendung:
 *   require_once __DIR__ . '/api/roboforex.php';
 *   $api = new RoboforexApi('dein_api_token');
 *   $info = $api->getAccountInfo('12345678');
 *
 * Alle Methoden geben zurück:
 *   ['success' => bool, 'data' => array|null, 'message' => string, 'http_code' => int]
 */

// =============================================================================
// KLASSE: RoboforexApi
// =============================================================================

class RoboforexApi
{
    // ── Konfiguration ─────────────────────────────────────────────────────────

    // Partner-API Basis-URL
    private const PARTNER_API_URL = 'https://my.roboforex.com/api/v1';
    // cTrader Open API (für cTrader-Konten)
    private const CTRADER_API_URL = 'https://connect.spotware.com/apps';

    private const TIMEOUT = 20;

    private string  $apiToken;       // RoboForex Partner API Token
    private ?string $ctraderToken;   // cTrader Access Token (OAuth2)
    private ?string $clientId;       // cTrader App Client ID

    // ── Konstruktor ───────────────────────────────────────────────────────────
    public function __construct(
        string  $apiToken      = '',
        ?string $ctraderToken  = null,
        ?string $clientId      = null
    ) {
        $this->apiToken     = $apiToken;
        $this->ctraderToken = $ctraderToken;
        $this->clientId     = $clientId;
    }

    // =========================================================================
    // PARTNER API – Kontoübersicht & Statistiken
    // =========================================================================

    /**
     * Kontoinformationen abrufen (Partner-Bereich).
     * Liefert: Kontonummer, Name, Kontostand, Equity, Margin, freie Margin,
     * Hebel, Währung, Serverinfo.
     *
     * Endpunkt: GET /account/info
     *
     * @param string $accountId  RoboForex Kontonummer
     */
    public function getAccountInfo(string $accountId): array
    {
        return $this->partnerGet('/account/info', ['account_id' => $accountId]);
    }

    /**
     * Kontostand-Historie (Equity-Kurve).
     *
     * Endpunkt: GET /account/equity-history
     *
     * @param string $accountId
     * @param string $from       Y-m-d
     * @param string $to         Y-m-d
     */
    public function getEquityHistory(string $accountId, string $from, string $to): array
    {
        return $this->partnerGet('/account/equity-history', [
            'account_id' => $accountId,
            'date_from'  => $from,
            'date_to'    => $to,
        ]);
    }

    /**
     * Tägliche Performance (Gewinn/Verlust je Tag).
     *
     * Endpunkt: GET /account/daily-performance
     *
     * @param string $accountId
     * @param string $from  Y-m-d
     * @param string $to    Y-m-d
     *
     * Rückgabe je Tag: date, profit, pips, trades_count, win_rate
     */
    public function getDailyPerformance(string $accountId, string $from, string $to): array
    {
        return $this->partnerGet('/account/daily-performance', [
            'account_id' => $accountId,
            'date_from'  => $from,
            'date_to'    => $to,
        ]);
    }

    /**
     * Gesamt-Performance-Statistiken.
     *
     * Endpunkt: GET /account/statistics
     *
     * Rückgabe: gain, max_drawdown, profit_factor, sharpe_ratio,
     *           win_rate, avg_win, avg_loss, total_trades etc.
     *
     * @param string $accountId
     */
    public function getStatistics(string $accountId): array
    {
        return $this->partnerGet('/account/statistics', ['account_id' => $accountId]);
    }

    /**
     * Alle Partner-Konten abrufen.
     *
     * Endpunkt: GET /accounts
     * Rückgabe: Liste aller verknüpften Konten mit Basisinfos.
     */
    public function getAllAccounts(): array
    {
        return $this->partnerGet('/accounts');
    }

    // =========================================================================
    // TRADE HISTORY – Geschlossene Trades
    // =========================================================================

    /**
     * Geschlossene Trades abrufen.
     *
     * Endpunkt: GET /account/trades/closed
     *
     * @param string $accountId
     * @param string $from       Y-m-d
     * @param string $to         Y-m-d
     * @param int    $limit      Max. Anzahl Trades (Standard: 100)
     * @param int    $offset     Pagination-Offset
     *
     * Rückgabe je Trade: ticket, symbol, type (buy/sell), volume,
     *   open_time, close_time, open_price, close_price,
     *   stop_loss, take_profit, profit, swap, commission, comment, magic
     */
    public function getClosedTrades(
        string $accountId,
        string $from,
        string $to,
        int    $limit  = 100,
        int    $offset = 0
    ): array {
        return $this->partnerGet('/account/trades/closed', [
            'account_id' => $accountId,
            'date_from'  => $from,
            'date_to'    => $to,
            'limit'      => $limit,
            'offset'     => $offset,
        ]);
    }

    /**
     * Offene Trades / Positionen abrufen.
     *
     * Endpunkt: GET /account/trades/open
     *
     * @param string $accountId
     *
     * Rückgabe je Position: ticket, symbol, type, volume,
     *   open_time, open_price, stop_loss, take_profit,
     *   current_price, profit, swap, margin
     */
    public function getOpenTrades(string $accountId): array
    {
        return $this->partnerGet('/account/trades/open', ['account_id' => $accountId]);
    }

    /**
     * Ausstehende Orders abrufen.
     *
     * Endpunkt: GET /account/orders/pending
     *
     * @param string $accountId
     */
    public function getPendingOrders(string $accountId): array
    {
        return $this->partnerGet('/account/orders/pending', ['account_id' => $accountId]);
    }

    // =========================================================================
    // FINANZEN – Einzahlungen, Auszahlungen
    // =========================================================================

    /**
     * Einzahlungs-Historie.
     *
     * Endpunkt: GET /account/deposits
     *
     * @param string $accountId
     * @param string $from  Y-m-d
     * @param string $to    Y-m-d
     */
    public function getDeposits(string $accountId, string $from, string $to): array
    {
        return $this->partnerGet('/account/deposits', [
            'account_id' => $accountId,
            'date_from'  => $from,
            'date_to'    => $to,
        ]);
    }

    /**
     * Auszahlungs-Historie.
     *
     * Endpunkt: GET /account/withdrawals
     *
     * @param string $accountId
     * @param string $from  Y-m-d
     * @param string $to    Y-m-d
     */
    public function getWithdrawals(string $accountId, string $from, string $to): array
    {
        return $this->partnerGet('/account/withdrawals', [
            'account_id' => $accountId,
            'date_from'  => $from,
            'date_to'    => $to,
        ]);
    }

    // =========================================================================
    // PARTNER-PROVISIONEN
    // =========================================================================

    /**
     * Partner-Provisionen abrufen.
     *
     * Endpunkt: GET /partner/commissions
     *
     * @param string $from  Y-m-d
     * @param string $to    Y-m-d
     */
    public function getCommissions(string $from, string $to): array
    {
        return $this->partnerGet('/partner/commissions', [
            'date_from' => $from,
            'date_to'   => $to,
        ]);
    }

    /**
     * Empfohlene Clients abrufen (Partner).
     *
     * Endpunkt: GET /partner/clients
     */
    public function getPartnerClients(): array
    {
        return $this->partnerGet('/partner/clients');
    }

    // =========================================================================
    // cTRADER OPEN API (für cTrader-Konten)
    // Voraussetzung: OAuth2-Token via https://connect.spotware.com
    // =========================================================================

    /**
     * cTrader-Konto-Liste abrufen.
     * Benötigt: ctraderToken + clientId
     *
     * Endpunkt: GET /tradingaccounts
     */
    public function getCtraderAccounts(): array
    {
        if (!$this->ctraderToken) {
            return $this->noTokenError('cTrader');
        }
        return $this->ctraderGet('/tradingaccounts');
    }

    /**
     * cTrader Konto-Übersicht (Balance, Equity, Margin).
     *
     * Endpunkt: GET /tradingaccounts/{accountId}
     *
     * @param int $ctAccountId  cTrader Account ID (numerisch)
     */
    public function getCtraderAccountInfo(int $ctAccountId): array
    {
        if (!$this->ctraderToken) {
            return $this->noTokenError('cTrader');
        }
        return $this->ctraderGet('/tradingaccounts/' . $ctAccountId);
    }

    /**
     * cTrader Trade-History.
     *
     * Endpunkt: GET /tradingaccounts/{accountId}/deals
     *
     * @param int    $ctAccountId
     * @param int    $fromTimestamp  Unix Timestamp in ms
     * @param int    $toTimestamp    Unix Timestamp in ms
     */
    public function getCtraderDeals(int $ctAccountId, int $fromTimestamp, int $toTimestamp): array
    {
        if (!$this->ctraderToken) {
            return $this->noTokenError('cTrader');
        }
        return $this->ctraderGet('/tradingaccounts/' . $ctAccountId . '/deals', [
            'from' => $fromTimestamp,
            'to'   => $toTimestamp,
        ]);
    }

    // =========================================================================
    // LIVE-DATEN HILFSMETHODE – für die Trading-Dashboard-Seite
    // =========================================================================

    /**
     * Kompakte Zusammenfassung für das Dashboard:
     * Kontostand, offene P&L, heutige Rendite.
     * Kombiniert getAccountInfo() + getOpenTrades() + getDailyPerformance().
     *
     * Rückgabe-Struktur:
     * {
     *   balance:       float,
     *   equity:        float,
     *   floating_pl:   float,
     *   today_profit:  float|null,
     *   today_return:  float|null,   ← in % (profit / balance_yesterday * 100)
     *   open_trades:   int,
     *   currency:      string
     * }
     *
     * @param string $accountId
     */
    public function getDashboardSummary(string $accountId): array
    {
        $today     = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        // Kontoinfo holen
        $infoResult = $this->getAccountInfo($accountId);
        if (!$infoResult['success']) return $infoResult;

        // Tagesperformance holen
        $perfResult = $this->getDailyPerformance($accountId, $today, $today);

        // Offene Trades holen
        $openResult = $this->getOpenTrades($accountId);

        $info = $infoResult['data'] ?? [];
        $perf = $perfResult['data'][0] ?? null;
        $open = $openResult['data']    ?? [];

        $balance    = (float) ($info['balance']  ?? 0);
        $equity     = (float) ($info['equity']   ?? 0);
        $floatingPl = $equity - $balance;

        $todayProfit = $perf ? (float) ($perf['profit'] ?? 0) : null;
        $balancePrev = $balance - ($todayProfit ?? 0);
        $todayReturn = ($balancePrev > 0 && $todayProfit !== null)
                       ? round(($todayProfit / $balancePrev) * 100, 4)
                       : null;

        return [
            'success' => true,
            'data'    => [
                'balance'      => $balance,
                'equity'       => $equity,
                'floating_pl'  => round($floatingPl, 2),
                'today_profit' => $todayProfit,
                'today_return' => $todayReturn,
                'open_trades'  => count($open),
                'currency'     => $info['currency'] ?? 'USD',
            ],
            'message'   => 'OK',
            'http_code' => 200,
        ];
    }

    // =========================================================================
    // HILFSMETHODEN (private)
    // =========================================================================

    /**
     * Partner-API GET mit Bearer-Token.
     */
    private function partnerGet(string $endpoint, array $params = []): array
    {
        $url = self::PARTNER_API_URL . $endpoint;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $this->httpGet($url, [
            'Authorization: Bearer ' . $this->apiToken,
            'Accept: application/json',
        ]);
    }

    /**
     * cTrader Open API GET.
     */
    private function ctraderGet(string $endpoint, array $params = []): array
    {
        $params['token']    = $this->ctraderToken;
        $params['clientId'] = $this->clientId;

        $url = self::CTRADER_API_URL . $endpoint . '?' . http_build_query($params);
        return $this->httpGet($url, ['Accept: application/json']);
    }

    /**
     * Universeller HTTP GET via cURL.
     */
    private function httpGet(string $url, array $headers = []): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT      => 'Monvesto-Dashboard/1.0',
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_FOLLOWLOCATION => false,
        ]);

        $raw      = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $curlErr) {
            error_log('[RoboforexApi] cURL-Fehler: ' . $curlErr);
            return ['success' => false, 'data' => null, 'message' => 'Netzwerkfehler: ' . $curlErr, 'http_code' => 0];
        }

        $decoded = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('[RoboforexApi] JSON-Fehler | Response: ' . substr($raw, 0, 200));
            return ['success' => false, 'data' => null, 'message' => 'Ungültige API-Antwort.', 'http_code' => $httpCode];
        }

        // Fehlerfelder die RoboForex typischerweise sendet
        if (isset($decoded['error']) || $httpCode >= 400) {
            $msg = $decoded['message'] ?? $decoded['error'] ?? 'API-Fehler (HTTP ' . $httpCode . ')';
            return ['success' => false, 'data' => $decoded, 'message' => $msg, 'http_code' => $httpCode];
        }

        return ['success' => true, 'data' => $decoded, 'message' => 'OK', 'http_code' => $httpCode];
    }

    /**
     * Fehler wenn Token fehlt.
     */
    private function noTokenError(string $service): array
    {
        return [
            'success'   => false,
            'data'      => null,
            'message'   => $service . '-Token nicht konfiguriert.',
            'http_code' => 0,
        ];
    }
}


// =============================================================================
// KONFIGURATION – in bootstrap.php oder config.php definieren
// =============================================================================
//
// Partner API Token: https://my.roboforex.com → API → Token generieren
//
// define('ROBOFOREX_API_TOKEN',      'dein_partner_api_token');
// define('ROBOFOREX_ACCOUNT_MAIN',   '12345678');   // Haupt-Account-Nr.
// define('ROBOFOREX_ACCOUNT_EA',     '23456789');   // EA-Account-Nr.
// define('ROBOFOREX_ACCOUNT_CHALLENGE', '34567890');
//
// cTrader (nur wenn du cTrader-Konten hast):
// define('CTRADER_CLIENT_ID',    'deine_app_client_id');
// define('CTRADER_ACCESS_TOKEN', 'dein_access_token');
//
// Verwendung im Dashboard:
//
//   $rf = new RoboforexApi(ROBOFOREX_API_TOKEN);
//
//   // Dashboard-Zusammenfassung (Balance + heutige Rendite)
//   $summary = $rf->getDashboardSummary(ROBOFOREX_ACCOUNT_EA);
//   if ($summary['success']) {
//       $todayReturn = $summary['data']['today_return']; // % für save.php
//   }
//
//   // Direkt in save.php einbinden um Rendite automatisch vorzufüllen:
//   // require_once __DIR__ . '/api/roboforex.php';
//   // $rf = new RoboforexApi(ROBOFOREX_API_TOKEN);
//   // $live = $rf->getDashboardSummary(ROBOFOREX_ACCOUNT_EA);