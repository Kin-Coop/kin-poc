<?php
use CRM_Civiconfig_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Civiconfig_Upgrader extends CRM_Extension_Upgrader_Base {

  public function upgrade_1001() {
    $this->executeSqlFile('sql/auto_install.sql');
    return TRUE;
  }
}
