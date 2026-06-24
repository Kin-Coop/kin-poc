<?php

function _civicrm_api3_kinpayments_payment_create_spec(&$spec) {

  $spec['contact_id'] = [
    'title' => 'Contact',
    'api.required' => 1,
  ];

  $spec['payment_status_id'] = [
    'title' => 'Status',
  ];
}
