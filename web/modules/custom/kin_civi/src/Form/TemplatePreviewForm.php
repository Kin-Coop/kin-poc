<?php

namespace Drupal\kin_civi\Form;

\Drupal::service('civicrm')->initialize();

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use CRM_Core_DAO_MessageTemplate;
use CRM_Core_Smarty;

class TemplatePreviewForm extends FormBase {

    public function getFormId(): string {
        return 'kin_civi_template_preview_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state): array {
        $form['template_id'] = [
            '#type' => 'number',
            '#title' => 'Message Template ID',
            '#required' => TRUE,
        ];

        $form['contact_id'] = [
            '#type' => 'number',
            '#title' => 'Contact ID (for token resolution)',
            '#required' => TRUE,
        ];

        $form['params'] = [
            '#type' => 'textarea',
            '#title' => 'Smarty Parameters (JSON)',
            '#description' => 'Example: {"first_name": "Jane", "group_code": "ABC123"}',
        ];

        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => 'Preview',
        ];

        if ($form_state->get('preview')) {
            $preview = $form_state->get('preview');

            $form['output'] = [
                '#type' => 'details',
                '#title' => 'Rendered Output',
                '#open' => TRUE,
            ];
            $form['output']['subject'] = [
                '#markup' => '<strong>Subject:</strong> ' . $preview['subject'],
            ];
            $form['output']['html'] = [
                '#markup' => '<strong>HTML:</strong><div style="border:1px solid #ccc; padding:10px; margin-top:5px;">' . $preview['html'] . '</div>',
            ];
            $form['output']['text'] = [
                '#markup' => '<strong>Text:</strong><pre>' . $preview['text'] . '</pre>',
            ];
        }

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state): void {
        $template_id = (int) $form_state->getValue('template_id');
        $contact_id = (int) $form_state->getValue('contact_id');
        $params_raw = $form_state->getValue('params');
        $params = [];

        if (!empty($params_raw)) {
            try {
                $params = json_decode($params_raw, TRUE, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $this->messenger()->addError('Invalid JSON in Smarty parameters.');
                return;
            }
        }

        /*
        try {
            /*
            //$preview = civicrm_api3('MessageTemplate', 'preview', [
              //  'id' => $template_id,
                //'contact_id' => $contact_id,
                //'params' => $params,
            //]);
            */
/*
            $preview = renderMessageTemplate($template_id, $contact_id, $params);
            $form_state->set('preview', $preview);

            //$form_state->set('preview', $preview['values'][0]);
            $form_state->setRebuild();
        } catch (\Exception $e) {
            $this->messenger()->addError('CiviCRM error: ' . $e->getMessage());
        }
        */

        $values = $form_state->getValues();

        // Get the Smarty engine.
        $smarty = \CRM_Core_Smarty::singleton();

        // Assign dynamic values from the form.
        $smarty->assign('first_name', $values['first_name']);
        $smarty->assign('group_code', $values['group_code']);

        $template = <<<EOT
Hello {\$first_name},

Your group code is {\$group_code}.
EOT;

        $rendered = $smarty->fetch("string:$template");

        // For debugging or saving
        \Drupal::logger('kin_civi')->info($rendered);
    }
}
