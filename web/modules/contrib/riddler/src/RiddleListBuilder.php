<?php

namespace Drupal\riddler;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of riddles.
 */
class RiddleListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['question'] = $this->t('Question');
    $header['solution'] = $this->t('Solution');
    $header['hint'] = $this->t('Hint');
    $header['status'] = $this->t('Status');

    // Inform users about CAPTCHA caching risks:
    $this->messenger()->addWarning('Page caching is only compatible with ONE single riddle. If > 1 riddles are enabled, CAPTCHA disables caching for these pages, which may have a negative impact on performance. See related CAPTCHA issues for details.');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['question'] = $entity->getQuestion();
    $row['solution'] = $entity->getSolution();
    $row['hint'] = $entity->getHint();
    $row['status'] = $entity->status() ? $this->t('Enabled') : $this->t('Disabled');
    return $row + parent::buildRow($entity);
  }

}
