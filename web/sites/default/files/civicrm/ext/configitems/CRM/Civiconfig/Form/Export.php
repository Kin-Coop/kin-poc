<?php
/**
 * Copyright (C) 2021  Jaap Jansma (jaap.jansma@civicoop.org)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

use CRM_Civiconfig_ExtensionUtil as E;

/**
 * Form controller class
 */
class CRM_Civiconfig_Form_Export extends CRM_Core_Form {

  /**
   * @var int
   */
  protected $id;

  protected $configItemSet;

  public function preProcess() {
    parent::preProcess();
    $this->id = CRM_Utils_Request::retrieve('id', 'Integer', $this, TRUE);
    $this->configItemSet = Civi\Api4\ConfigItemSet::get()
      ->addWhere('id', '=', $this->id)
      ->setLimit(1)
      ->execute()
      ->first();
    $this->assign('config_item_set', $this->configItemSet);
    $this->setTitle(E::ts('Export Configuration Set: %1', [1=>$this->configItemSet['title']]));
    if ($this->_action & CRM_Core_Action::EXPORT) {
      $factory = civiconfig_get_fileformat_factory();
      $increment_version = CRM_Utils_Request::retrieve('increment_version', 'Integer') ? true : false;
      $file_format = CRM_Utils_Request::retrieve('file_format', 'String', $this, true);
      if ($increment_version) {
        $this->configItemSet['version'] ++;
      }
      $values['version_hash'] = \CRM_Civiconfig_BAO_ConfigItemSet::calculateHash($this->configItemSet);
      $values['version'] = $this->configItemSet['version'];
      civicrm_api4('ConfigItemSet', 'update', [
        'values' => $values,
        'where' => [['id', '=', $this->id]],
      ]);

      $this->configItemSet = Civi\Api4\ConfigItemSet::get()
        ->addWhere('id', '=', $this->id)
        ->setLimit(1)
        ->execute()
        ->first();

      $exporterClass = $factory->getFileFormatClass($file_format);
      $exporterClass->download($this->configItemSet);
    }
  }

  public function buildQuickForm() {
    parent::buildQuickForm();
    $factory = civiconfig_get_fileformat_factory();

    $this->add('select', 'file_format', E::ts('Select File Format'), $factory->getTypes(), false, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));
    $this->addYesNo('increment_version', E::ts('Increment version?'), true, true);
    $this->addButtons(array(array('type' => 'cancel', 'name' => E::ts('Cancel'))));
  }

  function setDefaultValues() {
    $defaults = [];
    if (empty($this->configItemSet['version_hash'])) {
      $defaults['increment_version'] = '0';
    } elseif ($this->configItemSet['version_hash'] != \CRM_Civiconfig_BAO_ConfigItemSet::calculateHash($this->configItemSet)) {
      $defaults['increment_version'] = '1';
    } else {
      $defaults['increment_version'] = '0';
    }

    $factory = civiconfig_get_fileformat_factory();
    $types = $factory->getTypes();
    if (count($types) == 1) {
      $defaults['file_format'] = array_key_first($types);
    }

    return $defaults;
  }

  public function postProcess() {

  }

}
