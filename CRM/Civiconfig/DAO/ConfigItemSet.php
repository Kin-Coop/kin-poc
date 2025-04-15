<?php

/**
 * DAOs provide an OOP-style facade for reading and writing database records.
 *
 * DAOs are a primary source for metadata in older versions of CiviCRM (<5.74)
 * and are required for some subsystems (such as APIv3).
 *
 * This stub provides compatibility. It is not intended to be modified in a
 * substantive way. Property annotations may be added, but are not required.
 * @property string $id 
 * @property string $name 
 * @property string $title 
 * @property string $description 
 * @property string $version 
 * @property string $version_hash 
 * @property string $entities 
 * @property string $configuration 
 * @property string $import_file_format 
 * @property string $import_sub_directory 
 * @property string $import_configuration 
 */
class CRM_Civiconfig_DAO_ConfigItemSet extends CRM_Civiconfig_DAO_Base {

  /**
   * Required by older versions of CiviCRM (<5.74).
   * @var string
   */
  public static $_tableName = 'civicrm_config_item_set';

}
