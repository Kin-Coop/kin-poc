<?php
use CRM_Searchkitmaps_ExtensionUtil as E;

return [
  [
    'name' => 'SavedSearch_Skmaps_Address_Search_Demo',
    'entity' => 'SavedSearch',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'Skmaps_Address_Search_Demo',
        'label' => E::ts('SearchkitMaps address search with filtering and clickable table'),
        'api_entity' => 'Address',
        'api_params' => [
          'version' => 4,
          'select' => [
            'id',
            'street_address',
            'city',
            'Address_Contact_contact_id_01.sort_name',
            'geo_code_1',
            'geo_code_2',
            'contact_id',
            'Address_Contact_contact_id_01.contact_type:label',
          ],
          'orderBy' => [],
          'where' => [],
          'groupBy' => [],
          'join' => [
            [
              'Contact AS Address_Contact_contact_id_01',
              'INNER',
              [
                'contact_id',
                '=',
                'Address_Contact_contact_id_01.id',
              ],
            ],
          ],
          'having' => [],
        ],
      ],
      'match' => ['name'],
    ],
  ],
  [
    'name' => 'SavedSearch_Skmaps_Address_Search_Demo_SearchDisplay_Skmaps_Address_Search_Demo_Map_1',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'Skmaps_Address_Search_Demo_Map_1',
        'label' => E::ts('Map'),
        'saved_search_id.name' => 'Skmaps_Address_Search_Demo',
        'type' => 'map-leaflet',
        'settings' => [
          'format' => [
            'height' => 480,
            'max_width' => 768,
          ],
          'markers' => [
            'custom' => TRUE,
            'shape' => 'circle',
          ],
          'columns' => [
            [
              'type' => 'field',
              'key' => 'geo_code_1',
              'label' => E::ts('Latitude'),
              'mapTo' => 'lat',
            ],
            [
              'type' => 'field',
              'key' => 'geo_code_2',
              'label' => E::ts('Longitude'),
              'mapTo' => 'lon',
            ],
            [
              'type' => 'field',
              'key' => 'id',
              'label' => E::ts('Address ID'),
              'mapTo' => 'popupHtml',
              'rewrite' => '<strong>[Address_Contact_contact_id_01.sort_name]</strong><hr/>[Address_Contact_contact_id_01.contact_type:label]',
              'icons' => [
                [
                  'field' => 'Address_Contact_contact_id_01.contact_type:icon',
                  'side' => 'left',
                ],
              ],
            ],
            [
              'type' => 'field',
              'key' => 'street_address',
              'label' => E::ts('Street Address'),
              'mapTo' => 'customMarkerColor',
              'rewrite' => '{if \'[Address_Contact_contact_id_01.contact_type:label]\' eq \'Organization\'}green{else}red{/if}',
            ],
            [
              'type' => 'field',
              'key' => 'city',
              'label' => E::ts('City'),
              'mapTo' => 'customMarkerIcon',
              'rewrite' => 'fa-coffee',
            ],
          ],
        ],
      ],
      'match' => [
        'saved_search_id',
        'name',
      ],
    ],
  ],
  [
    'name' => 'SavedSearch_Skmaps_Address_Search_Demo_SearchDisplay_Skmaps_Address_Search_Demo_Table_1',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'Skmaps_Address_Search_Demo_Table_1',
        'label' => E::ts('Table'),
        'saved_search_id.name' => 'Skmaps_Address_Search_Demo',
        'type' => 'table',
        'settings' => [
          'description' => NULL,
          'sort' => [],
          'limit' => 50,
          'pager' => [],
          'placeholder' => 5,
          'columns' => NULL,
          'actions' => TRUE,
          'classes' => ['table', 'table-striped'],
          'columnMode' => 'auto',
          'actions_display_mode' => 'menu',
          'headerCount' => TRUE,
        ],
      ],
      'match' => [
        'saved_search_id',
        'name',
      ],
    ],
  ],
];
