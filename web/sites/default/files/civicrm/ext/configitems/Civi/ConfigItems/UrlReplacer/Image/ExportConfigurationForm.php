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

namespace Civi\ConfigItems\UrlReplacer\Image;

use Civi\ConfigItems\ConfigurationForm;
use Civi\ConfigItems\UrlReplacer\Image;
use CRM_Civiconfig_ExtensionUtil as E;

class ExportConfigurationForm implements ConfigurationForm {

  /**
   * @var \Civi\ConfigItems\UrlReplacer\Image
   */
  protected $url;

  public function __construct(Image $url) {
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
    $defaults = [];
    $name = $this->url->getUniqueKey();
    $replacementOptions = $this->url->getReplacementOptions();
    $form->add('select', 'replace_method_' . $name, E::ts('How to replace?'), $replacementOptions, FALSE, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));
    if (isset($configuration['method'])) {
      $defaults['replace_method_'.$name] = $configuration['method'];
    } else {
      $defaults['replace_method_'.$name] = array_key_first($replacementOptions);
    }

    $form->add('text', 'replace_url_' . $name, E::ts('Replace with URL'), ['class' => 'huge40', 'autocomplete' => 'url'], FALSE);
    $form->addFormRule([$this, 'validateForm']);
    if (isset($configuration['replace_url'])) {
      $defaults['replace_url_' . $name] = $configuration['replace_url'];
    }
    $form->setDefaults($defaults);
  }

  /**
   * @param $values
   * @param $files
   *
   * @return array
   */
  public function validateForm($values, $files) {
    $name = $this->url->getUniqueKey();
    $errors = [];
    if ($values['url'] == $name && empty($values['replace_method_'.$name])) {
      $errors['replace_method_'.$name] = E::ts('How to replace is required');
    } elseif ($values['url'] == $name && $values['replace_method_'.$name] == 'replace_with' && empty($values['replace_url_' . $name])) {
      $errors['replace_url_' . $name] = E::ts('Replace with URL is required');
    }
    return $errors;
  }

  public function processConfiguration($submittedValues, $config_item_set) {
    $name = $this->url->getUniqueKey();
    if (isset($submittedValues['replace_method_' . $name])) {
      $config['method'] = $submittedValues['replace_method_' . $name];
    }
    if (isset($submittedValues['replace_url_' . $name])) {
      $config['replace_url'] = $submittedValues['replace_url_' . $name];
    }
    return $config;
  }

  /**
   * @return string
   */
  public function getConfigurationTemplateFileName() {
    return "CRM/ConfigItems/UrlReplacer/Image/ExportForm.tpl";
  }

}
