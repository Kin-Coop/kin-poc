<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'Cron:Job.Membershipimplicit',
    'entity' => 'Job',
    'params' => 
    array (
      'version' => 3,
      'name' => 'Process unallocated recurring membership payments',
      'description' => 'Update Memberships from unallocated recurring contributions by financial type',
      'is_active' => 0,
      'run_frequency' => 'Always',
      'api_entity' => 'Job',
      'api_action' => 'Membershipimplicit',
      'parameters' => 'mapping=0:0
dateLimit=-1 year',
    ),
  ),
);
