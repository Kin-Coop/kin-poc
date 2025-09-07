<?php

  namespace Drupal\kin_forum\Plugin\Block;

  use Drupal\Core\Block\BlockBase;
  use Drupal\Core\Form\FormBuilderInterface;
  use Drupal\comment\Entity\Comment;
  use Drupal\field_group_migrate\Plugin\migrate\source\d6\FieldGroup;
  use Drupal\node\Entity\Node;
  use Symfony\Component\DependencyInjection\ContainerInterface;
  use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
  use Drupal\Core\Form\FormState;
  use Drupal\kin_civi\Service\Utils;
  use Drupal\Core\Session\AccountInterface;

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

        // Need to check if the user has access to this forum page before loading the comment form.
        $utils = new Utils();
        $current_user = \Drupal::currentUser();
        $uid = $current_user->id();
        $cid = $utils->kin_civi_get_contact_id($uid);

        \Drupal::logger('Household Access')->notice('<pre><code>@data</code></pre>', ['@data' => $utils->kin_civi_check_contact_in_group($cid, $group_id)]);

        if(!$utils->kin_civi_check_contact_in_group($cid, $group_id)) {
          return FALSE;
        }
        

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
