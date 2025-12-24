<?php

//use CivirulesActions\CRM_Civirules_Action;
//use CRM_Civirules_TriggerData_TriggerData;

class CRM_CivirulesActions_Contribution_SendEmail extends CRM_Civirules_Action
{

  /**
   * @inheritDoc
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData)
  {
    // TODO: Implement processAction() method.
    $contactId = $triggerData->getContactId();
    $entity = "Email";
    $action = "send";
    $params = array(
      "template_id" => "83",
      "from_name" => "Kin",
      "from_email_option" => 1,
      "contact_id = 70",
      "from_email" => "admin@kin.coop",
      "disable_smarty" => false,
    );
    civicrm_api3($entity, $action, $params);
  }

  /**
   * @inheritDoc
   */
  public function getExtraDataInputUrl($ruleActionId)
  {
    // TODO: Implement getExtraDataInputUrl() method.
    return FALSE;
  }
}
