<?php
// Module for administering map displays in SearchKit
return [
  'js' => [
    'ang/searchAdminDisplayMapLeaflet.js',
    'ang/searchAdminDisplayMapLeaflet/*.js',
  ],
  'css' => ['css/leaflet.css'],
  'partials' => [
    'ang/searchAdminDisplayMapLeaflet',
  ],
  'requires' => [
    'crmSearchAdmin',
    'crmSearchDisplayMapLeaflet',
  ],
  'basePages' => ['civicrm/admin/search'],
  'bundles' => ['bootstrap3', 'searchkitmaps'],
];
