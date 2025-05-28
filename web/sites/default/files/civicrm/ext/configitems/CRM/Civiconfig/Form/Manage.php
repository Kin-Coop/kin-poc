<?php

use CRM_Civiconfig_ExtensionUtil as E;

/**
 * Form controller class
 */
class CRM_Civiconfig_Form_Manage extends CRM_Core_Form {

  public function preProcess() {
    parent::preProcess();
    $this->setTitle(E::ts('Manage Config Items'));

    $formValues = $this->getSubmitValues();
    $configItemSets = \Civi\Api4\ConfigItemSet::get();
    if (isset($formValues['title']) && !empty($formValues['title'])) {
      $configItemSets->addWhere('title', 'LIKE', '%' . $formValues['title'] . '%');
    }
    $configItemSets->setLimit(0);
    $configItemSets->addOrderBy('title', 'ASC');
    $configItemSets = $configItemSets->execute();
    $this->assign('config_item_sets', $configItemSets->getArrayCopy());

    $session = CRM_Core_Session::singleton();
    $qfKey = CRM_Utils_Request::retrieve('qfKey', 'String', $this);
    $urlPath = CRM_Utils_System::currentPath();
    $urlParams = 'force=1';
    if ($qfKey) {
      $urlParams .= "&qfKey=$qfKey";
    }
    $session->replaceUserContext(CRM_Utils_System::url($urlPath, $urlParams));
  }

  public function buildQuickForm() {
    \CRM_Civiconfig_Settings::checkRequirements(TRUE);
    parent::buildQuickForm();

    $this->add('text', 'title', E::ts('Title contains'), array('class' => 'huge'));
    $this->addButtons(array(
      array(
        'type' => 'refresh',
        'name' => E::ts('Search'),
        'isDefault' => TRUE,
      ),
    ));
  }

  public function postProcess() {

  }

}
