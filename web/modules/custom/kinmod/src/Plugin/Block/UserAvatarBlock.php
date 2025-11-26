<?php

  namespace Drupal\kinmod\Plugin\Block;

  use Drupal\Core\Block\BlockBase;
  use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
  use Drupal\Core\Session\AccountProxyInterface;
  use Drupal\Core\Url;
  use Drupal\user\Entity\User;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * Provides a 'User avatar block'.
   *
   * @Block(
   *   id = "user_avatar_block",
   *   admin_label = @Translation("User avatar block"),
   * )
   */
  class UserAvatarBlock extends BlockBase implements ContainerFactoryPluginInterface {

    /**
     * The current user service.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
      $instance = new static($configuration, $plugin_id, $plugin_definition);
      $instance->currentUser = $container->get('current_user');
      return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function build() {
      $uid = $this->currentUser->id();
      if ($uid == 0) {
        // Anonymous user: show nothing or a default image.
        return ['#markup' => ''];
      }

      $account = User::load($uid);
      if ($account && $account->hasField('user_picture') && !$account->get('user_picture')->isEmpty()) {
        return $account->get('user_picture')->view('thumbnail');
      } else {
        $edit_url = Url::fromRoute('entity.user.edit_form', ['user' => $uid]);

        $build = [
          '#type' => 'link',
          '#title' => [
            '#markup' => '<div class="border border-primary border-3"><i class="bi bi-person-fill fs-1"></i></div>',
          ],
          '#url' => $edit_url,
          '#attributes' => [
            'class' => ['user-avatar-link'],
            'title' => $this->t('Your image here'),
          ],
          '#allowed_tags' => ['div', 'i', 'a'],
        ];
      }
      // Important: make this block vary per user.
      $build['#cache']['contexts'][] = 'user';

      return $build;

    }

  }
