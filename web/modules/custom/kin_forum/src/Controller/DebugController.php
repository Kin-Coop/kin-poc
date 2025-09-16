<?php

namespace Drupal\kin_forum\Controller;

use Drupal\Core\Controller\ControllerBase;

class DebugController extends ControllerBase {
  public function debugConstraints() {
    //$field_definition = \Drupal::service('entity_field.manager')
      //->getFieldDefinitions('node', 'group_forum')['field_group'];

    //$constraints = $field_definition->getConstraints();

    //dd($field_definition->getConstraints());

    //$manager = \Drupal::service('plugin.manager.constraint');
    //dd(array_keys($manager->getDefinitions()));

    $violations = \Drupal::service('validator')->validate($node->field_group);
    //dd($violations);



    //return [
      //'#markup' => '<pre>' . print_r($constraints, TRUE) . '</pre>',
    //];
  }
}
