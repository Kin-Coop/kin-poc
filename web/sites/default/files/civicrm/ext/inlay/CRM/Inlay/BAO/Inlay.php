<?php
use CRM_Inlay_ExtensionUtil as E;

class CRM_Inlay_BAO_Inlay extends CRM_Inlay_DAO_Inlay {

  /**
   * Create a new Inlay based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Inlay_DAO_Inlay|NULL
   *
  public static function create($params) {
    $className = 'CRM_Inlay_DAO_Inlay';
    $entityName = 'Inlay';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

}
