<?php
/**
 * Monvesto Track Record – Datendatei
 * Manuell pflegbar: Werte hier direkt anpassen.
 * Keine Datenbank, kein API-Aufruf.
 */

return [

    /* ---------------------------------------------------------------
     * META – Allgemeine Informationen
     * --------------------------------------------------------------- */
    'meta' => [
        'start_date'        => '01.01.2024',
        'last_update'       => '01.06.2025',
        'update_frequency'  => 'Wöchentlich / monatlich',
        'currency'          => 'EUR',
        'disclaimer_short'  => 'Manuell aktualisiert – keine Anlageberatung.',
    ],

    /* ---------------------------------------------------------------
     * SUMMARY – Kennzahlen-Übersicht
     * Renditen als Strings mit %-Zeichen, damit Formatierung flexibel bleibt.
     * --------------------------------------------------------------- */
    'summary' => [
        'total_return'    => '+18,4 %',
        'month_return'    => '+1,2 %',
        'ytd_return'      => '+6,8 %',
        'max_drawdown'    => '−7,3 %',
        'win_rate'        => '63 %',
        'starting_value'  => '10.000 €',
        'current_value'   => '11.840 €',
    ],

    /* ---------------------------------------------------------------
     * MONTHLY RESULTS – Monatliche Ergebnisse
     * Reihenfolge = Darstellungsreihenfolge (neueste zuerst empfohlen).
     * --------------------------------------------------------------- */
    'monthly_results' => [
        [
            'period'    => 'Mai 2025',
            'return'    => '+1,2 %',
            'pnl'       => '+138 €',
            'drawdown'  => '−1,0 %',
            'comment'   => 'Ruhiger Monat, leichte Gewinne im Trading-Bereich.',
        ],
        [
            'period'    => 'April 2025',
            'return'    => '+2,9 %',
            'pnl'       => '+321 €',
            'drawdown'  => '−2,1 %',
            'comment'   => 'ETF-Sparpläne liefen stark; Gold-Position weiter im Plus.',
        ],
        [
            'period'    => 'März 2025',
            'return'    => '−1,4 %',
            'pnl'       => '−152 €',
            'drawdown'  => '−4,5 %',
            'comment'   => 'Kurskorrektur im Krypto-Bereich; Trading neutral.',
        ],
        [
            'period'    => 'Februar 2025',
            'return'    => '+3,1 %',
            'pnl'       => '+335 €',
            'drawdown'  => '−1,8 %',
            'comment'   => 'Starker Monat; XAUUSD-Setup gut performt.',
        ],
        [
            'period'    => 'Januar 2025',
            'return'    => '+0,8 %',
            'pnl'       => '+86 €',
            'drawdown'  => '−2,3 %',
            'comment'   => 'Jahresstart konservativ; Positionsaufbau.',
        ],
        [
            'period'    => 'Dezember 2024',
            'return'    => '+4,2 %',
            'pnl'       => '+452 €',
            'drawdown'  => '−1,2 %',
            'comment'   => 'Jahresabschluss stark; ETFs und Krypto positiv.',
        ],
        [
            'period'    => 'November 2024',
            'return'    => '+2,0 %',
            'pnl'       => '+215 €',
            'drawdown'  => '−3,1 %',
            'comment'   => 'Zwischenzeitlicher Drawdown, aber positiver Abschluss.',
        ],
        [
            'period'    => 'Oktober 2024',
            'return'    => '+1,5 %',
            'pnl'       => '+161 €',
            'drawdown'  => '−2,0 %',
            'comment'   => '',
        ],
        [
            'period'    => 'September 2024',
            'return'    => '−0,9 %',
            'pnl'       => '−97 €',
            'drawdown'  => '−7,3 %',
            'comment'   => 'Max. Drawdown im Berichtszeitraum. Risikomanagement gegriffen.',
        ],
        [
            'period'    => 'August 2024',
            'return'    => '+2,6 %',
            'pnl'       => '+280 €',
            'drawdown'  => '−2,5 %',
            'comment'   => '',
        ],
        [
            'period'    => 'Juli 2024',
            'return'    => '+0,4 %',
            'pnl'       => '+43 €',
            'drawdown'  => '−1,5 %',
            'comment'   => 'Sommer, geringe Volatilität.',
        ],
        [
            'period'    => 'Juni 2024',
            'return'    => '+1,1 %',
            'pnl'       => '+118 €',
            'drawdown'  => '−2,0 %',
            'comment'   => '',
        ],
        [
            'period'    => 'Mai 2024',
            'return'    => '+2,3 %',
            'pnl'       => '+247 €',
            'drawdown'  => '−1,6 %',
            'comment'   => 'ETF-Anteil ausgebaut.',
        ],
        [
            'period'    => 'April 2024',
            'return'    => '−1,8 %',
            'pnl'       => '−192 €',
            'drawdown'  => '−5,2 %',
            'comment'   => 'Kurskorrektur Aktienmarkt.',
        ],
        [
            'period'    => 'März 2024',
            'return'    => '+3,8 %',
            'pnl'       => '+406 €',
            'drawdown'  => '−1,0 %',
            'comment'   => 'Sehr starker Monat; Trading und ETFs positiv.',
        ],
        [
            'period'    => 'Februar 2024',
            'return'    => '+1,9 %',
            'pnl'       => '+203 €',
            'drawdown'  => '−2,2 %',
            'comment'   => '',
        ],
        [
            'period'    => 'Januar 2024',
            'return'    => '−0,5 %',
            'pnl'       => '−53 €',
            'drawdown'  => '−3,0 %',
            'comment'   => 'Start; Portfolio aufgebaut, leicht negatives Ergebnis.',
        ],
    ],

    /* ---------------------------------------------------------------
     * ALLOCATION – Portfolio-/Strategie-Aufteilung
     * weight = Prozentzahl (int), ohne %-Zeichen
     * status: 'aktiv' | 'aufbau' | 'beobachtung' | 'pausiert'
     * --------------------------------------------------------------- */
    'allocation' => [
        [
            'name'        => 'Trading',
            'weight'      => 30,
            'description' => 'Algorithmisches und manuelles Trading, primär XAUUSD (Gold) auf M15. Klarer Regelrahmen, definiertes Risiko pro Trade.',
            'status'      => 'aktiv',
        ],
        [
            'name'        => 'Aktien',
            'weight'      => 20,
            'description' => 'Einzelwerte aus Technologie und Industrie. Langfristiger Halteansatz, keine kurzfristige Spekulation.',
            'status'      => 'aktiv',
        ],
        [
            'name'        => 'ETFs',
            'weight'      => 25,
            'description' => 'Monatliche Sparpläne auf breit diversifizierte Welt-ETFs (MSCI World / All Country). Kerninvestment des Portfolios.',
            'status'      => 'aktiv',
        ],
        [
            'name'        => 'Krypto',
            'weight'      => 10,
            'description' => 'Bitcoin und Ethereum als Beimischung. Bewusst kleiner Anteil aufgrund höherer Volatilität.',
            'status'      => 'aktiv',
        ],
        [
            'name'        => 'Cash',
            'weight'      => 15,
            'description' => 'Liquiditätspuffer auf Tagesgeldkonto. Dient zur Opportunitätsnutzung und als Sicherheitspuffer.',
            'status'      => 'aktiv',
        ],
    ],

];