<?php

namespace Drupal\kin_civi\Service;

use Drupal\Core\Session\AccountInterface;
use Civi\Api4\UFMatch;

\Drupal::service('civicrm')->initialize();




class Utils
{
    public function kin_civi_check_contact_in_group($contact_id, $group_id) {
        try {
            $relationships = \Civi\Api4\Relationship::get(FALSE)
                ->addSelect('*')
                ->addWhere('contact_id_a', '=', $contact_id)
                ->addWhere('contact_id_b', '=', $group_id)
                ->setLimit(1)
                ->execute();

            if (empty($relationships[0])) {
                return false;
            } else {
                return true;
            }
        }
        catch (CiviCRM_API4_Exception $e) {
            \Civi::log()->error("API error during email lookup: " . $e->getMessage());
        }
    }

    public function kin_civi_get_name($cid) {
        try {
            // Query CiviCRM APIv4 to get the name from the id.
            $contacts = \Civi\Api4\Contact::get(FALSE)
                ->addSelect('display_name', 'email_primary.email')
                ->addWhere('id', '=', $cid)
                ->execute();

            if ($contacts) {
                //return (array) $contacts->first();
                return $contacts->first()['display_name'];
            } else {
                return FALSE;
            }
        }
        catch (APIException $e) {
            \Drupal::logger('kin_civi')->error('CiviCRM APIv4 error: @message', ['@message' => $e->getMessage()]);
        }
    }

    public function kin_civi_check_group($group_id) {
        try {
            $group = \Civi\Api4\Household::get(FALSE)
                ->addSelect('id', 'display_name')
                ->addWhere('id', '=', $group_id)
                ->setLimit(1)
                ->execute();
            if (!empty($group)) {
                return (array) $group->first();
            } else {
                return FALSE;
            }
        }
        catch (APIException $e) {
            \Drupal::logger('kin_civi')->error('CiviCRM APIv4 error: @message', ['@message' => $e->getMessage()]);
        }
    }

    public function kin_civi_get_id_from_email($email) {
        try {
            $individuals = \Civi\Api4\Individual::get(FALSE)
                ->addSelect('id')
                ->addWhere('email_primary.email', '=', $email)
                ->setLimit(1)
                ->execute();

            if (empty($individuals[0])) {
                \Drupal::logger('kin_civi')->error('CiviCRM APIv4 error: Contact not found.');
            } else {
                return $individuals[0]["id"];
            }
        }
        catch (CiviCRM_API4_Exception $e) {
            \Civi::log()->error("API error during email lookup: " . $e->getMessage());
        }
    }
}