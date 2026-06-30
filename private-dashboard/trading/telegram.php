<?php
/**
 * trading/telegram.php
 * =====================
 * Telegram Bot API Wrapper für den Trading Autoposter.
 *
 * Verwendung:
 *   require_once __DIR__ . '/telegram.php';
 *   $tg = new TelegramBot(TELEGRAM_BOT_TOKEN, TELEGRAM_CHANNEL_ID);
 *   $tg->sendPhoto('/pfad/zur/grafik.png', 'Caption Text');
 */

class TelegramBot
{
    private const API_BASE = 'https://api.telegram.org/bot';
    private const TIMEOUT  = 30;

    private string $token;
    private string $chatId;

    public function __construct(string $token, string $chatId)
    {
        $this->token  = $token;
        $this->chatId = $chatId;
    }

    // ── Foto + Caption senden ─────────────────────────────────────────────────
    /**
     * Sendet ein Foto mit Caption an den Channel.
     *
     * @param  string $imagePath  Absoluter Pfad zur PNG-Datei
     * @param  string $caption    Text unter dem Bild (max. 1024 Zeichen, Markdown erlaubt)
     * @param  string $parseMode  'MarkdownV2' | 'HTML' | ''
     * @return array  ['success' => bool, 'message' => string, 'data' => array]
     */
    public function sendPhoto(string $imagePath, string $caption = '', string $parseMode = 'HTML'): array
    {
        if (!file_exists($imagePath)) {
            return ['success' => false, 'message' => 'Image file not found: ' . $imagePath];
        }

        $url  = self::API_BASE . $this->token . '/sendPhoto';
        $data = [
            'chat_id'    => $this->chatId,
            'photo'      => new CURLFile($imagePath, 'image/png', basename($imagePath)),
        ];

        if ($caption !== '') {
            $data['caption']    = $caption;
            $data['parse_mode'] = $parseMode;
        }

        return $this->post($url, $data, true);
    }

    // ── Nur Text senden ───────────────────────────────────────────────────────
    /**
     * Sendet eine reine Textnachricht.
     */
    public function sendMessage(string $text, string $parseMode = 'HTML'): array
    {
        $url  = self::API_BASE . $this->token . '/sendMessage';
        $data = [
            'chat_id'    => $this->chatId,
            'text'       => $text,
            'parse_mode' => $parseMode,
        ];
        return $this->post($url, $data);
    }

    // ── Bot-Info abrufen (Test) ───────────────────────────────────────────────
    public function getMe(): array
    {
        $url = self::API_BASE . $this->token . '/getMe';
        return $this->get($url);
    }

    // ── Channel-Info abrufen (Test) ───────────────────────────────────────────
    public function getChat(): array
    {
        $url = self::API_BASE . $this->token . '/getChat?chat_id=' . urlencode($this->chatId);
        return $this->get($url);
    }

    // ── HTTP POST ─────────────────────────────────────────────────────────────
    private function post(string $url, array $data, bool $multipart = false): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $multipart ? $data : http_build_query($data),
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $raw  = curl_exec($ch);
        $err  = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $err) {
            error_log('[TelegramBot] cURL error: ' . $err);
            return ['success' => false, 'message' => 'Network error: ' . $err];
        }

        $decoded = json_decode($raw, true);
        if (!$decoded) {
            return ['success' => false, 'message' => 'Invalid JSON response'];
        }

        if (!$decoded['ok']) {
            $msg = $decoded['description'] ?? 'Unknown Telegram error';
            error_log('[TelegramBot] API error: ' . $msg);
            return ['success' => false, 'message' => $msg, 'data' => $decoded];
        }

        return ['success' => true, 'message' => 'OK', 'data' => $decoded['result'] ?? []];
    }

    // ── HTTP GET ──────────────────────────────────────────────────────────────
    private function get(string $url): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $err) {
            return ['success' => false, 'message' => 'Network error: ' . $err];
        }
        $decoded = json_decode($raw, true);
        if (!$decoded || !$decoded['ok']) {
            return ['success' => false, 'message' => $decoded['description'] ?? 'Error'];
        }
        return ['success' => true, 'message' => 'OK', 'data' => $decoded['result'] ?? []];
    }
}

