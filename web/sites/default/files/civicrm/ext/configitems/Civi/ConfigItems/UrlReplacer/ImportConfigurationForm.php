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

class ImportConfigurationForm Implements ConfigurationForm, Tab {

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

    $form->assign('askForResourceUrlReplacement', false);
    if (Decorator::askForResourceUrlReplacementOnImport($config_item_set)) {
      $form->add('text', 'resource_url_replacement_cms_root_url', E::ts('Replace [cms.root] URLs with'), ['class' => 'huge40'], true);
      $form->add('text', 'resource_url_replacement_civicrm_files_url', E::ts('Replace [civicrm.files] URLs with'), ['class' => 'huge40'], true);
      $form->addRule('resource_url_replacement_cms_root_url', E::ts('Please provide a valid url'), 'url');
      $form->addRule('resource_url_replacement_civicrm_files_url', E::ts('Please provide a valid url'), 'url');
      if (isset($configuration['resource_url_replacement_cms_root_url'])) {
        $defaults['resource_url_replacement_cms_root_url'] = $configuration['resource_url_replacement_cms_root_url'];
      }
      if (isset($configuration['resource_url_replacement_civicrm_files_url'])) {
        $defaults['resource_url_replacement_civicrm_files_url'] = $configuration['resource_url_replacement_civicrm_files_url'];
      }
      $form->assign('askForResourceUrlReplacement', true);
    }

    $urlTemplates = [];
    $urls = $this->decorator->getUrlsForImport($config_item_set);
    $urlForConfiguration = [];
    $exportConfiguration = [];
    if (isset($config_item_set['configuration']) && isset($config_item_set['configuration'][$this->decorator->getName()])) {
      $exportConfiguration = $config_item_set['configuration'][$this->decorator->getName()];
    }
    foreach ($urls as $urlKey => $url) {
      if ($url instanceof ConfigurableUrl || $url instanceof ConfigurableImage) {
        $urlConfiguration = [];
        if (isset($exportConfiguration['urls']) && isset($exportConfiguration['urls'][$urlKey])) {
          $urlConfiguration = $exportConfiguration['urls'][$urlKey];
        }
        $urlImportForm = $url->getImportConfigurationForm();
        $urlImportForm->buildConfigurationForm($form, $urlConfiguration, $config_item_set);
        if ($urlImportForm->getConfigurationTemplateFileName()) {
          $urlTemplates[$urlKey] = $urlImportForm->getConfigurationTemplateFileName();
          $urlForConfiguration[$urlKey] = $url;
        }
      }
    }
    $form->assign('urls', $urlForConfiguration);
    $form->assign('url_templates', $urlTemplates);
    $form->setDefaults($defaults);
  }

  /**
   * Returns the name of the template for the configuration form.
   *
   * @return string
   */
  public function getConfigurationTemplateFileName() {
    return "CRM/ConfigItems/UrlReplacer/ImportForm.tpl";
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
    if (Decorator::askForResourceUrlReplacementOnImport($config_item_set)) {
      $config['resource_url_replacement_cms_root_url'] = $submittedValues['resource_url_replacement_cms_root_url'];
      $config['resource_url_replacement_civicrm_files_url'] = $submittedValues['resource_url_replacement_civicrm_files_url'];
    }
    $urls = $this->decorator->getUrlsForImport($config_item_set);
    foreach ($urls as $urlKey => $url) {
      if ($url instanceof ConfigurableUrl || $url instanceof ConfigurableImage) {
        $config['urls'][$urlKey] = $url->getImportConfigurationForm()->processConfiguration($submittedValues, $config_item_set);
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
    $decoratorName = $this->decorator->getName();
    $urls = $this->decorator->getUrlsForImport($config_item_set, $reset);
    $count = 0;
    if (Decorator::askForResourceUrlReplacementOnImport($config_item_set)) {
      $count = 2; // There are two fields required to fill.
    }
    foreach($urls as $urlKey => $url) {
      $urlConfiguration = [];
      if (isset($configuration['urls']) && isset($configuration['urls'][$urlKey])) {
        $urlConfiguration = $configuration['urls'][$urlKey];
      }
      if ($url instanceof ConfigurableUrl && $url->hasAdditionalConfigurationForImport($urlConfiguration, $config_item_set)) {
        $count ++;
        break;
      } elseif ($url instanceof ConfigurableImage && $url->hasAdditionalConfigurationForImport($urlConfiguration, $config_item_set)) {
        $count ++;
        break;
      }
    }
    if ($count) {
      $url = \CRM_Utils_System::url('civicrm/admin/civiconfig/import/decorator', [
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
        'count' => $count,
      ];
    }
    return $tabset;
  }


}
