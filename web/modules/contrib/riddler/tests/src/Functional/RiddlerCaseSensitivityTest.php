<?php

namespace Drupal\Tests\riddler\Functional;

use Drupal\captcha\Constants\CaptchaConstants;

/**
 * Tests Riddler main test case sensitivity.
 *
 * @group captcha
 */
class RiddlerCaseSensitivityTest extends RiddlerFunctionalTestBase {

  /**
   * A test user with administrative privileges.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp():void {
    parent::setUp();
    $this->user = $this->drupalCreateUser();
    // Logout admin user:
    $this->drupalLogout();
  }

  /**
   * Log in with inserting an answer in the captcha question.
   *
   * @param string $solution
   *   The captcha solution to insert into the captcha prompt.
   */
  protected function loginWithRiddleAnswer(string $solution) {
    $session = $this->assertSession();
    // Check if there is a CAPTCHA on the login form (look for the title).
    $this->drupalGet('user');
    $session->pageTextContains('This question is for testing whether or not you are a human visitor and to prevent automated spam submissions.');
    $session->pageTextContains('Yes?');

    // Try to log in with incorrect string:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => $solution,
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');
  }

  /**
   * Test the case insensititvity for different solution / answer cases.
   */
  public function testRiddlerCaseInSensitivityCaseOne() {
    // Set a CAPTCHA on login form:
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();
    // Set different Question on example riddle:
    $this->riddleStorage->load('example')->setQuestion('Yes?')->save();
    $session = $this->assertSession();
    // Set captcha case sensitivity:
    $this->config('captcha.settings')->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_INSENSITIVE)->save();

    $exampleRiddle = $this->riddleStorage->load('example');
    $exampleRiddle->setSolution('Yes')->save();

