<?php
use CRM_Civiconfig_ExtensionUtil as E;

class CRM_Civiconfig_BAO_ConfigItemSet extends CRM_Civiconfig_DAO_ConfigItemSet {

  /**
   * Calculates a hash value of this config item set.
   * Is used to determine whether we should increment a version when
   * a config item set is exported.
   *
   * @param $config_item_set
   * @return string
   */
  public static function calculateHash($config_item_set) {
    $dataForHash['name'] = $config_item_set['name'];
    $dataForHash['title'] = $config_item_set['title'];
    $dataForHash['version'] = $config_item_set['version'];
    $dataForHash['description'] = $config_item_set['description'];
    $dataForHash['configuration'] = $config_item_set['configuration'];
    return md5(json_encode($dataForHash));
  }

  /**
   * Save DAO object.
   *
   * @param bool $hook
   *
   * @return CRM_Core_DAO
   */
  public function save($hook = TRUE) {
    if (empty($this->id) && (!isset($this->name) || empty($this->name) || $this->name == 'null') && isset($this->title)) {
      $this->name = static::checkName($this->title);
    }
    return parent::save($hook);
  }

  /**
   * Create or update a record from supplied params.
   *
   * If 'id' is supplied, an existing record will be updated
   * Otherwise a new record will be created.
   *
   * @param array $record
   *
   * @return static
   * @throws \CRM_Core_Exception
   */
  public static function writeRecord(array $record): CRM_Core_DAO {
    $hook = empty($record['id']) ? 'create' : 'edit';
    $entityName = CRM_Core_DAO_AllCoreTables::getBriefName('CRM_Civiconfig_BAO_ConfigItemSet');
    \CRM_Utils_Hook::pre($hook, $entityName, $record['id'] ?? NULL, $record);
    $instance = new \CRM_Civiconfig_BAO_ConfigItemSet();
    $instance->copyValues($record);
    $instance->save();
    \CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Checks whether a name is valid.
   *
   * @param $title
   * @param null $id
   * @param null $name
   *
   * @return array|string|string[]|null
   */
  public static function checkName($title, $id=null,$name=null) {
    if (!$name) {
      $name = preg_replace('@[^a-z0-9_]+@','_',strtolower($title));
    }

    $name = preg_replace('@[^a-z0-9_]+@','_',strtolower($name));
    $name_part = $name;

    $sql = "SELECT COUNT(*) FROM `" . static::$_tableName . "` WHERE `name` = %1";
    $sqlParams[1] = array($name, 'String');
    if ($id) {
      $sql .= " AND `id` != %2";
      $sqlParams[2] = array($id, 'Integer');
    }

    $i = 1;
    while(CRM_Core_DAO::singleValueQuery($sql, $sqlParams) > 0) {
      $i++;
      $name = $name_part .'_'.$i;
      $sqlParams[1] = array($name, 'String');
    }
    return $name;
  }

  /**
   * Returns whether the name is valid or not
   *
   * @param string $name
   * @param int $id optional
   * @return bool
   * @static
   */
  public static function isNameValid($name, $id=null) {
    $sql = "SELECT COUNT(*) FROM `" . static::$_tableName . "` WHERE `name` = %1";
    $params[1] = array($name, 'String');
    if ($id) {
      $sql .= " AND `id` != %2";
      $params[2] = array($id, 'Integer');
    }
    $count = CRM_Core_DAO::singleValueQuery($sql, $params);
    return ($count > 0) ? false : true;
  }




}
