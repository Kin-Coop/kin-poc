<?php
use CRM_Kinrc_ExtensionUtil as E;

/**
 * Conrc.Duplicateconrc API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_conrc_Duplicateconrc_spec(&$spec) {
  //$spec['magicword']['api.required'] = 1;
}

/**
 * Conrc.Duplicateconrc API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws CRM_Core_Exception
 */
function civicrm_api3_conrc_Duplicateconrc($params) {
    $results = \Civi\Api4\Kinrc::copycontribution(TRUE)
        ->execute();
    foreach ($results as $result) {
        // do something
    }
    /*
  if (array_key_exists('magicword', $params) && $params['magicword'] == 'sesame') {
    $returnValues = array(
      // OK, return several data rows
      12 => ['id' => 12, 'name' => 'Twelve'],
      34 => ['id' => 34, 'name' => 'Thirty four'],
      56 => ['id' => 56, 'name' => 'Fifty six'],
    );
    // ALTERNATIVE: $returnValues = []; // OK, success
    // ALTERNATIVE: $returnValues = ["Some value"]; // OK, return a single value

    // Spec: civicrm_api3_create_success($values = 1, $params = [], $entity = NULL, $action = NULL)
    return civicrm_api3_create_success($returnValues, $params, 'Conrc', 'Duplicateconrc');
  }
    */
  //else {
    //throw new CRM_Core_Exception(/*error_message*/ 'Everyone knows that the magicword is "sesame"', /*error_code*/ 'magicword_incorrect');
  //}
    return civicrm_api3_create_success($results, $params, 'Conrc', 'Duplicateconrc');
}
