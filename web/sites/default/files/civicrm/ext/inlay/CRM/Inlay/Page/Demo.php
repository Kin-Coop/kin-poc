<?php

use CRM_Inlay_ExtensionUtil as E;

class CRM_Inlay_Page_Demo extends CRM_Core_Page {

  public function run() {

    $id = (int) ($_GET['id'] ?? NULL);
    if (!$id) {
      CRM_Core_Session::setStatus("Invalid URL", "Inlay Demo", 'error');
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/a', [], FALSE, '/inlays'));
      // exit
    }

    // Load inlay.
    try {
      /** @var \Civi\Inlay\Type */
      $inlay = \Civi\Inlay\Type::fromId($id);
    }
    catch (\Exception $e) {
      CRM_Core_Session::setStatus("Invalid Inlay ID", "Inlay Demo", 'error');
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/a', [], FALSE, '/inlays'));
    }

    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(E::ts('Demo: %1', [1 => $inlay->getName()]));
    $editUrl = str_replace('{id}', $inlay->getID(), $inlay->getInstanceEditURLTemplate());
    $this->assign('editUrl', CRM_Utils_System::url($editUrl));
    $cacheBuster = time();
    $this->assign('scriptTag', "<script defer src=\"{$inlay->getBundleUrl()}?nocache=$cacheBuster\" data-inlay-id=\"{$inlay->getPublicID()}\" ></script>");

    parent::run();
  }

}
