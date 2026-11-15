<?php
use CRM_Searchkitmaps_ExtensionUtil as E;

return [
  [
    'name' => 'SearchDisplayType_skmap_leaflet',
    'entity' => 'OptionValue',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'search_display_type',
        'value' => 'map-leaflet',
        'name' => 'crm-search-display-map-leaflet',
        'label' => E::ts('Map'),
        'description' => E::ts('Displays results as markers on a map.'),
        'icon' => 'fa-map',
      ],
      'match' => ['option_group_id', 'name'],
    ],
  ],
];
