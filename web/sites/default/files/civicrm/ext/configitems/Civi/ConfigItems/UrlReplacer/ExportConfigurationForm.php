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

namespace Civi\ConfigItems\UrlReplacer;

use Civi\ConfigItems\ConfigurationForm;
use Civi\ConfigItems\Tab;
use CRM_Civiconfig_ExtensionUtil as E;

class ExportConfigurationForm Implements ConfigurationForm, Tab {

  /**
   * @var \Civi\ConfigItems\UrlReplacer\Decorator
   */
  protected $decorator;

  /**
   * @param \Civi\ConfigItems\UrlReplacer\Decorator $decorator
   */
  public function __construct($decorator) {
    $this->decorator = $decorator;
  }

  /**
   * @return string
   */
  public function getTitle() {
    return E::ts('Replace Links/Images');
  }

  /**
   * @param \CRM_Core_Form $form
   * @param array $configuration
   * @param array $config_item_set
   */
  public function buildConfigurationForm(\CRM_Core_Form $form, $configuration, $config_item_set) {
    $defaults = [];
    $form->assign('id', $config_item_set['id']);
    $form->assign('containsResourceURL', FALSE);
    if ($this->decorator->containsResourceURL($config_item_set)) {
      $resourceUrlReplacementOptions = [
        'replace_with_target_resource_url' => E::ts('Replace with resource URL of target system'),
        'ask_on_import' => E::ts('Ask for replacement on import'),
        'keep' => E::ts('Do not replace'),
      ];
      $form->add('select', 'resource_url_replacement', E::ts('Resource URL'), $resourceUrlReplacementOptions, true, array(
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- select -'),
      ));
      if (isset($configuration['resource_url_replacement'])) {
        $defaults['resource_url_replacement'] = $configuration['resource_url_replacement'];
      } else {
        $defaults['resource_url_replacement'] = 'replace_with_target_resource_url';
      }
      $form->assign('containsResourceURL', TRUE);
    }
    if (!isset($configuration['urls'])) {
      $configuration['urls'] = [];
    }

    $urls = $this->decorator->getUrlsForExport($config_item_set);
    $importUrls = $this->decorator->getUrlsForImport($config_item_set, FALSE, TRUE);
    $selectedUrls = [];
    $selectedImportUrls = [];
    $selectedImages = [];
    $selectedImportImages = [];
    foreach($configuration['urls'] as $urlKey => $urlConfig) {
      $url = $urls[$urlKey];
      $importUrl = $importUrls[$urlKey];
      if ($url && $url instanceof ConfigurableUrl) {
        $selectedUrls[$urlKey] = [
          'label' => $url->getLabel(),
          'config_label' => $url->getExportConfigurationLabel($urlConfig, $config_item_set),
        ];
      } elseif ($importUrl && $importUrl instanceof ConfigurableUrl) {
        $selectedImportUrls[$urlKey] = [
          'label' => $importUrl->getLabel(),
          'config_label' => $importUrl->getExportConfigurationLabel($urlConfig, $config_item_set),
        ];
      } elseif ($url && $url instanceof ConfigurableImage) {
        $selectedImages[$urlKey] = [
          'label' => $url->getLabel(),
          'config_label' => $url->getExportConfigurationLabel($urlConfig, $config_item_set),
        ];
      } elseif ($importUrl && $importUrl instanceof ConfigurableImage) {
        $selectedImportImages[$urlKey] = [
          'label' => $importUrl->getLabel(),
          'config_label' => $importUrl->getExportConfigurationLabel($urlConfig, $config_item_set),
        ];
      }
    }
    $form->assign('selected_urls', $selectedUrls);
    $form->assign('selected_import_urls', $selectedImportUrls);
    $form->assign('selected_images', $selectedImages);
    $form->assign('selected_import_images', $selectedImportImages);

    $form->setDefaults($defaults);
  }

  /**
   * Returns the name of the template for the configuration form.
   *
   * @return string
   */
  public function getConfigurationTemplateFileName() {
    return "CRM/ConfigItems/UrlReplacer/ExportForm.tpl";
  }

  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @param array $config_item_set
   *
   * @return array
   */
  public function processConfiguration($submittedValues, $config_item_set) {
    $config = [];
    if (isset($config_item_set['configuration']['UrlReplaceer'])) {
      $config = $config_item_set['configuration']['UrlReplaceer'];
    }
    unset($config['resource_url_replacement']);
    if ($this->decorator->containsResourceURL($config_item_set)) {
      $config['resource_url_replacement'] = $submittedValues['resource_url_replacement'];
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
    if ($this->decorator->isAvailable($config_item_set)) {
      $decoratorName = $this->decorator->getName();
      $url = \CRM_Utils_System::url('civicrm/admin/civiconfig/edit/decorator', [
        'reset' => 1,
        'id' => $config_item_set['id'],
        'decorator' => $decoratorName
      ]);
      $tabset[$decoratorName] = [
        'title' => $this->getTitle(),
        'active' => 1,
        'valid' => 1,
        'link' => $url,
        'current' => FALSE,
      ];
    }
    return $tabset;
  }


}
