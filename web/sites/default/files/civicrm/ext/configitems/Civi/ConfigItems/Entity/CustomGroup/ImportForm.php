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

class ImportForm implements ConfigurationForm, ConfigurationFormCountable, Tab {

  /**
   * @var \Civi\ConfigItems\Entity\CustomGroup\Importer
   */
  protected $importer;

  public function __construct(Importer $importer) {
    $this->importer = $importer;
  }

  /**
   * @return string
   */
  public function getTitle() {
    return $this->importer->getEntityDefinition()->getTitlePlural();
  }

  /**
   * @return string
   */
  public function getHelpText() {
    return $this->importer->getHelpText();
  }

  /**
   * @param \CRM_Core_Form $form
   * @param array $configuration
   * @param array $config_item_set
   */
  public function buildConfigurationForm(\CRM_Core_Form $form, $configuration, $config_item_set) {
    $entityName = $this->importer->getEntityDefinition()->getName();
    $entityData = $this->importer->loadEntityImportData($config_item_set);
    $exportConfig = $config_item_set['configuration'][$entityName];
    $defaults = [];
    $form->assign('entityData', $entityData);
    $form->assign('entityTitle', $this->getTitle());
    $form->assign('helpText', $this->getHelpText());
    $elements = [];
    if (!isset($exportConfig['include'])) {
      $exportConfig['include'] = [];
    }
    foreach ($exportConfig['include'] as $name => $item) {
      $elementName = \CRM_Utils_String::munge($name);
      $radioButtons = $this->getRadioButtonOptionsForCustomGroup('include', $entityData[$name]);
      if ($radioButtons && is_array($radioButtons) && count($radioButtons)) {
        $form->addRadio($elementName, $entityData[$name]['title'], $radioButtons, ['allowClear' => TRUE, 'class' => 'included_custom_group'], NULL, TRUE);
        if (isset($configuration['include'][$data['name']])) {
          $defaults[$elementName] = $configuration['include'][$name];
        }
        else {
          $defaults[$elementName] = $this->getDefaultRadioButtonOptionForCustomGroup('include', $entityData[$name]);
        }
        $elements['include'][$elementName] = ['name' => $entityData[$name]['title'], 'subelements' => []];

        foreach($entityData[$name]['fields'] as $customFieldName => $customField) {
          $subElementName = \CRM_Utils_String::munge($name) . '_' . \CRM_Utils_String::munge($customFieldName);
          $radioButtons = $this->getRadioButtonOptionsForCustomField('include', $customField);
          if ($radioButtons && is_array($radioButtons) && count($radioButtons)) {
            $form->addRadio($subElementName, $customField['label'], $radioButtons, ['allowClear' => TRUE], NULL, TRUE);
            if (isset($configuration['include_fields'][$name][$customFieldName])) {
              $defaults[$subElementName] = $configuration['include_fields'][$name][$customFieldName];
            } else {
              $defaults[$subElementName] = $this->getDefaultRadioButtonOptionForCustomField('include', $customField);
            }
            $elements['include'][$elementName]['subelements'][] = $subElementName;
          }
        }
        foreach($entityData[$name]['existing_fields'] as $customFieldName => $customField) {
          $subElementName = \CRM_Utils_String::munge($name) . '_' . \CRM_Utils_String::munge($customFieldName);
          $radioButtons = $this->getRadioButtonOptionsForCustomField('remove', $customField);
          if ($radioButtons && is_array($radioButtons) && count($radioButtons)) {
            $form->addRadio($subElementName, $customField['label'], $radioButtons, ['allowClear' => TRUE], NULL, TRUE);
            if (isset($configuration['remove_values'][$name][$customFieldName])) {
              $defaults[$subElementName] = $configuration['remove_fields'][$name][$customFieldName];
            } else {
              $defaults[$subElementName] = $this->getDefaultRadioButtonOptionForCustomField('remove', $customField);
            }
            $elements['include'][$elementName]['subelements'][] = $subElementName;
          }
        }

      }
    }

    if (!isset($exportConfig['remove'])) {
      $exportConfig['remove'] = [];
    }
    foreach ($exportConfig['remove'] as $name) {
      $elementName = \CRM_Utils_String::munge($name);
      $radioButtons = $this->getRadioButtonOptionsForCustomGroup('remove', $entityData[$name]);
      if ($radioButtons && is_array($radioButtons) && count($radioButtons)) {
        $form->addRadio($elementName, $entityData[$name]['title'], $radioButtons, ['allowClear' => TRUE], NULL, TRUE);
        if (isset($configuration['remove'][$data['name']])) {
          $defaults[$elementName] = $configuration['remove'][$name];
        }
        else {
          $defaults[$elementName] = $this->getDefaultRadioButtonOptionForCustomGroup('remove', $entityData[$name]);
        }
        $elements['remove'][$elementName] = ['name' => $entityData[$name]['title'], 'subelements' => []];
      }
    }

    $form->assign('elements', $elements);
    $form->setDefaults($defaults);
  }


