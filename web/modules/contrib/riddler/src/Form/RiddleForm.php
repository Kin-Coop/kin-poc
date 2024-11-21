<?php

namespace Drupal\riddler\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Riddle form.
 *
 * @property \Drupal\riddler\Entity $entity
 */
class RiddleForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);

    $form['question'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Question'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->getQuestion() ?? '',
      '#description' => $this->t('The question to answer by one of the allowed answers below.'),
      '#required' => TRUE,
    ];

    $form['solution'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Allowed answer(s)'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->getSolution() ?? '',
      '#description' => $this->t('The allowed answer(s) to this question. The answers case sensitivity are based on the captcha "Default CAPTCHA validation" setting. Multiple allowed answers can be separated by a comma (",").'),
      '#required' => TRUE,
    ];

    $form['hint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hint'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->getHint() ?? '',
      '#description' => $this->t('An optional hint to help get the question right.'),
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\riddler\Entity\Riddle::load',
        'source' => ['question'],
      ],
      '#disabled' => !$this->entity->isNew(),
    ];

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $this->entity->status(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);
    $message_args = ['%question' => $this->entity->getQuestion()];
    $message = $result == SAVED_NEW
      ? $this->t('Created new CAPTCHA Riddler question: "%question".', $message_args)
      : $this->t('Updated CAPTCHA Riddler question: "%question".', $message_args);
    $this->messenger()->addStatus($message);
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $result;
  }

}
