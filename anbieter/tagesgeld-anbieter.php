<?php

return [

  // ─────────────────────────────────────────────
  // 01. Trade Republic
  // ─────────────────────────────────────────────
  [
    'rank'        => 1,
    'show_top'    => true,
    'featured'    => true,

    'name'        => 'C24 Konto',
    'table_name'  => 'C24',
    'type'        => 'Neo-Broker',

    'rate'        => '2,00 %',
    'rate_num'    => '2,00 %',
    'rate_label'  => '2,00 % p.a.',
    'promo'       => '–',
    'availability'=> 'täglich',
    'minimum'     => 'Keine',

    'deposit_protection' => 'EU 100k €',
    'deposit_class'      => 'tag-blue',

    'badge'       => 'Broker-Cash',
    'description' => 'Tagesgeldähnliche Verzinsung auf nicht investiertes Guthaben. Besonders interessant für Anleger, die Cash, ETFs und Wertpapiere in einer App verwalten möchten.',
    'tags' => [
      ['text' => '2,00 % p.a.',          'class' => 'tag-green'],
      ['text' => 'Täglich verfügbar',   'class' => 'tag-green'],
      ['text' => 'Keine Mindestanlage', 'class' => ''],
      ['text' => 'Unterkonten integriert',   'class' => 'tag-blue'],
    ],

    'url'           => '/go/check24/',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#check24-detail',
  ],

  // ─────────────────────────────────────────────
  // 02. ING Extra-Konto
  // ─────────────────────────────────────────────
  [
    'rank'        => 2,
    'show_top'    => true,
    'featured'    => false,

    'name'        => 'ING Extra-Konto',
    'table_name'  => 'ING Extra-Konto',
    'type'        => 'Direktbank',

    'rate'        => '3,20 %',
    'rate_num'    => '3,20 %',
    'rate_label'  => 'Bis 3,20 % p.a.',
    'promo'       => '4 Monate',
    'availability'=> 'täglich',
    'minimum'     => 'Keine',

    'deposit_protection' => 'DE 100k €',
    'deposit_class'      => 'tag-green',

    'badge'       => 'Starke Direktbank',
    'description' => 'Etabliertes Tagesgeldkonto einer großen deutschen Direktbank. Gut geeignet für Neukunden, die ein einfaches Tagesgeldkonto mit deutscher Einlagensicherung suchen.',
    'tags' => [
      ['text' => 'Bis 3,20 % p.a.',       'class' => 'tag-green'],
      ['text' => '4 Monate Aktionszins', 'class' => 'tag-amber'],
      ['text' => 'DE-Einlagensicherung', 'class' => ''],
      ['text' => 'Keine Mindestanlage',  'class' => ''],
    ],

    'url'           => 'https://www.ing.de/sparen/?ref=monvesto',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#ing-detail',
  ],

  // ─────────────────────────────────────────────
  // 03. DKB Tagesgeld
  // ─────────────────────────────────────────────
  [
    'rank'        => 3,
    'show_top'    => true,
    'featured'    => false,

    'name'        => 'DKB Tagesgeld',
    'table_name'  => 'DKB Tagesgeld',
    'type'        => 'Direktbank',

    'rate'        => '2,75 %',
    'rate_num'    => '2,75 %',
    'rate_label'  => '2,75 % p.a.',
    'promo'       => 'Aktionszins',
    'availability'=> 'täglich',
    'minimum'     => 'Keine',

    'deposit_protection' => 'DE 100k €',
    'deposit_class'      => 'tag-green',

    'badge'       => 'Für DKB-Kunden',
    'description' => 'Solides Tagesgeldangebot mit deutscher Einlagensicherung. Besonders praktisch für Nutzer, die bereits ein DKB-Konto führen und ihr Geld zentral verwalten möchten.',
    'tags' => [
      ['text' => '2,75 % p.a.',          'class' => 'tag-green'],
      ['text' => 'Aktionszins',         'class' => 'tag-amber'],
      ['text' => 'Täglich verfügbar',   'class' => 'tag-green'],
      ['text' => 'DE-Einlagensicherung','class' => ''],
    ],

    'url'           => 'https://www.dkb.de/sparen/?ref=monvesto',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#dkb-detail',
  ],

  // ─────────────────────────────────────────────
  // 04. Scalable Capital
  // ─────────────────────────────────────────────
  [
    'rank'        => 4,
    'show_top'    => true,
    'featured'    => false,

    'name'        => 'Scalable Capital Zinsen',
    'table_name'  => 'Scalable Zinsen',
    'type'        => 'Neo-Broker',

    'rate'        => '2,50 %',
    'rate_num'    => '2,50 %',
    'rate_label'  => 'Bis 2,50 % p.a.',
    'promo'       => '–',
    'availability'=> 'täglich',
    'minimum'     => 'Keine',

    'deposit_protection' => 'EU 100k €',
    'deposit_class'      => 'tag-blue',

    'badge'       => 'Für Anleger',
    'description' => 'Cash-Zinsen direkt im Broker-Konto. Geeignet für Anleger, die freie Liquidität verzinsen und gleichzeitig ETF-Sparpläne oder Wertpapiere nutzen möchten.',
    'tags' => [
      ['text' => 'Bis 2,50 % p.a.',      'class' => 'tag-green'],
      ['text' => 'Im Broker integriert','class' => 'tag-blue'],
      ['text' => 'Täglich verfügbar',   'class' => 'tag-green'],
      ['text' => 'Für Anleger',         'class' => ''],
    ],

    'url'           => 'https://scalable.capital/?ref=monvesto',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#scalable-detail',
  ],

  // ─────────────────────────────────────────────
  // 05. Comdirect
  // ─────────────────────────────────────────────
  [
    'rank'        => 5,
    'show_top'    => true,
    'featured'    => false,

    'name'        => 'Comdirect Tagesgeld PLUS',
    'table_name'  => 'Comdirect Tagesgeld PLUS',
    'type'        => 'Direktbank',

    'rate'        => '2,25 %',
    'rate_num'    => '2,25 %',
    'rate_label'  => '2,25 % p.a.',
    'promo'       => 'Neukunden',
    'availability'=> 'täglich',
    'minimum'     => 'Keine',

    'deposit_protection' => 'DE 100k €',
    'deposit_class'      => 'tag-green',

    'badge'       => 'Bekannte Direktbank',
    'description' => 'Tagesgeldangebot einer etablierten deutschen Direktbank. Interessant für Kunden, die Tagesgeld, Girokonto und Depot bei einem bekannten Anbieter bündeln möchten.',
    'tags' => [
      ['text' => '2,25 % p.a.',          'class' => 'tag-green'],
      ['text' => 'Neukundenangebot',    'class' => 'tag-amber'],
      ['text' => 'DE-Einlagensicherung','class' => ''],
      ['text' => 'Keine Mindestanlage', 'class' => ''],
    ],

    'url'           => '#',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#',
  ],

  // ─────────────────────────────────────────────
  // 06. Consorsbank
  // ─────────────────────────────────────────────
  [
    'rank'        => 6,
    'show_top'    => false,
    'featured'    => false,

    'name'        => 'Consorsbank Tagesgeld',
    'table_name'  => 'Consorsbank Tagesgeld',
    'type'        => 'Direktbank',

    'rate'        => '2,40 %',
    'rate_num'    => '2,40 %',
    'rate_label'  => '2,40 % p.a.',
    'promo'       => 'Neukunden',
    'availability'=> 'täglich',
    'minimum'     => 'Keine',

    'deposit_protection' => 'DE 100k €',
    'deposit_class'      => 'tag-green',

    'badge'       => 'Neukundenangebot',
    'description' => 'Tagesgeldkonto einer etablierten Direktbank mit Fokus auf Online-Banking und Wertpapiergeschäft. Vor allem für Neukunden mit Aktionszins interessant.',
    'tags' => [
      ['text' => '2,40 % p.a.',          'class' => 'tag-green'],
      ['text' => 'Neukunden',           'class' => 'tag-amber'],
      ['text' => 'Täglich verfügbar',   'class' => 'tag-green'],
      ['text' => 'DE-Einlagensicherung','class' => ''],
    ],

    'url'           => '#',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#',
  ],

  // ─────────────────────────────────────────────
  // 07. Renault Bank direkt
  // ─────────────────────────────────────────────
  [
    'rank'        => 7,
    'show_top'    => false,
    'featured'    => false,

    'name'        => 'Renault Bank direkt',
    'table_name'  => 'Renault Bank direkt',
    'type'        => 'Autobank',

    'rate'        => '2,30 %',
    'rate_num'    => '2,30 %',
    'rate_label'  => '2,30 % p.a.',
    'promo'       => '–',
    'availability'=> 'täglich',
    'minimum'     => 'Keine',

    'deposit_protection' => 'FR 100k €',
    'deposit_class'      => 'tag-blue',

    'badge'       => 'Autobank',
    'description' => 'Tagesgeld einer spezialisierten Autobank mit französischer Einlagensicherung. Geeignet für Sparer, die einen klassischen Tagesgeldanbieter ohne Mindestanlage suchen.',
    'tags' => [
      ['text' => '2,30 % p.a.',          'class' => 'tag-green'],
      ['text' => 'Keine Mindestanlage', 'class' => ''],
      ['text' => 'FR-Einlagensicherung','class' => ''],
      ['text' => 'Autobank',            'class' => 'tag-blue'],
    ],

    'url'           => '#',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#',
  ],

  // ─────────────────────────────────────────────
  // 08. BMW Bank
  // ─────────────────────────────────────────────
  [
    'rank'        => 8,
    'show_top'    => false,
    'featured'    => false,

    'name'        => 'BMW Bank Tagesgeld',
    'table_name'  => 'BMW Bank Tagesgeld',
    'type'        => 'Autobank',

    'rate'        => '2,00 %',
    'rate_num'    => '2,00 %',
    'rate_label'  => '2,00 % p.a.',
    'promo'       => '–',
    'availability'=> 'täglich',
    'minimum'     => 'Keine',

    'deposit_protection' => 'DE 100k €',
    'deposit_class'      => 'tag-green',

    'badge'       => 'Deutsche Autobank',
    'description' => 'Klassisches Tagesgeldangebot einer deutschen Autobank. Interessant für Sparer, die Wert auf deutsche Einlagensicherung und einfache Verfügbarkeit legen.',
    'tags' => [
      ['text' => '2,00 % p.a.',          'class' => 'tag-green'],
      ['text' => 'Täglich verfügbar',   'class' => 'tag-green'],
      ['text' => 'DE-Einlagensicherung','class' => ''],
      ['text' => 'Autobank',            'class' => 'tag-blue'],
    ],

    'url'           => '#',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#',
  ],

  // ─────────────────────────────────────────────
  // 09. Volkswagen Bank
  // ─────────────────────────────────────────────
  [
    'rank'        => 9,
    'show_top'    => false,
    'featured'    => false,

    'name'        => 'Volkswagen Bank Tagesgeld',
    'table_name'  => 'Volkswagen Bank',
    'type'        => 'Autobank',

    'rate'        => '2,10 %',
    'rate_num'    => '2,10 %',
    'rate_label'  => '2,10 % p.a.',
    'promo'       => 'Neukunden',
    'availability'=> 'täglich',
    'minimum'     => 'Keine',

    'deposit_protection' => 'DE 100k €',
    'deposit_class'      => 'tag-green',

    'badge'       => 'Neukundenzins',
    'description' => 'Tagesgeldangebot einer bekannten deutschen Autobank. Für Neukunden interessant, die ein einfaches Sparkonto mit deutscher Absicherung bevorzugen.',
    'tags' => [
      ['text' => '2,10 % p.a.',          'class' => 'tag-green'],
      ['text' => 'Neukunden',           'class' => 'tag-amber'],
      ['text' => 'DE-Einlagensicherung','class' => ''],
      ['text' => 'Keine Mindestanlage', 'class' => ''],
    ],

    'url'           => '#',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#',
  ],

  // ─────────────────────────────────────────────
  // 10. Mercedes-Benz Bank
  // ─────────────────────────────────────────────
  [
    'rank'        => 10,
    'show_top'    => false,
    'featured'    => false,

    'name'        => 'Mercedes-Benz Bank Tagesgeld',
    'table_name'  => 'Mercedes-Benz Bank',
    'type'        => 'Autobank',

    'rate'        => '1,75 %',
    'rate_num'    => '1,75 %',
    'rate_label'  => '1,75 % p.a.',
    'promo'       => '–',
    'availability'=> 'täglich',
    'minimum'     => 'Keine',

    'deposit_protection' => 'DE 100k €',
    'deposit_class'      => 'tag-green',

    'badge'       => 'Solider Klassiker',
    'description' => 'Klassisches Tagesgeldkonto einer deutschen Autobank. Der Fokus liegt weniger auf Maximalzins, sondern auf einfacher Struktur und deutscher Einlagensicherung.',
    'tags' => [
      ['text' => '1,75 % p.a.',          'class' => 'tag-green'],
      ['text' => 'Täglich verfügbar',   'class' => 'tag-green'],
      ['text' => 'DE-Einlagensicherung','class' => ''],
      ['text' => 'Autobank',            'class' => 'tag-blue'],
    ],

    'url'           => '#',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#',
  ],

  // ─────────────────────────────────────────────
  // 11. LeasePlan Bank
  // ─────────────────────────────────────────────
  [
    'rank'        => 11,
    'show_top'    => false,
    'featured'    => false,

    'name'        => 'LeasePlan Bank Tagesgeld',
    'table_name'  => 'LeasePlan Bank',
    'type'        => 'Direktbank',

    'rate'        => '2,20 %',
    'rate_num'    => '2,20 %',
    'rate_label'  => '2,20 % p.a.',
    'promo'       => '–',
    'availability'=> 'täglich',
    'minimum'     => 'Keine',

    'deposit_protection' => 'NL 100k €',
    'deposit_class'      => 'tag-blue',

    'badge'       => 'EU-Direktbank',
    'description' => 'Tagesgeld einer niederländischen Direktbank. Geeignet für Sparer, die ein EU-Tagesgeldkonto ohne Mindestanlage suchen.',
    'tags' => [
      ['text' => '2,20 % p.a.',          'class' => 'tag-green'],
      ['text' => 'Täglich verfügbar',   'class' => 'tag-green'],
      ['text' => 'NL-Einlagensicherung','class' => ''],
      ['text' => 'Keine Mindestanlage', 'class' => ''],
    ],

    'url'           => '#',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#',
  ],

  // ─────────────────────────────────────────────
  // 12. TF Bank
  // ─────────────────────────────────────────────
  [
    'rank'        => 12,
    'show_top'    => false,
    'featured'    => false,

    'name'        => 'TF Bank Tagesgeld',
    'table_name'  => 'TF Bank Tagesgeld',
    'type'        => 'Direktbank',

    'rate'        => '2,35 %',
    'rate_num'    => '2,35 %',
    'rate_label'  => '2,35 % p.a.',
    'promo'       => 'Aktionszins',
    'availability'=> 'täglich',
    'minimum'     => 'Keine',

    'deposit_protection' => 'SE 100k €',
    'deposit_class'      => 'tag-blue',

    'badge'       => 'Aktionszins',
    'description' => 'Tagesgeldkonto einer schwedischen Direktbank. Vor allem für Sparer interessant, die gezielt Aktionszinsen vergleichen und ihr Geld flexibel verfügbar halten möchten.',
    'tags' => [
      ['text' => '2,35 % p.a.',          'class' => 'tag-green'],
      ['text' => 'Aktionszins',         'class' => 'tag-amber'],
      ['text' => 'SE-Einlagensicherung','class' => ''],
      ['text' => 'Täglich verfügbar',   'class' => 'tag-green'],
    ],

    'url'           => '#',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#',
  ],

  // ─────────────────────────────────────────────
  // 13. Advanzia
  // ─────────────────────────────────────────────
  [
    'rank'        => 13,
    'show_top'    => false,
    'featured'    => false,

    'name'        => 'Advanzia Tagesgeld',
    'table_name'  => 'Advanzia Tagesgeld',
    'type'        => 'Direktbank',

    'rate'        => '2,25 %',
    'rate_num'    => '2,25 %',
    'rate_label'  => '2,25 % p.a.',
    'promo'       => 'Neukunden',
    'availability'=> 'täglich',
    'minimum'     => 'Keine',

    'deposit_protection' => 'LU 100k €',
    'deposit_class'      => 'tag-blue',

    'badge'       => 'Luxemburg',
    'description' => 'Tagesgeldangebot einer luxemburgischen Bank. Geeignet für Sparer, die ein EU-Tagesgeldkonto mit täglicher Verfügbarkeit suchen.',
    'tags' => [
      ['text' => '2,25 % p.a.',          'class' => 'tag-green'],
      ['text' => 'Neukunden',           'class' => 'tag-amber'],
      ['text' => 'LU-Einlagensicherung','class' => ''],
      ['text' => 'Keine Mindestanlage', 'class' => ''],
    ],

    'url'           => '#',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#',
  ],

  // ─────────────────────────────────────────────
  // 14. Openbank
  // ─────────────────────────────────────────────
  [
    'rank'        => 14,
    'show_top'    => false,
    'featured'    => false,

    'name'        => 'Openbank Tagesgeld',
    'table_name'  => 'Openbank Tagesgeld',
    'type'        => 'Direktbank',

    'rate'        => '2,00 %',
    'rate_num'    => '2,00 %',
    'rate_label'  => '2,00 % p.a.',
    'promo'       => 'Neukunden',
    'availability'=> 'täglich',
    'minimum'     => 'Keine',

    'deposit_protection' => 'ES 100k €',
    'deposit_class'      => 'tag-blue',

    'badge'       => 'Santander-Gruppe',
    'description' => 'Digitales Tagesgeldangebot aus Spanien. Interessant für Nutzer, die Online-Banking, Tagesgeld und weitere Bankprodukte in einer App kombinieren möchten.',
    'tags' => [
      ['text' => '2,00 % p.a.',          'class' => 'tag-green'],
      ['text' => 'Neukunden',           'class' => 'tag-amber'],
      ['text' => 'ES-Einlagensicherung','class' => ''],
      ['text' => 'Täglich verfügbar',   'class' => 'tag-green'],
    ],

    'url'           => '#',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#',
  ],

  // ─────────────────────────────────────────────
  // 15. Bank11
  // ─────────────────────────────────────────────
  [
    'rank'        => 15,
    'show_top'    => false,
    'featured'    => false,

    'name'        => 'Bank11 Tagesgeld',
    'table_name'  => 'Bank11 Tagesgeld',
    'type'        => 'Direktbank',

    'rate'        => '2,15 %',
    'rate_num'    => '2,15 %',
    'rate_label'  => '2,15 % p.a.',
    'promo'       => '–',
    'availability'=> 'täglich',
    'minimum'     => 'Keine',

    'deposit_protection' => 'DE 100k €',
    'deposit_class'      => 'tag-green',

    'badge'       => 'Deutsche Banklizenz',
    'description' => 'Tagesgeld einer deutschen Direktbank mit einfacher Struktur. Geeignet für Sparer, die deutsche Einlagensicherung und tägliche Verfügbarkeit bevorzugen.',
    'tags' => [
      ['text' => '2,15 % p.a.',          'class' => 'tag-green'],
      ['text' => 'Täglich verfügbar',   'class' => 'tag-green'],
      ['text' => 'DE-Einlagensicherung','class' => ''],
      ['text' => 'Keine Mindestanlage', 'class' => ''],
    ],

    'url'           => '#',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#',
  ],

  // ─────────────────────────────────────────────
  // 16. 1822direkt
  // ─────────────────────────────────────────────
  [
    'rank'        => 16,
    'show_top'    => false,
    'featured'    => false,

    'name'        => '1822direkt Tagesgeld',
    'table_name'  => '1822direkt Tagesgeld',
    'type'        => 'Direktbank',

    'rate'        => '2,10 %',
    'rate_num'    => '2,10 %',
    'rate_label'  => '2,10 % p.a.',
    'promo'       => 'Neukunden',
    'availability'=> 'täglich',
    'minimum'     => 'Keine',

    'deposit_protection' => 'DE 100k €',
    'deposit_class'      => 'tag-green',

    'badge'       => 'Sparkassen-Nähe',
    'description' => 'Direktbankangebot mit Bezug zur Frankfurter Sparkasse. Geeignet für Sparer, die ein deutsches Tagesgeldkonto mit Online-Abwicklung suchen.',
    'tags' => [
      ['text' => '2,10 % p.a.',          'class' => 'tag-green'],
      ['text' => 'Neukunden',           'class' => 'tag-amber'],
      ['text' => 'DE-Einlagensicherung','class' => ''],
      ['text' => 'Direktbank',          'class' => 'tag-blue'],
    ],

    'url'           => '#',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#',
  ],

  // ─────────────────────────────────────────────
  // 17. Barclays
  // ─────────────────────────────────────────────
  [
    'rank'        => 17,
    'show_top'    => false,
    'featured'    => false,

    'name'        => 'Barclays Tagesgeld',
    'table_name'  => 'Barclays Tagesgeld',
    'type'        => 'Direktbank',

    'rate'        => '2,00 %',
    'rate_num'    => '2,00 %',
    'rate_label'  => '2,00 % p.a.',
    'promo'       => '–',
    'availability'=> 'täglich',
    'minimum'     => 'Keine',

    'deposit_protection' => 'DE 100k €',
    'deposit_class'      => 'tag-green',

    'badge'       => 'Bekannte Bankmarke',
    'description' => 'Tagesgeldangebot einer bekannten Bankmarke. Geeignet für Sparer, die ein unkompliziertes Konto ohne feste Laufzeit suchen.',
    'tags' => [
      ['text' => '2,00 % p.a.',          'class' => 'tag-green'],
      ['text' => 'Täglich verfügbar',   'class' => 'tag-green'],
      ['text' => 'Keine Mindestanlage', 'class' => ''],
      ['text' => 'DE-Einlagensicherung','class' => ''],
    ],

    'url'           => '#',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#',
  ],

  // ─────────────────────────────────────────────
  // 18. Suresse Direkt Bank
  // ─────────────────────────────────────────────
  [
    'rank'        => 18,
    'show_top'    => false,
    'featured'    => false,

    'name'        => 'Suresse Direkt Bank Tagesgeld',
    'table_name'  => 'Suresse Direkt Bank',
    'type'        => 'Direktbank',

    'rate'        => '2,40 %',
    'rate_num'    => '2,40 %',
    'rate_label'  => '2,40 % p.a.',
    'promo'       => 'Neukunden',
    'availability'=> 'täglich',
    'minimum'     => 'Keine',

    'deposit_protection' => 'ES 100k €',
    'deposit_class'      => 'tag-blue',

    'badge'       => 'Hoher Tabellenzins',
    'description' => 'Direktbankangebot mit spanischer Einlagensicherung. Besonders relevant für Sparer, die höhere Neukundenzinsen innerhalb der EU vergleichen möchten.',
    'tags' => [
      ['text' => '2,40 % p.a.',          'class' => 'tag-green'],
      ['text' => 'Neukunden',           'class' => 'tag-amber'],
      ['text' => 'ES-Einlagensicherung','class' => ''],
      ['text' => 'Keine Mindestanlage', 'class' => ''],
    ],

    'url'           => '#',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#',
  ],

  // ─────────────────────────────────────────────
  // 19. J&T Direktbank
  // ─────────────────────────────────────────────
  [
    'rank'        => 19,
    'show_top'    => false,
    'featured'    => false,

    'name'        => 'J&T Direktbank Tagesgeld',
    'table_name'  => 'J&T Direktbank',
    'type'        => 'Direktbank',

    'rate'        => '2,25 %',
    'rate_num'    => '2,25 %',
    'rate_label'  => '2,25 % p.a.',
    'promo'       => '–',
    'availability'=> 'täglich',
    'minimum'     => 'Keine',

    'deposit_protection' => 'CZ 100k €',
    'deposit_class'      => 'tag-blue',

    'badge'       => 'EU-Tagesgeld',
    'description' => 'Tagesgeldangebot mit tschechischer Einlagensicherung. Geeignet für Sparer, die ihr Tagesgeld innerhalb der EU breiter verteilen möchten.',
    'tags' => [
      ['text' => '2,25 % p.a.',          'class' => 'tag-green'],
      ['text' => 'Täglich verfügbar',   'class' => 'tag-green'],
      ['text' => 'CZ-Einlagensicherung','class' => ''],
      ['text' => 'Keine Mindestanlage', 'class' => ''],
    ],

    'url'           => '#',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#',
  ],

  // ─────────────────────────────────────────────
  // 20. Bigbank
  // ─────────────────────────────────────────────
  [
    'rank'        => 20,
    'show_top'    => false,
    'featured'    => false,

    'name'        => 'Bigbank Tagesgeld',
    'table_name'  => 'Bigbank Tagesgeld',
    'type'        => 'Direktbank',

    'rate'        => '2,30 %',
    'rate_num'    => '2,30 %',
    'rate_label'  => '2,30 % p.a.',
    'promo'       => '–',
    'availability'=> 'täglich',
    'minimum'     => 'Keine',

    'deposit_protection' => 'EE 100k €',
    'deposit_class'      => 'tag-blue',

    'badge'       => 'Baltische Direktbank',
    'description' => 'Tagesgeldangebot einer estnischen Direktbank. Interessant für Sparer, die ein EU-Tagesgeldkonto mit täglicher Verfügbarkeit suchen.',
    'tags' => [
      ['text' => '2,30 % p.a.',          'class' => 'tag-green'],
      ['text' => 'Täglich verfügbar',   'class' => 'tag-green'],
      ['text' => 'EE-Einlagensicherung','class' => ''],
      ['text' => 'Keine Mindestanlage', 'class' => ''],
    ],

    'url'           => '#',
    'button'        => 'Jetzt eröffnen →',
    'table_button'  => 'Öffnen',
    'detail_anchor' => '#',
  ],

];