<?php

use CRM_Kincoop_ExtensionUtil as E;

// Only register when CiviRules is installed, so the entity is cleaned up
// (and not re-created) if CiviRules is ever removed.
if (empty(\Civi\Api4\Extension::get(FALSE)
  ->addWhere('file', '=', 'civirules')
  ->addWhere('status:name', '=', 'installed')
  ->execute()
  ->first())) {
  return [];
}

return [
  [
    'name' => 'CiviRulesCondition_kincoop_membership_valid_until_passed',
    'entity' => 'CiviRulesCondition',
    'cleanup' => 'always',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'kincoop_membership_valid_until_passed',
        'label' => E::ts('Membership valid-until date has passed'),
        'class_name' => 'CRM_Kincoop_CivirulesCondition_MembershipValidUntilPassed',
        'is_active' => TRUE,
      ],
    ],
  ],
];
