<?php

  namespace Drupal\kinmod\Plugin\Block;

  use Drupal\Core\Block\BlockBase;
  use Drupal\Core\Form\FormBuilderInterface;
  use Drupal\comment\Entity\Comment;
  use Drupal\node\Entity\Node;
  use Symfony\Component\DependencyInjection\ContainerInterface;
  use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
  use Drupal\Core\Form\FormState;

  /**
   * Provides a Comment Form block.
   *
   * @Block(
   *   id = "comment_form_block",
   *   admin_label = @Translation("Comment Form Block")
   * )
   */
  class CommentFormBlock extends BlockBase implements ContainerFactoryPluginInterface {

    protected $formBuilder;

    public function __construct( array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $formBuilder ) {
      parent::__construct( $configuration, $plugin_id, $plugin_definition );
      $this->formBuilder = $formBuilder;
    }

    public static function create( ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition ) {
      return new static( $configuration, $plugin_id, $plugin_definition, $container->get( 'form_builder' ) );
    }

    public function build() {
      $current_path = \Drupal::service('path.current')->getPath();
      $args = explode('/', $current_path);

      if (!empty($args[3])) {
        $group_id = (int) $args[3];

        $nids = \Drupal::entityTypeManager()
          ->getStorage('node')
          ->getQuery()
          ->accessCheck(TRUE)
          ->condition('type', 'group_forum')
          ->condition('field_group', $group_id)
          ->range(0, 1)
          ->execute();

        if (!empty($nids)) {
          $node = \Drupal\node\Entity\Node::load(reset($nids));

          if ($node) {
            $comment = Comment::create([
              'entity_type' => 'node',
              'entity_id'   => $node->id(),
              'field_name'  => 'field_comments',
            ]);

            $form_object = \Drupal::entityTypeManager()
              ->getFormObject('comment', 'default');

            $form_object->setEntity($comment);

            // ✅ Create a proper FormState object
            $form_state = new FormState();

            // ✅ Pass it by reference
            return $this->formBuilder->buildForm($form_object, $form_state);
          }
        }
      }

      return [
        '#markup' => $this->t('Comment form is not available.'),
      ];
    }
  }
