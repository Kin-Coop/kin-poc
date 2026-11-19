<?php
// Module for rendering map displays in SearchKit
return [
  'js' => [
    'ang/crmSearchDisplayMapLeaflet.js',
    'ang/crmSearchDisplayMapLeaflet/*.js',
    'ang/crmSearchKitMaps.js',
  ],
  'css' => ['css/searchkitmaps.css'],
  'partials' => [
    'ang/crmSearchDisplayMapLeaflet',
  ],
  'requires' => [
    'crmSearchDisplay',
    'crmUi',
    'ui.bootstrap',
  ],
  'basePages' => ['civicrm/search'],
  'bundles' => ['bootstrap3', 'searchkitmaps'],
  'exports' => [
    'crm-search-display-map-leaflet' => 'E',
  ],
  'settingsFactory' => ['\Civi\Searchkitmaps\Utils', 'getSettings'],
];
