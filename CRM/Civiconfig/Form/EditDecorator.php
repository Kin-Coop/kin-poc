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

class CRM_Civiconfig_Form_EditDecorator extends CRM_Civiconfig_Form_Edit {

  /**
   * @var Civi\ConfigItems\Entity\Decorator;
   */
  protected $decorator;

  /**
   * @var string
   */
  protected $decoratorName;

  public function preProcess() {
    $this->decoratorName = CRM_Utils_Request::retrieve('decorator', 'String', $this, TRUE);
    $factory = civiconfig_get_entity_factory();
    $this->decorator = $factory->getDecoratorByName($this->decoratorName);
    $this->assign('decoratorName', $this->decoratorName);
    parent::preProcess();
  }

  public function buildQuickForm() {
    $this->add('hidden', 'id');
    if ($this->isTabContent()) {
      $this->assign('configuration_template', $this->decorator->getExportConfigurationForm()
        ->getConfigurationTemplateFileName());
      $configuration = [];
      if (isset($this->configItemSet['configuration']) && isset($this->configItemSet['configuration'][$this->decoratorName])) {
        $configuration = $this->configItemSet['configuration'][$this->decoratorName];
      }
      $this->decorator->getExportConfigurationForm()
        ->buildConfigurationForm($this, $configuration, $this->configItemSet);

      CRM_Utils_System::setTitle(E::ts('Edit configuration set: %1', [1 => $this->configItemSet['title']]));
      $nextLabel = E::ts('Save & Export');
      $nextTab = $this->getNextTab();
      if ($nextTab) {
        $nextLabel = E::ts('Save & Next');
      }
      $this->addButtons([
        ['type' => 'next', 'name' => $nextLabel, 'isDefault' => TRUE,],
        ['type' => 'cancel', 'name' => E::ts('Cancel')]
      ]);
    }
  }

  public function postProcess() {
    $redirectUrl = CRM_Utils_System::url('civicrm/admin/civiconfig/export', array('reset' => 1, 'action' => 'preview', 'id' => $this->configItemSet['id']));
    $submittedValues = $this->getSubmitValues();
    $this->configItemSet['configuration'][$this->decoratorName] = $this->decorator->getExportConfigurationForm()->processConfiguration($submittedValues, $this->configItemSet);
    $values['configuration'] = $this->configItemSet['configuration'];
    civicrm_api4('ConfigItemSet', 'update', [
      'values' => $values,
      'where' => [['id', '=', $this->id]],
    ]);
    $this->loadConfigItemSet();
    $nextTab = $this->getNextTab();
    if ($nextTab) {
      $redirectUrl = $nextTab['link'];
    }

    CRM_Utils_System::redirect($redirectUrl);
  }

  /**
   * @return string
   */
  protected function getCurrentTabName() {
    return $this->decoratorName;
  }

}
