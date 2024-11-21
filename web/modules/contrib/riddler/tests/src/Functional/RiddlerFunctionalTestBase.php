<?php

namespace Drupal\Tests\riddler\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests Riddler main test case sensitivity.
 *
 * @group captcha
 */
abstract class RiddlerFunctionalTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'test_page_test',
    'block',
    'comment',
    'captcha',
    'riddler',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * A test user with administrative privileges.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * A riddle storage object.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $riddleStorage;

  /**
   * {@inheritdoc}
   */
  protected function setUp():void {
    parent::setUp();
    $this->config('system.site')->set('page.front', '/test-page')->save();
    $this->adminUser = $this->drupalCreateUser([]);
    $this->adminUser->addRole($this->createAdminRole('admin', 'admin'));
    $this->adminUser->save();
    $this->drupalLogin($this->adminUser);
    // Create a riddle storage object:
    $this->riddleStorage = \Drupal::service('entity_type.manager')->getStorage('riddle');
  }

}
