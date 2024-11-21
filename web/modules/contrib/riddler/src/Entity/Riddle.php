<?php

namespace Drupal\riddler\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the riddle entity type.
 *
 * @ConfigEntityType(
 *   id = "riddle",
 *   label = @Translation("Riddle"),
 *   label_collection = @Translation("Riddles"),
 *   label_singular = @Translation("riddle"),
 *   label_plural = @Translation("riddles"),
 *   label_count = @PluralTranslation(
 *     singular = "@count riddle",
 *     plural = "@count riddles",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\riddler\RiddleListBuilder",
 *     "form" = {
 *       "add" = "Drupal\riddler\Form\RiddleForm",
 *       "edit" = "Drupal\riddler\Form\RiddleForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "riddle",
 *   admin_permission = "administer CAPTCHA settings",
 *   links = {
 *     "collection" = "/admin/structure/riddler-riddle",
 *     "add-form" = "/admin/structure/riddler-riddle/add",
 *     "edit-form" = "/admin/structure/riddler-riddle/{riddle}",
 *     "delete-form" = "/admin/structure/riddler-riddle/{riddle}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "question" = "question",
 *     "solution" = "solution",
 *     "uuid" = "uuid",
 *     "hint" = "hint"
 *   },
 *   config_export = {
 *     "id",
 *     "question",
 *     "solution",
 *     "hint"
 *   }
 * )
 */
class Riddle extends ConfigEntityBase {

  /**
   * The riddle ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The riddle question.
   *
   * @var string
   */
  protected $question;

  /**
   * The riddle solution.
   *
   * @var string
   */
  protected $solution;

  /**
   * The question hint.
   *
   * @var string
   */
  protected $hint;

  /**
   * The riddle status.
   *
   * @var bool
   */
  protected $status = TRUE;

  /**
   * Get the riddle question.
   *
   * @return string
   *   The riddle question.
   */
  public function getQuestion() {
    return $this->question;
  }

  /**
   * Get the riddle solution.
   *
   * @return string
   *   The riddle solution.
   */
  public function getSolution() {
    return $this->solution;
  }

  /**
   * Get the question hint.
   *
   * @return string
   *   The hint.
   */
  public function getHint() {
    return $this->hint;
  }

  /**
   * Get the riddle status.
   *
   * @return bool
   *   The status.
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * Set the riddle question.
   *
   * @param string $question
   *   The riddle question.
   *
   * @return self
   *   The object.
   */
  public function setQuestion(string $question) {
    $this->question = $question;

    return $this;
  }

  /**
   * Set the riddle solution.
   *
   * @param string $solution
   *   The riddle solution.
   *
   * @return self
   *   The object.
   */
  public function setSolution(string $solution) {
    $this->solution = $solution;

    return $this;
  }

  /**
   * Set the question hint.
   *
   * @param string $hint
   *   The question hint.
   *
   * @return self
   *   The object.
   */
  public function setHint(string $hint) {
    $this->hint = $hint;

    return $this;
  }

}
