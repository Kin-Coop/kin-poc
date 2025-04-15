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

namespace Civi\ConfigItems\UrlReplacer\ExternalLink;

use Civi\ConfigItems\ConfigurationForm;
use Civi\ConfigItems\UrlReplacer\ExternalLink;
use CRM_Civiconfig_ExtensionUtil as E;

class ImportConfigurationForm implements ConfigurationForm {

  /**
   * @var \Civi\ConfigItems\UrlReplacer\ExternalLink
   */
  protected $url;

  protected $hasElements = false;

  public function __construct(ExternalLink $url) {
    $this->url = $url;
  }

  /**
   * @return string
   */
  public function getTitle() {
    return $this->url->getLabel();
  }


  /**
   * Add additional elements to the form.
   * Such as a text field for the replacement url
   * @param \CRM_Core_Form $form
   * @param array $configuration
   * @param array $config_item_set
   * @throws \CRM_Core_Exception
   */
  public function buildConfigurationForm(\CRM_Core_Form $form, $configuration, $config_item_set) {
    $name = $this->url->getUniqueKey();
    if ($configuration['method'] == 'ask_on_import') {
      $form->add('text', 'replace_url_' . $name, E::ts('Replace with URL'), [
        'class' => 'huge40',
        'autocomplete' => 'url'
      ], TRUE);
      $form->addRule('replace_url_' . $name, E::ts('Please provide a valid url'), 'url');
      $defaults = [];
      if (isset($configuration['replace_url'])) {
        $defaults['replace_url_' . $name] = $configuration['replace_url'];
      }
      $form->setDefaults($defaults);
      $this->hasElements = true;
    }
  }

  public function processConfiguration($submittedValues, $config_item_set) {
    $config = [];
    $name = $this->url->getUniqueKey();
    if (isset($submittedValues['replace_url_' . $name])) {
      $config['replace_url'] = $submittedValues['replace_url_' . $name];
    }
    return $config;
  }

  /**
   * @return string
   */
  public function getConfigurationTemplateFileName() {
    if ($this->hasElements) {
      return "CRM/ConfigItems/UrlReplacer/ExternalLink/ImportForm.tpl";
    }
    return FALSE;
  }

}
