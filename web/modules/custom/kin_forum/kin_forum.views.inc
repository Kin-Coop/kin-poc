<?php

/**
 * @file
 * Views data for kin_forum_notify module.
 */

/**
 * Implements hook_views_data().
 */
function kin_forum_views_data() {
  $data = [];

  // Define a custom "table" for global fields
  $data['kin_forum_global']['table']['group'] = t('Custom Global');
  $data['kin_forum_global']['table']['join'] = [
    // This is a global table, so it can be joined to any other table.
    // Since it doesn't actually represent a database table, we use a
    // fake table name and no join conditions.
    '#global' => [],
  ];

  // Current User ID field
  $data['kin_forum_global']['current_user_id'] = [
    'title' => t('Current User ID'),
    'help' => t('The user ID of the currently logged-in user.'),
    'field' => [
      'id' => 'current_user_id',
    ],
  ];

  return $data;
}
