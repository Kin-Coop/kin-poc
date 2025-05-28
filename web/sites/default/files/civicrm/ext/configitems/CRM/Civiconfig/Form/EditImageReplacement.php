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

class CRM_Civiconfig_Form_EditImageReplacement extends CRM_Core_Form {

  /**
   * @var Civi\ConfigItems\UrlReplacer\Decorator;
   */
  protected $decorator;

  /**
   * @var string
   */
  protected $decoratorName;

  /**
   * @var int
   */
  protected $id;

  protected $configItemSet;

  protected $urlKey;

  public function preProcess() {
    parent::preProcess();
    $this->decoratorName = CRM_Utils_Request::retrieve('decorator', 'String', $this, TRUE);
    $factory = civiconfig_get_entity_factory();
    $this->decorator = $factory->getDecoratorByName($this->decoratorName);
    $this->assign('decoratorName', $this->decoratorName);

    $this->id = CRM_Utils_Request::retrieve('id', 'Integer');
    $this->urlKey = CRM_Utils_Request::retrieve('urlKey', 'String');
    if ($this->id) {
      $this->configItemSet = Civi\Api4\ConfigItemSet::get()
        ->addWhere('id', '=', $this->id)
        ->setLimit(1)
        ->execute()
        ->first();
      $this->assign('config_item_set', $this->configItemSet);
    }
    if ($this->urlKey && $this->_action & CRM_Core_Action::DELETE) {
      $redirectUrl = \CRM_Utils_System::url('civicrm/admin/civiconfig/edit/decorator', ['reset' => 1, 'id' => $this->id, 'decorator' => $this->decoratorName]);
      unset($this->configItemSet['configuration'][$this->decoratorName]['urls'][$this->urlKey]);
      $values['configuration'] = $this->configItemSet['configuration'];
      civicrm_api4('ConfigItemSet', 'update', [
        'values' => $values,
        'where' => [['id', '=', $this->id]],
      ]);
      CRM_Utils_System::redirect($redirectUrl);
    }
  }

  public function buildQuickForm() {
    $this->add('hidden', 'id');
    $this->add('hidden', 'urlKey');
    CRM_Utils_System::setTitle(E::ts('Add Image replacement for %1', [1=>$this->configItemSet['title']]));

    $urls = $this->decorator->getUrlsForExport($this->configItemSet);
    $configuration = $this->configItemSet['configuration'][$this->decoratorName]['urls'];
    if (!is_array($configuration)) {
      $configuration = [];
    }
    $urlOptions = [];
    $urlTemplates = [];
    $images = [];
    foreach($urls as $urlKey => $url) {
      if ($url instanceof \Civi\ConfigItems\UrlReplacer\ConfigurableImage) {
        if (($this->urlKey && $this->urlKey == $urlKey) || !isset($configuration[$urlKey])) {
          $urlOptions[$urlKey] = $url->getLabel();
          $images[$urlKey] = $url->getImageUrl();
          $urlConfiguration = [];
          if (isset($configuration[$urlKey])) {
            $urlConfiguration = $configuration[$urlKey];
          }
          $url->getExportConfigurationForm()
            ->buildConfigurationForm($this, $urlConfiguration, $this->configItemSet);
          if ($url->getExportConfigurationForm()
            ->getConfigurationTemplateFileName()) {
            $urlTemplates[$urlKey] = $url->getExportConfigurationForm()
              ->getConfigurationTemplateFileName();
          }
        }
      }
    }

    $this->add('select', 'url', E::ts('Image'), $urlOptions, true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge40',
      'placeholder' => E::ts('- select -'),
    ));
    $this->assign('url_templates', $urlTemplates);
    $this->assign('url_images', $images);

    $this->addButtons(array(
      array('type' => 'next', 'name' => E::ts('Save & Next'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => E::ts('Cancel'))));

    $redirectUrl = \CRM_Utils_System::url('civicrm/admin/civiconfig/edit/decorator', ['reset' => 1, 'id' => $this->id, 'decorator' => $this->decoratorName]);
    CRM_Utils_System::appendBreadCrumb([['title' => E::ts('Edit Configuration Set'), 'url' => $redirectUrl]]);
  }

  /**
   * Function to set default values (overrides parent function)
   *
   * @return array $defaults
   * @access public
   */
  function setDefaultValues() {
    $defaults = array();
    $defaults['id'] = $this->id;
    if ($this->urlKey) {
      $defaults['urlKey'] = $this->urlKey;
      $defaults['url'] = $this->urlKey;
    }
    return $defaults;
  }

  public function postProcess() {
    $redirectUrl = \CRM_Utils_System::url('civicrm/admin/civiconfig/edit/decorator', ['reset' => 1, 'id' => $this->id, 'decorator' => $this->decoratorName]);
    $submittedValues = $this->getSubmitValues();
    $urlKey = $submittedValues['url'];
    $urls = $this->decorator->getUrlsForExport($this->configItemSet);
    if (isset($urls[$urlKey])) {
      $url = $urls[$urlKey];
      if ($url instanceof \Civi\ConfigItems\UrlReplacer\ConfigurableImage) {
        $config = $url->getExportConfigurationForm()->processConfiguration($submittedValues, $this->configItemSet);
        if ($this->urlKey) {
          unset($this->configItemSet['configuration'][$this->decoratorName]['urls'][$this->urlKey]);
        }
        $this->configItemSet['configuration'][$this->decoratorName]['urls'][$urlKey] = $config;
        $values['configuration'] = $this->configItemSet['configuration'];
        civicrm_api4('ConfigItemSet', 'update', [
          'values' => $values,
          'where' => [['id', '=', $this->id]],
        ]);
      }
    }
    CRM_Utils_System::redirect($redirectUrl);
  }

  /**
   * Function that can be defined in Form to override or.
   * perform specific action on cancel action
   */
  public function cancelAction() {
    $this->decoratorName = CRM_Utils_Request::retrieve('decorator', 'String', $this, TRUE);
    $this->id = CRM_Utils_Request::retrieve('id', 'Integer');
    $redirectUrl = \CRM_Utils_System::url('civicrm/admin/civiconfig/edit/decorator', ['reset' => 1, 'id' => $this->id, 'decorator' => $this->decoratorName]);
    CRM_Utils_System::redirect($redirectUrl);
  }

}