    $this->loginWithRiddleAnswer('Yes');
    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());
  }

  /**
   * Test the case insensititvity for different solution / answer cases.
   */
  public function testRiddlerCaseInSensitivityCaseTwo() {
    // Set a CAPTCHA on login form:
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();
    // Set different Question on example riddle:
    $this->riddleStorage->load('example')->setQuestion('Yes?')->save();
    $session = $this->assertSession();
    // Set captcha case sensitivity:
    $this->config('captcha.settings')->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_INSENSITIVE)->save();

    $exampleRiddle = $this->riddleStorage->load('example');
    $exampleRiddle->setSolution('Yes')->save();

    $this->loginWithRiddleAnswer('YES');
    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());
  }

  /**
   * Test the case insensititvity for different solution / answer cases.
   */
  public function testRiddlerCaseInSensitivityCaseThree() {
    $session = $this->assertSession();

    // Set a CAPTCHA on login form:
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();
    // Set different Question on example riddle:
    $this->riddleStorage->load('example')->setQuestion('Yes?')->save();
    // Set captcha case sensitivity:
    $this->config('captcha.settings')->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_INSENSITIVE)->save();

    $exampleRiddle = $this->riddleStorage->load('example');
    $exampleRiddle->setSolution('Yes')->save();

    $this->loginWithRiddleAnswer('yes');
    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());
  }

  /**
   * Test the case insensititvity for different solution / answer cases.
   */
  public function testRiddlerCaseInSensitivityCaseFour() {
    $session = $this->assertSession();

    // Set a CAPTCHA on login form:
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();
    // Set different Question on example riddle:
    $this->riddleStorage->load('example')->setQuestion('Yes?')->save();
    // Set captcha case sensitivity:
    $this->config('captcha.settings')->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_INSENSITIVE)->save();

    $exampleRiddle = $this->riddleStorage->load('example');
    $exampleRiddle->setSolution('YES')->save();

    $this->loginWithRiddleAnswer('Yes');
    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());
  }

  /**
   * Test the case insensititvity for different solution / answer cases.
   */
  public function testRiddlerCaseInSensitivityCaseFive() {
    $session = $this->assertSession();

    // Set a CAPTCHA on login form:
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();
    // Set different Question on example riddle:
    $this->riddleStorage->load('example')->setQuestion('Yes?')->save();
    // Set captcha case sensitivity:
    $this->config('captcha.settings')->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_INSENSITIVE)->save();

    $exampleRiddle = $this->riddleStorage->load('example');
    $exampleRiddle->setSolution('YES')->save();

    $this->loginWithRiddleAnswer('YES');
    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());
  }

  /**
   * Test the case insensititvity for different solution / answer cases.
   */
  public function testRiddlerCaseInSensitivityCaseSix() {
    $session = $this->assertSession();

    // Set a CAPTCHA on login form:
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();
    // Set different Question on example riddle:
    $this->riddleStorage->load('example')->setQuestion('Yes?')->save();
    // Set captcha case sensitivity:
    $this->config('captcha.settings')->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_INSENSITIVE)->save();

    $exampleRiddle = $this->riddleStorage->load('example');
    $exampleRiddle->setSolution('YES')->save();

    $this->loginWithRiddleAnswer('yes');
    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());
  }

  /**
   * Test the case insensititvity for different solution / answer cases.
   */
  public function testRiddlerCaseInSensitivityCaseSeven() {
    $session = $this->assertSession();

    // Set a CAPTCHA on login form:
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();
    // Set different Question on example riddle:
    $this->riddleStorage->load('example')->setQuestion('Yes?')->save();
    // Set captcha case sensitivity:
    $this->config('captcha.settings')->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_INSENSITIVE)->save();

    $exampleRiddle = $this->riddleStorage->load('example');
    $exampleRiddle->setSolution('yes')->save();

    $this->loginWithRiddleAnswer('Yes');
    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());
  }

  /**
   * Test the case insensititvity for different solution / answer cases.
   */
  public function testRiddlerCaseInSensitivityCaseEight() {
    $session = $this->assertSession();

    // Set a CAPTCHA on login form:
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();
    // Set different Question on example riddle:
    $this->riddleStorage->load('example')->setQuestion('Yes?')->save();
    // Set captcha case sensitivity:
    $this->config('captcha.settings')->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_INSENSITIVE)->save();

    $exampleRiddle = $this->riddleStorage->load('example');
    $exampleRiddle->setSolution('yes')->save();

    $this->loginWithRiddleAnswer('YES');
    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());
  }

  /**
   * Test the case insensititvity for different solution / answer cases.
   */
  public function testRiddlerCaseInSensitivityCaseNine() {
    $session = $this->assertSession();

    // Set a CAPTCHA on login form:
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();
    // Set different Question on example riddle:
    $this->riddleStorage->load('example')->setQuestion('Yes?')->save();
    // Set captcha case sensitivity:
    $this->config('captcha.settings')->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_INSENSITIVE)->save();

    $exampleRiddle = $this->riddleStorage->load('example');
    $exampleRiddle->setSolution('yes')->save();

    $this->loginWithRiddleAnswer('yes');
    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());
  }

  /**
   * Test the case sensititvity for different solution / answer cases.
   */
  public function testRiddlerCaseSensitivityCaseOne() {
    $session = $this->assertSession();

    // Set a CAPTCHA on login form:
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();
    // Set different Question on example riddle:
    $this->riddleStorage->load('example')->setQuestion('Yes?')->save();
    // Set captcha case sensitivity:
    $this->config('captcha.settings')->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_SENSITIVE)->save();

    $exampleRiddle = $this->riddleStorage->load('example');
    $exampleRiddle->setSolution('Yes')->save();

    $this->loginWithRiddleAnswer('Yes');
    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());
  }

  /**
   * Test the case sensititvity for different solution / answer cases.
   */
  public function testRiddlerCaseSensitivityCaseTwo() {
    $session = $this->assertSession();

    // Set a CAPTCHA on login form:
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();
    // Set different Question on example riddle:
    $this->riddleStorage->load('example')->setQuestion('Yes?')->save();
    // Set captcha case sensitivity:
    $this->config('captcha.settings')->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_SENSITIVE)->save();

    $exampleRiddle = $this->riddleStorage->load('example');
    $exampleRiddle->setSolution('Yes')->save();

    $this->loginWithRiddleAnswer('YES');
    // Check that the user really hasn't logged in:
    $this->drupalGet('user');
    $session->fieldExists('name');
    $session->fieldExists('pass');
  }

  /**
   * Test the case sensititvity for different solution / answer cases.
   */
  public function testRiddlerCaseSensitivityCaseThree() {
    $session = $this->assertSession();

    // Set a CAPTCHA on login form:
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();
    // Set different Question on example riddle:
    $this->riddleStorage->load('example')->setQuestion('Yes?')->save();
    // Set captcha case sensitivity:
    $this->config('captcha.settings')->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_SENSITIVE)->save();

    $exampleRiddle = $this->riddleStorage->load('example');
    $exampleRiddle->setSolution('Yes')->save();

    $this->loginWithRiddleAnswer('yes');
    // Check that the user really hasn't logged in:
    $this->drupalGet('user');
    $session->fieldExists('name');
    $session->fieldExists('pass');
  }

  /**
   * Test the case sensititvity for different solution / answer cases.
   */
  public function testRiddlerCaseSensitivityCaseFour() {
    $session = $this->assertSession();

    // Set a CAPTCHA on login form:
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();
    // Set different Question on example riddle:
    $this->riddleStorage->load('example')->setQuestion('Yes?')->save();
    // Set captcha case sensitivity:
    $this->config('captcha.settings')->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_SENSITIVE)->save();

    $exampleRiddle = $this->riddleStorage->load('example');
    $exampleRiddle->setSolution('YES')->save();

    $this->loginWithRiddleAnswer('Yes');
    // Check that the user really hasn't logged in:
    $this->drupalGet('user');
    $session->fieldExists('name');
    $session->fieldExists('pass');
  }

  /**
   * Test the case sensititvity for different solution / answer cases.
   */
  public function testRiddlerCaseSensitivityCaseFive() {
    $session = $this->assertSession();

    // Set a CAPTCHA on login form:
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();
    // Set different Question on example riddle:
    $this->riddleStorage->load('example')->setQuestion('Yes?')->save();
    // Set captcha case sensitivity:
    $this->config('captcha.settings')->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_SENSITIVE)->save();

    $exampleRiddle = $this->riddleStorage->load('example');
    $exampleRiddle->setSolution('YES')->save();

    $this->loginWithRiddleAnswer('YES');
    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());
  }

  /**
   * Test the case sensititvity for different solution / answer cases.
   */
  public function testRiddlerCaseSensitivityCaseSix() {
    $session = $this->assertSession();

    // Set a CAPTCHA on login form:
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();
    // Set different Question on example riddle:
    $this->riddleStorage->load('example')->setQuestion('Yes?')->save();
    // Set captcha case sensitivity:
    $this->config('captcha.settings')->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_SENSITIVE)->save();

    $exampleRiddle = $this->riddleStorage->load('example');
    $exampleRiddle->setSolution('YES')->save();

    $this->loginWithRiddleAnswer('yes');
    // Check that the user really hasn't logged in:
    $this->drupalGet('user');
    $session->fieldExists('name');
    $session->fieldExists('pass');
  }

  /**
   * Test the case sensititvity for different solution / answer cases.
   */
  public function testRiddlerCaseSensitivityCaseSeven() {
    $session = $this->assertSession();

    // Set a CAPTCHA on login form:
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();
    // Set different Question on example riddle:
    $this->riddleStorage->load('example')->setQuestion('Yes?')->save();
    // Set captcha case sensitivity:
    $this->config('captcha.settings')->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_SENSITIVE)->save();

    $exampleRiddle = $this->riddleStorage->load('example');
    $exampleRiddle->setSolution('yes')->save();

    $this->loginWithRiddleAnswer('Yes');
    // Check that the user really hasn't logged in:
    $this->drupalGet('user');
    $session->fieldExists('name');
    $session->fieldExists('pass');
  }

  /**
   * Test the case sensititvity for different solution / answer cases.
   */
  public function testRiddlerCaseSensitivityCaseEight() {
    $session = $this->assertSession();

    // Set a CAPTCHA on login form:
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();
    // Set different Question on example riddle:
    $this->riddleStorage->load('example')->setQuestion('Yes?')->save();
    // Set captcha case sensitivity:
    $this->config('captcha.settings')->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_SENSITIVE)->save();

    $exampleRiddle = $this->riddleStorage->load('example');
    $exampleRiddle->setSolution('yes')->save();

    $this->loginWithRiddleAnswer('YES');
    // Check that the user really hasn't logged in:
    $this->drupalGet('user');
    $session->fieldExists('name');
    $session->fieldExists('pass');
  }

  /**
   * Test the case sensititvity for different solution / answer cases.
   */
  public function testRiddlerCaseSensitivityCaseNine() {
    $session = $this->assertSession();

    // Set a CAPTCHA on login form:
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();
    // Set different Question on example riddle:
    $this->riddleStorage->load('example')->setQuestion('Yes?')->save();
    // Set captcha case sensitivity:
    $this->config('captcha.settings')->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_SENSITIVE)->save();

    $exampleRiddle = $this->riddleStorage->load('example');
    $exampleRiddle->setSolution('yes')->save();

    $this->loginWithRiddleAnswer('yes');
    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());
  }

  /**
   * Testing if we can brute force the login form with case sensitivtiy enabled.
   */
  public function testRiddlerCaseSensitiveBruteForceLoginForm() {
    $session = $this->assertSession();

    // Set a CAPTCHA on login form:
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();
    // Set different Question on example riddle:
    $this->riddleStorage->load('example')->setQuestion('Yes?')->save();
    // Set captcha case sensitivity:
    $this->config('captcha.settings')->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_SENSITIVE)->save();

    $exampleRiddle = $this->riddleStorage->load('example');
    $exampleRiddle->setSolution('YES')->save();

    // Brute force the login screen with an incorrect riddler answer:
    for ($i = 0; $i < 10; $i++) {
      $this->loginWithRiddleAnswer('Yes');
      // Check that the user really hasn't logged in:
      $this->drupalGet('user');
      $session->fieldExists('name');
      $session->fieldExists('pass');
    }
  }

  /**
   * Testing if we can brute force the login form without case sensitivity.
   */
  public function testRiddlerCaseInsensitiveGeneralBruteForceLoginForm() {
    $session = $this->assertSession();

    // Set a CAPTCHA on login form:
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();
    // Set different Question on example riddle:
    $this->riddleStorage->load('example')->setQuestion('Yes?')->save();
    // Set captcha case sensitivity:
    $this->config('captcha.settings')->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_INSENSITIVE)->save();

    $exampleRiddle = $this->riddleStorage->load('example');
    $exampleRiddle->setSolution('Yes')
      ->save();

    // Brute force the login screen with an incorrect riddler answer:
    for ($i = 0; $i < 10; $i++) {
      $this->loginWithRiddleAnswer('No');
      // Check that the user really hasn't logged in:
      $this->drupalGet('user');
      $session->fieldExists('name');
      $session->fieldExists('pass');
    }
  }

  /**
   * Tests the riddler captcha case sensitivity.
   */
  public function testRiddlerCaptchaCaseSensitivity() {
    $session = $this->assertSession();

    $config = $this->config('captcha.settings');
    // Enable the case sensitivity:
    $config->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_SENSITIVE);
    $config->save();

    // Delete all riddles:
    $this->riddleStorage->delete($this->riddleStorage->loadMultiple());
    // Create a new test riddle:
    $this->riddleStorage->create([
      'id' => 'test',
      'question' => 'Do you like case sensitivity?',
      'solution' => 'YES I DO!',
      'status' => TRUE,
    ])->save();

    // Set a CAPTCHA on login form.
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();

    // Check if there is a CAPTCHA on the login form (look for the title).
    $this->drupalGet('user');
    $session->pageTextContains('This question is for testing whether or not you are a human visitor and to prevent automated spam submissions.');
    $session->pageTextContains('Do you like case sensitivity?');

    // Try to log in, which should fail, as the case is wrong:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'Yes I do!',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');
    // Check for error message.
    $session->pageTextContains('The answer you entered for the CAPTCHA was not correct.');
    // Check that the user really hasn't logged in:
    $this->drupalGet('user');
    $session->fieldExists('name');
    $session->fieldExists('pass');

    // Try to log in with the correct case:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'YES I DO!',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');

    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());

    // Logout:
    $this->drupalLogout();

    // Try to log in, with a complete different answer, which should fail:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'Completely different answer',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');
    // Check for error message.
    $session->pageTextContains('The answer you entered for the CAPTCHA was not correct.');
    // Check that the user really hasn't logged in:
    $this->drupalGet('user');
    $session->fieldExists('name');
    $session->fieldExists('pass');
  }

  /**
   * Tests the riddler captcha case insensitivity.
   */
  public function testRiddlerCaptchaCaseInsensitivity() {
    $session = $this->assertSession();

    $config = $this->config('captcha.settings');
    // Enable the case sensitivity:
    $config->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_INSENSITIVE);
    $config->save();

    // Delete all riddles:
    $this->riddleStorage->delete($this->riddleStorage->loadMultiple());
    // Create a new test riddle:
    $this->riddleStorage->create([
      'id' => 'test',
      'question' => 'Do you like case sensitivity?',
      'solution' => 'YES I DO!',
      'status' => TRUE,
    ])->save();

    // Set a CAPTCHA on login form.
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();

    // Check if there is a CAPTCHA on the login form (look for the title).
    $this->drupalGet('user');
    $session->pageTextContains('This question is for testing whether or not you are a human visitor and to prevent automated spam submissions.');
    $session->pageTextContains('Do you like case sensitivity?');

    // Try to log in, with an incorrect case, which should succeed:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'Yes I do!',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');

    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());

    // Logout:
    $this->drupalLogout();

    // Try to log in with the correct case, which should also succeed:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'YES I DO!',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');

    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());

    // Logout:
    $this->drupalLogout();

    // Try to log in, with a complete different answer, which should fail:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'Completely different answer',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');
    // Check for error message.
    $session->pageTextContains('The answer you entered for the CAPTCHA was not correct.');
    // Check that the user really hasn't logged in:
    $this->drupalGet('user');
    $session->fieldExists('name');
    $session->fieldExists('pass');
  }

  /**
   * Tests the riddler captcha case sensitivity with multiple answers.
   */
  public function testRiddlerCaptchaCaseSensitivityMultipleAnswers() {
    $session = $this->assertSession();

    $config = $this->config('captcha.settings');
    // Enable the case sensitivity:
    $config->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_SENSITIVE);
    $config->save();

    // Delete all riddles:
    $this->riddleStorage->delete($this->riddleStorage->loadMultiple());
    // Create a new test riddle:
    $this->riddleStorage->create([
      'id' => 'test',
      'question' => 'Do you like case sensitivity?',
      'solution' => 'YeS, YES I DO!, Indeed',
      'status' => TRUE,
    ])->save();

    // Set a CAPTCHA on login form.
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();

    // Check if there is a CAPTCHA on the login form (look for the title).
    $this->drupalGet('user');
    $session->pageTextContains('This question is for testing whether or not you are a human visitor and to prevent automated spam submissions.');
    $session->pageTextContains('Do you like case sensitivity?');

    // Try to log in, which should fail, as the case is wrong:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'Yes I do!',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');
    // Check for error message.
    $session->pageTextContains('The answer you entered for the CAPTCHA was not correct.');
    // Check that the user really hasn't logged in:
    $this->drupalGet('user');
    $session->fieldExists('name');
    $session->fieldExists('pass');

    // Try to log in with the correct case:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'YES I DO!',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');

    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());

    // Logout:
    $this->drupalLogout();

    // Try to log in, with a complete different answer, which should fail:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'Completely different answer',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');
    // Check for error message.
    $session->pageTextContains('The answer you entered for the CAPTCHA was not correct.');
    // Check that the user really hasn't logged in:
    $this->drupalGet('user');
    $session->fieldExists('name');
    $session->fieldExists('pass');

    // Another possible answer with the incorrect case should also fail:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'indEEd',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');

    // Check for error message.
    $session->pageTextContains('The answer you entered for the CAPTCHA was not correct.');
    // Check that the user really hasn't logged in:
    $this->drupalGet('user');
    $session->fieldExists('name');
    $session->fieldExists('pass');

    // With the correct case, it should work:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'Indeed',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');

    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());
  }

  /**
   * Tests the riddler captcha case insensitivity with multiple answers.
   */
  public function testRiddlerCaptchaCaseInsensitivityMultipleAnswers() {
    $session = $this->assertSession();

    $config = $this->config('captcha.settings');
    // Enable the case sensitivity:
    $config->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_INSENSITIVE);
    $config->save();

    // Delete all riddles:
    $this->riddleStorage->delete($this->riddleStorage->loadMultiple());
    // Create a new test riddle:
    $this->riddleStorage->create([
      'id' => 'test',
      'question' => 'Do you like case sensitivity?',
      'solution' => 'YeS, YES I DO!, Indeed',
      'status' => TRUE,
    ])->save();

    // Set a CAPTCHA on login form.
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();

    // Check if there is a CAPTCHA on the login form (look for the title).
    $this->drupalGet('user');
    $session->pageTextContains('This question is for testing whether or not you are a human visitor and to prevent automated spam submissions.');
    $session->pageTextContains('Do you like case sensitivity?');

    // Try to log in, with an incorrect case, which should succeed:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'Yes I do!',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');

    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());

    // Logout:
    $this->drupalLogout();

    // Try to log in with the correct case, which should also succeed:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'YES I DO!',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');

    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());

    // Logout:
    $this->drupalLogout();

    // Try to log in, with a complete different answer, which should fail:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'Completely different answer',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');
    // Check for error message.
    $session->pageTextContains('The answer you entered for the CAPTCHA was not correct.');
    // Check that the user really hasn't logged in:
    $this->drupalGet('user');
    $session->fieldExists('name');
    $session->fieldExists('pass');

    // Another possible answer with the incorrect case should also succeed:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'indEEd',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');

    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());

    // Logout:
    $this->drupalLogout();

    // With the correct case, it should also work:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'Indeed',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');

    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());
  }

  /**
   * Tests the riddler captcha case sensitivity with multiple riddles.
   */
  public function testRiddlerCaptchaCaseSensitivityMultipleRiddles() {
    $session = $this->assertSession();

    $config = $this->config('captcha.settings');
    // Enable the case sensitivity:
    $config->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_SENSITIVE);
    $config->save();

    // Delete all riddles:
    $this->riddleStorage->delete($this->riddleStorage->loadMultiple());
    // Create a new test riddle:
    $this->riddleStorage->create([
      'id' => 'test',
      'question' => 'Do you like case sensitivity?',
      'solution' => 'YeS, YES I DO!, Indeed',
      'status' => TRUE,
    ])->save();
    // Create the exact same riddle with a different id, as we can NOT
    // differentiate riddles through tests:
    $this->riddleStorage->create([
      'id' => 'test2',
      'question' => 'Do you like case sensitivity?',
      'solution' => 'YeS, YES I DO!, Indeed',
      'status' => TRUE,
    ])->save();

    // Set a CAPTCHA on login form.
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();

    // Check if there is a CAPTCHA on the login form (look for the title).
    $this->drupalGet('user');
    $session->pageTextContains('This question is for testing whether or not you are a human visitor and to prevent automated spam submissions.');
    $session->pageTextContains('Do you like case sensitivity?');

    // Try to log in, which should fail, as the case is wrong:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'Yes I do!',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');
    // Check for error message.
    $session->pageTextContains('The answer you entered for the CAPTCHA was not correct.');
    // Check that the user really hasn't logged in:
    $this->drupalGet('user');
    $session->fieldExists('name');
    $session->fieldExists('pass');

    // Try to log in with the correct case:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'YES I DO!',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');

    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());

    // Logout:
    $this->drupalLogout();

    // Try to log in, with a complete different answer, which should fail:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'Completely different answer',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');
    // Check for error message.
    $session->pageTextContains('The answer you entered for the CAPTCHA was not correct.');
    // Check that the user really hasn't logged in:
    $this->drupalGet('user');
    $session->fieldExists('name');
    $session->fieldExists('pass');

    // Another possible answer with the incorrect case should also fail:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'indEEd',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');

    // Check for error message.
    $session->pageTextContains('The answer you entered for the CAPTCHA was not correct.');
    // Check that the user really hasn't logged in:
    $this->drupalGet('user');
    $session->fieldExists('name');
    $session->fieldExists('pass');

    // With the correct case, it should work:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'Indeed',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');

    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());
  }

  /**
   * Tests the riddler captcha case insensitivity with multiple riddles.
   */
  public function testRiddlerCaptchaCaseInsensitivityMultipleRiddles() {
    $session = $this->assertSession();

    $config = $this->config('captcha.settings');
    // Enable the case sensitivity:
    $config->set('default_validation', CaptchaConstants::CAPTCHA_DEFAULT_VALIDATION_CASE_INSENSITIVE);
    $config->save();

    // Delete all riddles:
    $this->riddleStorage->delete($this->riddleStorage->loadMultiple());
    // Create a new test riddle:
    $this->riddleStorage->create([
      'id' => 'test',
      'question' => 'Do you like case sensitivity?',
      'solution' => 'YeS, YES I DO!, Indeed',
      'status' => TRUE,
    ])->save();
    // Create the exact same riddle with a different id, as we can NOT
    // differentiate riddles through tests:
    $this->riddleStorage->create([
      'id' => 'test2',
      'question' => 'Do you like case sensitivity?',
      'solution' => 'YeS, YES I DO!, Indeed',
      'status' => TRUE,
    ])->save();

    // Set a CAPTCHA on login form.
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('riddler/Riddler');
    $captcha_point->enable()->save();

    // Check if there is a CAPTCHA on the login form (look for the title).
    $this->drupalGet('user');
    $session->pageTextContains('This question is for testing whether or not you are a human visitor and to prevent automated spam submissions.');
    $session->pageTextContains('Do you like case sensitivity?');

    // Try to log in, with an incorrect case, which should succeed:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'Yes I do!',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');

    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());

    // Logout:
    $this->drupalLogout();

    // Try to log in with the correct case, which should also succeed:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'YES I DO!',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');

    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());

    // Logout:
    $this->drupalLogout();

    // Try to log in, with a complete different answer, which should fail:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'Completely different answer',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');
    // Check for error message.
    $session->pageTextContains('The answer you entered for the CAPTCHA was not correct.');
    // Check that the user really hasn't logged in:
    $this->drupalGet('user');
    $session->fieldExists('name');
    $session->fieldExists('pass');

    // Another possible answer with the incorrect case should also succeed:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'indEEd',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');

    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());

    // Logout:
    $this->drupalLogout();

    // With the correct case, it should also work:
    $edit = [
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->pass_raw,
      'captcha_response' => 'Indeed',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');

    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($this->user->getDisplayName());
  }

}
