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

namespace Civi\ConfigItems\Entity\CustomGroup;

use Civi\ConfigItems\ConfigurationForm;
use Civi\ConfigItems\ConfigurationFormCountable;
use Civi\ConfigItems\Tab;
use Civi\ConfigItems\FileFormat\EntityImportDataException;
use CRM_Civiconfig_ExtensionUtil as E;

class ExportForm implements ConfigurationForm, ConfigurationFormCountable, Tab {

  /**
   * @var \Civi\ConfigItems\Entity\CustomGroup\Exporter
   */
  protected $exporter;

  protected $customGroups;

  public function __construct(Exporter $exporter) {
    $this->exporter = $exporter;
  }

  /**
   * @return string
   */
  public function getTitle() {
    return $this->exporter->getEntityDefinition()->getTitlePlural();
  }

  public function getHelpText() {
    return $this->exporter->getHelpText();
  }

  /**
   * @param \CRM_Core_Form $form
   * @param array $configuration
   * @param array $config_item_set
   */
  public function buildConfigurationForm(\CRM_Core_Form $form, $configuration, $config_item_set) {
    $entityName = $this->exporter->getEntityDefinition()->getName();
    $form->assign('entityTitle', $this->getTitle());
    $form->assign('helpText', $this->getHelpText());
    $form->assign('id', $config_item_set['id']);
    $form->assign('entityName', $entityName);
    $form->assign('configuration', $configuration);

    $customFields = [];
    if (isset($configuration['include'])) {
      foreach($configuration['include'] as $custom_group_name => $item) {
        $customFields[$custom_group_name] = $this->getCustomFieldsForCustomGroup($custom_group_name);
      }
    }
    if (isset($configuration['remove'])) {
      foreach($configuration['remove'] as $custom_group_name => $item) {
        $customFields[$custom_group_name] = $this->getCustomFieldsForCustomGroup($custom_group_name);
      }
    }
    $form->assign('customfields_by_group', $customFields);
    $form->assign('customgroups', $this->getCustomGroups());

    $defaults = [];
    $form->setDefaults($defaults);
  }


  /**
   * Returns the name of the template for the configuration form.
   *
   * @return string
   */
  public function getConfigurationTemplateFileName() {
    return "CRM/ConfigItems/Entity/CustomGroup/ExportForm.tpl";
  }

  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @param array $config_item_set
   * @return array
   */
  public function processConfiguration($submittedValues, $config_item_set) {
    $entityName = $this->exporter->getEntityDefinition()->getName();
    $config = [];
    if (isset($config_item_set['configuration'][$entityName])) {
      $config = $config_item_set['configuration'][$entityName];
    }
    return $config;
  }

  /**
   * This function is called to add tabs to the tabset.
   * Returns the $tabset
   *
   * @param $tabset
   * @param $configuration
   * @param $config_item_set
   * @param bool $reset
   * @return array
   */
  public function getTabs($tabset, $configuration, $config_item_set, $reset=FALSE) {
    $entityName = $this->exporter->getEntityDefinition()->getName();
    $url = \CRM_Utils_System::url('civicrm/admin/civiconfig/edit/entity', ['reset' => 1, 'id' => $config_item_set['id'], 'entity' => $entityName]);
    $tabset[$entityName] = [
      'title' => $this->exporter->getEntityDefinition()->getTitlePlural(),
      'active' => 1,
      'valid' => 1,
      'link' => $url,
      'current' => false,
      'count' => $this->getCount($configuration),
    ];
    return $tabset;
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $configuration
   *
   * @return int
   */
  public function getCount($configuration) {
    $count = 0;
    if (isset($configuration['include'])) {
      $count += count($configuration['include']);
    }
    if (isset($configuration['remove'])) {
      $count += count($configuration['remove']);
    }
    return $count;
  }

  /**
   * @return array
   * @throws \API_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  protected function getCustomGroups() {
    if (!$this->customGroups) {
      foreach (\Civi\Api4\CustomGroup::get()->addOrderBy('title', 'ASC')->execute() as $customGroup) {
        $this->customGroups[$customGroup['name']] = $customGroup;
      }
    }
    return $this->customGroups;
  }

  protected function getCustomFieldsForCustomGroup($custom_group_name) {
    $return = [];
    $customFields = \Civi\Api4\CustomField::get()
      ->addWhere('custom_group_id:name', '=', $custom_group_name)
      ->addOrderBy('weight', 'ASC')
      ->execute();
    foreach ($customFields as $customField) {
      $return[$customField['name']] = $customField;
    }
    return $return;
  }

}
