<?php

use Civi\Api4\Email;
use Civi\Api4\Mailing;
use Civi\Api4\Activity;

function xxkin_civi_send_email($contactId) {
    $templateId = 1; // Change to your template ID
    $template = \Civi\Api4\MessageTemplate::get()
        ->addWhere('id', '=', $templateId)
        ->addSelect('msg_subject', 'msg_html', 'msg_text')
        ->execute()
        ->first();

    $contactId = 123; // Replace with the target contact ID
    $emailAddress = 'example@example.com'; // Replace with contact's email

    $email = \Civi\Api4\Email::get()
        ->addWhere('contact_id', '=', $contactId)
        ->addSelect('id', 'email')
        ->execute()
        ->first();

    if ($email) {
        \Civi\Api4\Mailing::send()
            ->setValue('subject', $template['msg_subject'])
            ->setValue('body_html', $template['msg_html'])
            ->setValue('body_text', $template['msg_text'])
            ->setValue('to_name', 'Recipient Name') // Replace as needed
            ->setValue('to_email', $email['email'])
            ->setValue('from_email', 'your-email@example.com') // Replace with sender
            ->execute();
    }

    \Civi\Api4\Activity::create()
        ->setValue('source_contact_id', 1) // ID of sender (e.g., admin)
        ->setValue('target_contact_id', [$contactId]) // ID of recipient
        ->setValue('activity_type_id', 'Email Sent') // Ensure this activity type exists
        ->setValue('subject', $template['msg_subject'])
        ->setValue('details', $template['msg_html'])
        ->execute();

    return TRUE;

}