// ── Caption Builder ───────────────────────────────────────────────────────────
/**
 * Baut die ausführliche HTML-Caption für den Telegram-Post.
 *
 * @param  array $entry   Zeile aus trading_daily_updates
 * @param  array $stats   ['main'=>['all'=>float,'week'=>float], 'ea'=>..., 'challenge'=>...]
 * @param  array $settings Account-Einstellungen
 * @param  float|null $challengeBal Aktueller Challenge-Kontostand
 * @return string HTML-formatierte Caption
 */
function tgPct(?float $v): string {
    if ($v === null) return '–';
    return ($v >= 0 ? '+' : '') . number_format($v, 2, '.', ',') . '%';
}
function tgEur(?float $v, string $cur = 'EUR'): string {
    if ($v === null) return '–';
    return ($v >= 0 ? '+' : '') . number_format($v, 2, '.', ',') . ' ' . $cur;
}

function buildTelegramCaption(array $entry, array $stats, array $settings, ?float $challengeBal): string
{
    $date = date('d.m.Y', strtotime($entry['entry_date']));
    $day  = $entry['trading_day'];

    $accounts = [
        'main'      => [
            'label'  => 'Main Account',
            'ret'    => 'main_account_return',
            'profit' => 'main_account_profit',
            'link'   => 'https://www.myfxbook.com/members/Monvesto/monvesto-main-account/12095621',
        ],
        'ea'        => [
            'label'  => 'Low Risk Account',
            'ret'    => 'ea_account_return',
            'profit' => 'ea_account_profit',
            'link'   => 'https://www.myfxbook.com/members/Monvesto/monvesto-low-risk/12095622',
        ],
        'challenge' => [
            'label'  => 'Road to 100k',
            'ret'    => 'challenge_account_return',
            'profit' => 'challenge_account_profit',
            'link'   => 'https://www.myfxbook.com/members/Monvesto/monvesto-road-100k-challenge/12095625',
        ],
    ];

    $lines = [];
    $lines[] = "❗️ <b>Daily Results</b> ❗️";
    $lines[] = "📅 " . $date . "  |  Day " . $day;
    $lines[] = "";

    foreach ($accounts as $key => $acc) {
        $cur    = $settings[$key]['currency'] ?? 'EUR';
        $today  = isset($entry[$acc['ret']])    && $entry[$acc['ret']]    !== null ? (float)$entry[$acc['ret']]    : null;
        $profit = isset($entry[$acc['profit']]) && $entry[$acc['profit']] !== null ? (float)$entry[$acc['profit']] : null;
        $allRet = $stats[$key]['all']  ?? null;
        $wkRet  = $stats[$key]['week'] ?? null;

        $emoji = ($today === null || $today >= 0) ? '🟢' : '🔴';

        $lines[] = $emoji . " <b>" . $acc['label'] . "</b>";
        $lines[] = "➡️ " . $acc['link'];
        $lines[] = "  Today:  <b>" . tgPct($today) . "</b>  (" . tgEur($profit, $cur) . ")";
        $lines[] = "  Week:   " . tgPct($wkRet);
        $lines[] = "  Total:  " . tgPct($allRet);

        // Challenge: Kontostand + Progress
        if ($key === 'challenge' && $challengeBal !== null) {
            $pct    = min(100, $challengeBal / 1000);
            $filled = (int)($pct / 5);
            $bar    = str_repeat('█', $filled) . str_repeat('░', 20 - $filled);
            $lines[] = "  " . $bar . " " . number_format($pct, 1) . "%";
            $lines[] = "  " . number_format($challengeBal, 2, '.', ',') . " / 100,000 " . $cur;
        }

        $lines[] = "";
    }

    $lines[] = "⚠️ <b>Get REBATE + FREE 30$ CASH after verification!</b>";
    $lines[] = "💰 Broker Link: https://my.roboforex.com/de/?a=cslx";
    $lines[] = "💰 Affiliate Code: <b>CSLX</b>";
    $lines[] = "";
    $lines[] = "Stay tuned for Daily Updates ✔️";
    $lines[] = "#RoadTo100K #CopyTrading #Challenge #TradingJourney";
    $lines[] = "";
    $lines[] = "ℹ️ <i>All important info can be found in the pinned message.</i>";

    return implode("\n", $lines);
}