  /**
   * Returns the name of the template for the configuration form.
   *
   * @return string
   */
  public function getConfigurationTemplateFileName() {
    return "CRM/ConfigItems/Entity/CustomGroup/ImportForm.tpl";
  }

  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @param array $config_item_set
   * @return array
   */
  public function processConfiguration($submittedValues, $config_item_set) {
    $entityName = $this->importer->getEntityDefinition()->getName();
    $config = [];
    $exportConfig = $config_item_set['configuration'][$entityName];
    $entityData = $this->importer->loadEntityImportData($config_item_set);
    if (isset($exportConfig['include'])) {
      foreach ($exportConfig['include'] as $name => $item) {
        $elementName = \CRM_Utils_String::munge($name);
        if (isset($submittedValues[$elementName])) {
          $config['include'][$name] = $submittedValues[$elementName];
        }
        foreach ($entityData[$name]['fields'] as $customFieldName => $customField) {
          $subElementName = \CRM_Utils_String::munge($name) . '_' . \CRM_Utils_String::munge($customFieldName);
          $config['include_fields'][$name][$customFieldName] = $submittedValues[$subElementName];
        }
        foreach ($entityData[$name]['existing_fields'] as $customFieldName => $customField) {
          $subElementName = \CRM_Utils_String::munge($name) . '_' . \CRM_Utils_String::munge($customFieldName);
          $config['remove_fields'][$name][$customFieldName] = $submittedValues[$subElementName];
        }
      }
    }
    if (isset($exportConfig['remove'])) {
      foreach ($exportConfig['remove'] as $name) {
        $elementName = \CRM_Utils_String::munge($name);
        if (isset($submittedValues[$elementName])) {
          $config['remove'][$name] = $submittedValues[$elementName];
        }
      }
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
    $entityName = $this->importer->getEntityDefinition()->getName();
    $url = \CRM_Utils_System::url('civicrm/admin/civiconfig/import/entity', ['reset' => 1, 'id' => $config_item_set['id'], 'entity' => $entityName]);
    $tabset[$entityName] = [
      'title' => $this->importer->getEntityDefinition()->getTitlePlural(),
      'active' => 1,
      'valid' => 1,
      'link' => $url,
      'current' => false,
      'count' => $this->getCount($configuration),
    ];
    return $tabset;
  }

  /**
   * Returns the number of options to be configured or which are configured.
   * The count is used to display in the tabs on the import/export
   * configuration screen.
   *
   * @param $configuration
   *
   * @return int
   */
  public function getCount($configuration) {
    return 0;
  }

  /**
   * @param $group
   * @param $data
   *
   * @return array
   */
  protected function getRadioButtonOptionsForCustomGroup($group, $data) {
    if ($group == 'include' && isset($data['id'])) {
      return [
        0 => E::ts('Do not update'),
        1 => E::ts('Update'),
      ];
    } elseif ($group == 'include' && !isset($data['id'])) {
      return [
        1 => E::ts('Add'),
        0 => E::ts('Do not add'),
      ];
    } elseif ($group == 'remove' && isset($data['id'])) {
      return  [
        0 => E::ts('Keep'),
        1 => E::ts('Remove')
      ];
    }
    return [];
  }

  /**
   * @param $group
   * @param $data
   *
   * @return string|void
   */
  protected function getDefaultRadioButtonOptionForCustomGroup($group, $data) {
    if ($group == 'include' && isset($data['id'])) {
      return '0';
    } elseif ($group == 'include' && !isset($data['id'])) {
      return '1';
    } elseif ($group == 'remove') {
      return  '0';
    }
  }

  /**
   * @param $group
   * @param $data
   *
   * @return array
   */
  protected function getRadioButtonOptionsForCustomField($group, $data) {
    if ($group == 'include' && isset($data['id'])) {
      return [
        0 => E::ts('Do not update'),
        1 => E::ts('Update'),
      ];
    } elseif ($group == 'include' && !isset($data['id'])) {
      return [
        1 => E::ts('Add'),
        0 => E::ts('Do not add'),
      ];
    } elseif ($group == 'remove' && isset($data['id'])) {
      return  [
        0 => E::ts('Keep'),
        1 => E::ts('Remove')
      ];
    }
    return [];
  }

  /**
   * @param $group
   * @param $data
   *
   * @return string|void
   */
  protected function getDefaultRadioButtonOptionForCustomField($group, $data) {
    if ($group == 'include' && isset($data['id'])) {
      return '0';
    } elseif ($group == 'include' && !isset($data['id'])) {
      return '1';
    } elseif ($group == 'remove') {
      return  '0';
    }
  }

}
