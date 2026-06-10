<?php
/**
 * Monvesto Track Record – Datendatei
 * Start: 01.07.2026
 *
 * Pflege: Neuen Monat unten einfügen (Kommentar entfernen),
 * summary-Werte aktualisieren, last_update setzen.
 */

return [

    /* ===============================================================
       TRADING
       =============================================================== */
    'trading' => [
        'meta' => [
            'name'           => 'Trading',
            'icon'           => '📈',
            'start_date'     => '01.07.2026',
            'last_update'    => '10.06.2026',
            'currency'       => 'EUR',
            'starting_value' => 4000.00,
        ],
        'summary' => [
            'current_value' => 4000.00,
            'month_return'  => 0.0,
            'ytd_return'    => 0.0,
            'max_drawdown'  => 0.0,
            'win_rate'      => null,
        ],
        'monthly_results' => [
            ['period' => 'Juni 2026',     'return_pct' =>  0.0,  'pnl' =>   0.00, 'drawdown_pct' =>  0.0, 'comment' => 'Start – erste Trades in Vorbereitung.'],
            // ['period' => 'Juli 2026',     'return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
            // ['period' => 'August 2026',   'return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
            // ['period' => 'September 2026','return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
            // ['period' => 'Oktober 2026',  'return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
            // ['period' => 'November 2026', 'return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
            // ['period' => 'Dezember 2026', 'return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
        ],
    ],

    /* ===============================================================
       AKTIEN
       =============================================================== */
    'aktien' => [
        'meta' => [
            'name'           => 'Aktien',
            'icon'           => '🏢',
            'start_date'     => '01.07.2026',
            'last_update'    => '10.06.2026',
            'currency'       => 'EUR',
            'starting_value' => 1000.00,
        ],
        'summary' => [
            'current_value' => 1000.00,
            'month_return'  => 0.0,
            'ytd_return'    => 0.0,
            'max_drawdown'  => 0.0,
            'win_rate'      => null,
        ],
        'monthly_results' => [
            ['period' => 'Juni 2026',     'return_pct' =>  0.0,  'pnl' =>   0.00, 'drawdown_pct' =>  0.0, 'comment' => 'Start – Positionen im Aufbau.'],
            // ['period' => 'Juli 2026',     'return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
            // ['period' => 'August 2026',   'return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
            // ['period' => 'September 2026','return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
            // ['period' => 'Oktober 2026',  'return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
            // ['period' => 'November 2026', 'return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
            // ['period' => 'Dezember 2026', 'return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
        ],
    ],

    /* ===============================================================
       ETF
       =============================================================== */
    'etf' => [
        'meta' => [
            'name'           => 'ETFs',
            'icon'           => '🌍',
            'start_date'     => '01.07.2026',
            'last_update'    => '10.06.2026',
            'currency'       => 'EUR',
            'starting_value' => 1000.00,
        ],
        'summary' => [
            'current_value' => 1000.00,
            'month_return'  => 0.0,
            'ytd_return'    => 0.0,
            'max_drawdown'  => 0.0,
            'win_rate'      => null,
        ],
        'monthly_results' => [
            ['period' => 'Juni 2026',     'return_pct' =>  0.0,  'pnl' =>   0.00, 'drawdown_pct' =>  0.0, 'comment' => 'Start – Sparpläne eingerichtet.'],
            // ['period' => 'Juli 2026',     'return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
            // ['period' => 'August 2026',   'return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
            // ['period' => 'September 2026','return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
            // ['period' => 'Oktober 2026',  'return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
            // ['period' => 'November 2026', 'return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
            // ['period' => 'Dezember 2026', 'return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
        ],
    ],

    /* ===============================================================
       KRYPTO
       =============================================================== */
    'krypto' => [
        'meta' => [
            'name'           => 'Krypto',
            'icon'           => '₿',
            'start_date'     => '01.07.2026',
            'last_update'    => '10.06.2026',
            'currency'       => 'EUR',
            'starting_value' => 1000.00,
        ],
        'summary' => [
            'current_value' => 1000.00,
            'month_return'  => 0.0,
            'ytd_return'    => 0.0,
            'max_drawdown'  => 0.0,
            'win_rate'      => null,
        ],
        'monthly_results' => [
            ['period' => 'Juni 2026',     'return_pct' =>  0.0,  'pnl' =>   0.00, 'drawdown_pct' =>  0.0, 'comment' => 'Start – BTC & ETH Position aufgebaut.'],
            // ['period' => 'Juli 2026',     'return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
            // ['period' => 'August 2026',   'return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
            // ['period' => 'September 2026','return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
            // ['period' => 'Oktober 2026',  'return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
            // ['period' => 'November 2026', 'return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
            // ['period' => 'Dezember 2026', 'return_pct' =>  0.0,  'pnl' =>  0.00, 'drawdown_pct' =>  0.0, 'comment' => ''],
        ],
    ],

];