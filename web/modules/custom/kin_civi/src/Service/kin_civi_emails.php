<?php
namespace Drupal\kin_civi\Service;

use Civi\Api4\Email;
use Civi\Api4\Mailing;
use Civi\Api4\Activity;
use CRM_Utils_Mail;

class kin_civi_emails {

    public function kin_civi_send_email($contactId, $params, $templateId = NULL) {

        $sent = CRM_Utils_Mail::send($params);

        if ($sent) {
            \Drupal::messenger()->addMessage('Email notification sent successfully.');
        } else {
            \Drupal::messenger()->addError('Failed to send email.');
        }


        // Create activity about contribution

        \Civi\Api4\Activity::create(FALSE)
            ->addValue('source_contact_id', $params['admin']) // ID of sender (e.g., admin)
            ->addValue('target_contact_id', $params['member']) // ID of recipient
            ->addValue('activity_type_id', 6)
            ->addValue('subject', $params['subject'])
            ->execute();

        return TRUE;

    }
}