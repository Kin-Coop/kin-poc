<?php

function _civicrm_api3_kinpayments_payment_get_spec(&$spec) {
  $spec['id'] = [
    'title' => 'Payment ID',
    'type' => CRM_Utils_Type::T_INT,
  ];
}
