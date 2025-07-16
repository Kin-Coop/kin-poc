<?php
  
  namespace Drupal\kinmod\Plugin\Block;
  
  use Drupal\Core\Block\BlockBase;
  use Drupal\Core\Form\FormBuilderInterface;
  use Drupal\comment\Entity\Comment;
  use Drupal\node\Entity\Node;
  use Symfony\Component\DependencyInjection\ContainerInterface;
  use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
  
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
      // You must define how to get the node ID; this assumes it's from the current route.
      $route_match = \Drupal::routeMatch();
      $node        = $route_match->getParameter( 'node' );
      
      if ( $node instanceof Node && $node->isPublished() ) {
        $comment_type = 'comment'; // Adjust if using a custom comment type.
        
        $comment = Comment::create( [
          'entity_type' => 'node',
          'entity_id'   => $node->id(),
          'field_name'  => 'comment',
          // Replace with your comment field machine name.
        ] );
        
        return $this->formBuilder->getForm( $comment );
      }
      
      return [
        '#markup' => $this->t( 'Comment form is not available.' ),
      ];
    }
  }
