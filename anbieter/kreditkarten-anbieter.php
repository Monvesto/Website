<?php

return [

  // ─────────────────────────────────────────────
  // 01. Barclays Visa
  // ─────────────────────────────────────────────
  [
    'rank'        => 1,
    'show_top'    => true,
    'featured'    => true,

    'name'        => 'Barclays Visa',
    'table_name'  => 'Barclays Visa',
    'type'        => 'Visa',

    'annual_fee'       => '0 €',
    'annual_fee_class' => 'tag-green',
    'cashback'         => '–',
    'fx_free'          => true,
    'insurance'        => false,
    'suitable_for'     => 'Reise · Alltag',

    'table_cashback'        => '–',
    'table_cashback_class'  => 'dash',
    'table_fx'              => '✓',
    'table_fx_class'        => 'check',
    'table_insurance'       => '–',
    'table_insurance_class' => 'dash',

    'badge'       => 'Testsieger Kostenlos',
    'description' => 'Dauerhaft kostenlose Kreditkarte ohne Jahresgebühr, mit weltweiter Akzeptanz und ohne Fremdwährungsgebühren. Besonders geeignet für Alltag und Reisen.',
    'tags' => [
      ['text' => '0 € Jahresgebühr',  'class' => 'tag-green'],
      ['text' => 'Keine FX-Gebühren', 'class' => 'tag-green'],
      ['text' => 'Weltweit nutzbar',  'class' => ''],
      ['text' => 'App: Gut',          'class' => ''],
    ],

    'stars'         => '★★★★★',
    'score'         => '4,8',
    'url'           => 'https://www.barclays.de/?ref=monvesto',
    'button'        => 'Jetzt beantragen →',
    'table_button'  => 'Beantragen',
    'detail_anchor' => '#barclays-detail',
  ],

  // ─────────────────────────────────────────────
  // 02. DKB Visa
  // ─────────────────────────────────────────────
  [
    'rank'        => 2,
    'show_top'    => true,
    'featured'    => false,

    'name'        => 'DKB Visa',
    'table_name'  => 'DKB Visa',
    'type'        => 'Visa',

    'annual_fee'       => '0 €',
    'annual_fee_class' => 'tag-green',
    'cashback'         => '–',
    'fx_free'          => true,
    'insurance'        => false,
    'suitable_for'     => 'Studenten · Reise',

    'table_cashback'        => '–',
    'table_cashback_class'  => 'dash',
    'table_fx'              => '✓',
    'table_fx_class'        => 'check',
    'table_insurance'       => '–',
    'table_insurance_class' => 'dash',

    'badge'       => 'Mit Girokonto',
    'description' => 'Kreditkarte im DKB-Ökosystem mit Girokonto-Anbindung. Besonders interessant für Studenten, Reisende und Nutzer, die Banking und Karte zusammen nutzen möchten.',
    'tags' => [
      ['text' => '0 € Jahresgebühr',      'class' => 'tag-green'],
      ['text' => 'Kostenloses Girokonto', 'class' => 'tag-green'],
      ['text' => 'Weltweit abheben',      'class' => ''],
    ],

    'stars'         => '★★★★★',
    'score'         => '4,6',
    'url'           => 'https://www.dkb.de/?ref=monvesto',
    'button'        => 'Jetzt beantragen →',
    'table_button'  => 'Beantragen',
    'detail_anchor' => '#dkb-detail',
  ],

  // ─────────────────────────────────────────────
  // 03. American Express Gold
  // ─────────────────────────────────────────────
  [
    'rank'        => 3,
    'show_top'    => true,
    'featured'    => false,

    'name'        => 'American Express Gold',
    'table_name'  => 'Amex Gold',
    'type'        => 'American Express',

    'annual_fee'       => '152 €',
    'annual_fee_class' => 'tag-amber',
    'cashback'         => 'Punkte',
    'fx_free'          => false,
    'insurance'        => true,
    'suitable_for'     => 'Vielkäufer · Reise',

    'table_cashback'        => '✓',
    'table_cashback_class'  => 'check',
    'table_fx'              => '–',
    'table_fx_class'        => 'dash',
    'table_insurance'       => '✓',
    'table_insurance_class' => 'check',

    'badge'       => 'Cashback & Punkte',
    'description' => 'Premium-Kreditkarte mit Membership Rewards, Reiseversicherungen und Willkommensbonus. Geeignet für Vielkäufer und Reisende, die aktiv Punkte sammeln möchten.',
    'tags' => [
      ['text' => 'Cashback / Punkte',  'class' => 'tag-amber'],
      ['text' => 'Reiseversicherung',  'class' => 'tag-blue'],
      ['text' => '152 € Jahresgebühr', 'class' => ''],
      ['text' => 'Willkommensbonus',   'class' => ''],
    ],

    'stars'         => '★★★★☆',
    'score'         => '4,4',
    'url'           => 'https://www.americanexpress.com/de/?ref=monvesto',
    'button'        => 'Jetzt beantragen →',
    'table_button'  => 'Beantragen',
    'detail_anchor' => '#amex-detail',
  ],

  // ─────────────────────────────────────────────
  // 04. Payback Visa
  // ─────────────────────────────────────────────
  [
    'rank'        => 4,
    'show_top'    => true,
    'featured'    => false,

    'name'        => 'Payback Visa',
    'table_name'  => 'Payback Visa',
    'type'        => 'Amex / Visa',

    'annual_fee'       => '0 €',
    'annual_fee_class' => 'tag-green',
    'cashback'         => 'Punkte',
    'fx_free'          => false,
    'insurance'        => false,
    'suitable_for'     => 'Alltag · Punkte',

    'table_cashback'        => '✓',
    'table_cashback_class'  => 'check',
    'table_fx'              => '–',
    'table_fx_class'        => 'dash',
    'table_insurance'       => '–',
    'table_insurance_class' => 'dash',

    'badge'       => 'Alltagsvorteile',
    'description' => 'Kreditkarte für den Alltag mit Payback-Punkten bei Einkäufen und Partner-Vorteilen. Geeignet für Nutzer, die regelmäßig Punkte sammeln möchten.',
    'tags' => [
      ['text' => '0 € Jahresgebühr', 'class' => 'tag-green'],
      ['text' => 'Payback-Punkte',   'class' => 'tag-amber'],
      ['text' => 'Partner-Vorteile', 'class' => ''],
    ],

    'stars'         => '★★★★☆',
    'score'         => '4,2',
    'url'           => 'https://www.payback.de/karte/?ref=monvesto',
    'button'        => 'Jetzt beantragen →',
    'table_button'  => 'Beantragen',
    'detail_anchor' => '#payback-detail',
  ],

  // ─────────────────────────────────────────────
  // 05. Lufthansa Miles & More
  // ─────────────────────────────────────────────
  [
    'rank'        => 5,
    'show_top'    => true,
    'featured'    => false,

    'name'        => 'Lufthansa Miles & More',
    'table_name'  => 'Miles & More',
    'type'        => 'Mastercard',

    'annual_fee'       => '109 €',
    'annual_fee_class' => 'tag-amber',
    'cashback'         => 'Meilen',
    'fx_free'          => false,
    'insurance'        => true,
    'suitable_for'     => 'Vielflieger',

    'table_cashback'        => '✓',
    'table_cashback_class'  => 'check',
    'table_fx'              => '–',
    'table_fx_class'        => 'dash',
    'table_insurance'       => '✓',
    'table_insurance_class' => 'check',

    'badge'       => 'Für Vielflieger',
    'description' => 'Kreditkarte zum Sammeln von Meilen bei Einkäufen. Interessant für Vielflieger, die Flüge, Upgrades und Reisevorteile stärker nutzen möchten.',
    'tags' => [
      ['text' => 'Meilen sammeln',      'class' => 'tag-blue'],
      ['text' => 'Lounge-Zugang',       'class' => 'tag-blue'],
      ['text' => '109 € Jahresgebühr',  'class' => ''],
      ['text' => 'Reiseversicherung',   'class' => ''],
    ],

    'stars'         => '★★★★☆',
    'score'         => '4,1',
    'url'           => 'https://www.miles-and-more.com/kreditkarte/?ref=monvesto',
    'button'        => 'Jetzt beantragen →',
    'table_button'  => 'Beantragen',
    'detail_anchor' => '#mam-detail',
  ],

];