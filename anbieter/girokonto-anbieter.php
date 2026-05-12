<?php

return [

  // ─────────────────────────────────────────────
  // 01. C24 Smart Girokonto
  // ─────────────────────────────────────────────
  [
    'rank'        => 1,
    'category'    => 'girokonto',
    'show_top'    => true,
    'featured'    => true,

    'name'        => 'C24 Smart Girokonto',
    'table_name'  => 'C24',
    'type'        => 'Neobank',

    'fee'            => '0 €',
    'fee_class'      => 'tag-green',
    'interest'       => '✓',
    'card'           => 'Mastercard',
    'withdraw'       => 'Weltweit gratis',
    'withdraw_class' => 'tag-green',
    'suitable_for'   => 'Alle',
    'app'            => 'Sehr gut',
    'app_class'      => 'tag-green',

    'badge'       => 'Testsieger Gesamtpaket',
    'description' => 'Kostenloses Girokonto mit Mastercard, Unterkonten und moderner App. Besonders stark als Alltagskonto für Nutzer, die Banking einfach und digital organisieren möchten.',
    'tags' => [
      ['text' => '0 € Kontoführung',     'class' => 'tag-green'],
      ['text' => 'Mastercard inklusive', 'class' => 'tag-green'],
      ['text' => 'Weltweit abheben',     'class' => 'tag-green'],
      ['text' => 'App: Sehr gut',        'class' => ''],
    ],

    'stars'         => '★★★★★',
    'score'         => '4,8',
    'url'           => '/go/check24/',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#c24-detail',
  ],

  // ─────────────────────────────────────────────
  // 02. ING Girokonto
  // ─────────────────────────────────────────────
  [
    'rank'        => 2,
    'category'    => 'girokonto',
    'show_top'    => true,
    'featured'    => false,

    'name'        => 'ING Girokonto',
    'table_name'  => 'ING',
    'type'        => 'Direktbank',

    'fee'            => '0 € möglich',
    'fee_class'      => 'tag-green',
    'interest'       => '–',
    'card'           => 'Visa Debit',
    'withdraw'       => 'DE gratis',
    'withdraw_class' => 'tag-green',
    'suitable_for'   => 'Alle',
    'app'            => 'Sehr gut',
    'app_class'      => 'tag-green',

    'badge'       => 'Starke Direktbank',
    'description' => 'Girokonto einer etablierten Direktbank mit Visa-Debitkarte, starker App und guter Kombinierbarkeit mit Tagesgeld und Depot.',
    'tags' => [
      ['text' => '0 € möglich',            'class' => 'tag-green'],
      ['text' => 'Visa Debit',             'class' => 'tag-green'],
      ['text' => 'Tagesgeld kombinierbar', 'class' => ''],
    ],

    'stars'         => '★★★★★',
    'score'         => '4,6',
    'url'           => 'https://www.ing.de/girokonto/?ref=monvesto',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#ing-detail',
  ],

  // ─────────────────────────────────────────────
  // 03. Trade Republic Konto
  // ─────────────────────────────────────────────
  [
    'rank'        => 3,
    'category'    => 'girokonto',
    'show_top'    => true,
    'featured'    => false,

    'name'        => 'Trade Republic Konto',
    'table_name'  => 'Trade Republic',
    'type'        => 'Neo-Broker',

    'fee'            => '0 €',
    'fee_class'      => 'tag-green',
    'interest'       => '3,75 %',
    'card'           => 'Visa Debit',
    'withdraw'       => 'Weltweit gratis',
    'withdraw_class' => 'tag-green',
    'suitable_for'   => 'Anleger',
    'app'            => 'Sehr gut',
    'app_class'      => 'tag-green',

    'badge'       => 'Zinsen + Konto',
    'description' => 'Kombination aus Konto, Broker und verzinstem Guthaben. Besonders geeignet für Anleger, die Zahlungsverkehr, Cash und Investments in einer App bündeln möchten.',
    'tags' => [
      ['text' => '3,75 % Zinsen',    'class' => 'tag-green'],
      ['text' => '0 € Kontoführung', 'class' => 'tag-green'],
      ['text' => 'Broker integriert','class' => 'tag-blue'],
      ['text' => 'Visa Debit',       'class' => ''],
    ],

    'stars'         => '★★★★★',
    'score'         => '4,5',
    'url'           => '/go/trade-republic/',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#tr-detail',
  ],

  // ─────────────────────────────────────────────
  // 04. N26 Standard
  // ─────────────────────────────────────────────
  [
    'rank'        => 4,
    'category'    => 'girokonto',
    'show_top'    => true,
    'featured'    => false,

    'name'        => 'N26 Standard',
    'table_name'  => 'N26',
    'type'        => 'Neobank',

    'fee'            => '0 €',
    'fee_class'      => 'tag-green',
    'interest'       => '–',
    'card'           => 'Mastercard Debit',
    'withdraw'       => '3x/Monat gratis',
    'withdraw_class' => 'tag-amber',
    'suitable_for'   => 'Jung · Digital',
    'app'            => 'Sehr gut',
    'app_class'      => 'tag-green',

    'badge'       => 'Digital First',
    'description' => 'Modernes Smartphone-Konto mit Echtzeit-Benachrichtigungen, Unterkonten und optionalen Premium-Paketen. Besonders beliebt bei jungen digitalen Nutzern.',
    'tags' => [
      ['text' => '0 € Grundkonto',       'class' => 'tag-green'],
      ['text' => 'Mastercard Debit',     'class' => 'tag-blue'],
      ['text' => 'Spaces / Unterkonten', 'class' => ''],
    ],

    'stars'         => '★★★★☆',
    'score'         => '4,2',
    'url'           => 'https://n26.com/de-de/?ref=monvesto',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#n26-detail',
  ],

  // ─────────────────────────────────────────────
  // 05. Commerzbank Girokonto
  // ─────────────────────────────────────────────
  [
    'rank'        => 5,
    'category'    => 'girokonto',
    'show_top'    => true,
    'featured'    => false,

    'name'        => 'Commerzbank Girokonto',
    'table_name'  => 'Commerzbank',
    'type'        => 'Großbank',

    'fee'            => '0–12,90 €',
    'fee_class'      => 'tag-amber',
    'interest'       => '–',
    'card'           => 'Visa',
    'withdraw'       => 'DE Filialen',
    'withdraw_class' => '',
    'suitable_for'   => 'Beratung',
    'app'            => 'Gut',
    'app_class'      => '',

    'badge'       => 'Filialbank',
    'description' => 'Klassisches Girokonto einer deutschen Großbank mit Filialnetz, Beratung und breitem Produktangebot. Geeignet für Nutzer, die persönliche Betreuung bevorzugen.',
    'tags' => [
      ['text' => 'Filialnetz vorhanden', 'class' => ''],
      ['text' => 'Visa Kreditkarte',     'class' => 'tag-blue'],
      ['text' => 'Ab 0 € möglich',       'class' => 'tag-amber'],
    ],

    'stars'         => '★★★★☆',
    'score'         => '3,9',
    'url'           => 'https://www.commerzbank.de/girokonto/?ref=monvesto',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#cb-detail',
  ],

  // ─────────────────────────────────────────────
  // 101. Comdirect Junior Giro
  // ─────────────────────────────────────────────
  [
    'rank'        => 101,
    'youth_rank'  => 1,
    'category'    => 'jugendkonto',
    'show_top'    => false,
    'featured'    => true,

    'name'        => 'Comdirect Junior Giro',
    'table_name'  => 'Comdirect Junior Giro',
    'type'        => 'Jugendkonto',

    'fee'            => '0 €',
    'fee_class'      => 'tag-green',
    'interest'       => '–',
    'card'           => 'Visa',
    'withdraw'       => 'DE gratis',
    'withdraw_class' => 'tag-green',
    'suitable_for'   => 'Kinder · Jugendliche',
    'app'            => 'Sehr gut',
    'app_class'      => 'tag-green',

    'badge'       => 'Bestes Jugendkonto',
    'description' => 'Kostenloses Girokonto ab 7 Jahren mit Visa-Karte, modernem Online-Banking und späterem Übergang ins reguläre Girokonto.',
    'tags' => [
      ['text' => 'Ab 7 Jahren',        'class' => 'tag-green'],
      ['text' => '0 € dauerhaft',      'class' => 'tag-green'],
      ['text' => '25 € Startguthaben', 'class' => 'tag-green'],
      ['text' => 'App: Sehr gut',      'class' => ''],
    ],

    'stars'         => '★★★★★',
    'score'         => '4,8',
    'url'           => '/go/comdirect-junior/',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#comdirect-junior-detail',
  ],

  // ─────────────────────────────────────────────
  // 102. Tomorrow Konto
  // ─────────────────────────────────────────────
  [
    'rank'        => 102,
    'youth_rank'  => 2,
    'category'    => 'jugendkonto',
    'show_top'    => false,
    'featured'    => false,

    'name'        => 'Tomorrow Konto',
    'table_name'  => 'Tomorrow',
    'type'        => 'Nachhaltiges Konto',

    'fee'            => '0 € Basis',
    'fee_class'      => 'tag-green',
    'interest'       => '–',
    'card'           => 'Visa Debit',
    'withdraw'       => 'Tarifabhängig',
    'withdraw_class' => 'tag-amber',
    'suitable_for'   => 'Nachhaltig · Digital',
    'app'            => 'Sehr gut',
    'app_class'      => 'tag-green',

    'badge'       => 'Nachhaltig',
    'description' => 'Nachhaltiges Konto mit Fokus auf Klimaschutz und digitalem Banking. Geeignet für junge Nutzer mit Interesse an nachhaltigen Finanzprodukten.',
    'tags' => [
      ['text' => '0 € Basisversion', 'class' => 'tag-green'],
      ['text' => 'Nachhaltig',       'class' => 'tag-purple'],
      ['text' => 'Ab 18 Jahren',     'class' => ''],
    ],

    'stars'         => '★★★★☆',
    'score'         => '4,3',
    'url'           => 'https://tomorrow.one/?ref=monvesto',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#tomorrow-detail',
  ],

  // ─────────────────────────────────────────────
  // 103. Commerzbank Young Account
  // ─────────────────────────────────────────────
  [
    'rank'        => 103,
    'youth_rank'  => 3,
    'category'    => 'jugendkonto',
    'show_top'    => false,
    'featured'    => false,

    'name'        => 'Commerzbank Young Account',
    'table_name'  => 'Commerzbank Young Account',
    'type'        => 'Jugendkonto',

    'fee'            => '0 €',
    'fee_class'      => 'tag-green',
    'interest'       => '–',
    'card'           => 'Visa',
    'withdraw'       => 'DE Filialen',
    'withdraw_class' => '',
    'suitable_for'   => 'Schüler · Studenten',
    'app'            => 'Gut',
    'app_class'      => '',

    'badge'       => 'Mit Filialnetz',
    'description' => 'Kostenloses Konto für Schüler und Studenten mit Filialnetz und Beratung. Interessant für Jugendliche, die persönliche Ansprechpartner wünschen.',
    'tags' => [
      ['text' => '0 € bis 22 Jahre', 'class' => 'tag-green'],
      ['text' => 'Visa Kreditkarte', 'class' => 'tag-blue'],
      ['text' => 'Filialnetz',       'class' => ''],
    ],

    'stars'         => '★★★★☆',
    'score'         => '4,1',
    'url'           => 'https://www.commerzbank.de/youngaccount/?ref=monvesto',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#cb-young-detail',
  ],

  // ─────────────────────────────────────────────
  // 104. Sparkasse JuniorKonto
  // ─────────────────────────────────────────────
  [
    'rank'        => 104,
    'youth_rank'  => 4,
    'category'    => 'jugendkonto',
    'show_top'    => false,
    'featured'    => false,

    'name'        => 'Sparkasse JuniorKonto',
    'table_name'  => 'Sparkasse JuniorKonto',
    'type'        => 'Jugendkonto',

    'fee'            => '0 € möglich',
    'fee_class'      => 'tag-green',
    'interest'       => '–',
    'card'           => 'Sparkassen-Card',
    'withdraw'       => 'Regional',
    'withdraw_class' => '',
    'suitable_for'   => 'Kinder · Einstieg',
    'app'            => 'Gut',
    'app_class'      => '',

    'badge'       => 'Regional',
    'description' => 'Konto für Kinder und Jugendliche mit Elternkontrolle und regionaler Sparkassen-Infrastruktur. Konditionen können je nach Sparkasse variieren.',
    'tags' => [
      ['text' => 'Ab 7 Jahren',        'class' => 'tag-green'],
      ['text' => 'Elternkontrolle',    'class' => ''],
      ['text' => 'Regional verfügbar', 'class' => ''],
    ],

    'stars'         => '★★★★☆',
    'score'         => '3,9',
    'url'           => 'https://www.sparkasse.de/juniorkonto/?ref=monvesto',
    'button'        => 'Zur Sparkasse →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#sparkasse-detail',
  ],

];