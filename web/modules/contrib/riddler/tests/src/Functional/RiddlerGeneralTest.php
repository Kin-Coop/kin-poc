<?php

namespace Drupal\Tests\riddler\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests Riddler main test case sensitivity.
 *
 * @group captcha
 */
class RiddlerGeneralTest extends RiddlerFunctionalTestBase {

  /**
   * Tests if installing the module, won't break the site.
   */
  public function testInstallation() {
    $session = $this->assertSession();
    $this->drupalGet('<front>');
    // Ensure the status code is success:
    $session->statusCodeEquals(200);
    // Ensure the correct test page is loaded as front page:
    $session->pageTextContains('Test page text.');
  }

  /**
   * Tests if uninstalling the module, won't break the site.
   */
  public function testUninstallation() {
    // Go to uninstallation page an uninstall riddler:
    $session = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->drupalGet('/admin/modules/uninstall');
    $session->statusCodeEquals(200);
    $page->checkField('edit-uninstall-riddler');
    $page->pressButton('edit-submit');
    $session->statusCodeEquals(200);
    // Confirm uninstall:
    $page->pressButton('edit-submit');
    $session->statusCodeEquals(200);
    $session->pageTextContains('The selected modules have been uninstalled.');
    // Retest the frontpage:
    $this->drupalGet('<front>');
    // Ensure the status code is success:
    $session->statusCodeEquals(200);
    // Ensure the correct test page is loaded as front page:
    $session->pageTextContains('Test page text.');
  }

  /**
   * Method for testing the Riddler riddles administration overview.
   */
  public function testRiddlesOverview() {
    $session = $this->assertSession();

    // Go to riddle overview page and check if it exists:
    $this->drupalGet('/admin/config/people/captcha/riddler-riddle');
    $session->statusCodeEquals(200);
    $session->pageTextContains('Riddle configuration');
    // Check if the example riddler is seen in the table:
    $session->elementTextEquals('css', 'body > div > div > main > div > div > table > tbody > tr > td:nth-child(1)', 'Do you really hate Spam?');
    $session->elementTextEquals('css', 'body > div > div > main > div > div > table > tbody > tr > td:nth-child(2)', 'Yes,Yes!,y,absolutely,yeah');
  }

  /**
   * Method for testing to add a riddle via UI.
   */
  public function testAddRiddle() {
    $session = $this->assertSession();
    $page = $this->getSession()->getPage();
    // Go to riddle add page:
    $this->drupalGet('/admin/config/people/captcha/riddler-riddle/add');
    $session->statusCodeEquals(200);
    $session->pageTextContains('Add a riddle');
    // Check if all fields exist:
    $session->elementExists('css', '#edit-question');
    $session->elementExists('css', '#edit-solution');
    $session->elementExists('css', '#edit-status');
    // Create a riddle:
    $page->fillField('edit-id', 'test_question');
    $page->fillField('edit-question', 'Test Question?');
    $page->fillField('edit-solution', 'Test Solution!');
    $page->checkField('edit-status');
    $page->pressButton('edit-submit');
    // See if the riddle got created:
    $session->statusCodeEquals(200);
    $session->pageTextContains('Created new CAPTCHA Riddler question: "Test Question?".');
    // See if newly created riddle appears in table:
    $session->elementTextEquals('css', 'body > div > div > main > div > div > table > tbody > tr:nth-child(2) > td:nth-child(1)', 'Test Question?');
    $session->elementTextEquals('css', 'body > div > div > main > div > div > table > tbody > tr:nth-child(2) > td:nth-child(2)', 'Test Solution!');
  }

  /**
   * Method for testing to edit a riddle via UI.
   */
  public function testEditRiddle() {
    $session = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->riddleStorage->create([
      'id' => 'test',
      'question' => 'Test',
      'solution' => 'Test',
      'status' => TRUE,
    ])->save();
    // Go to riddle edit page:
    $this->drupalGet('/admin/config/people/captcha/riddler-riddle/test');
    $session->statusCodeEquals(200);
    $session->pageTextContains('Edit a riddle');
    // Check if all fields exist:
    $session->elementExists('css', '#edit-question');
    $session->elementExists('css', '#edit-solution');
    $session->elementExists('css', '#edit-status');
    // See if the fields have the correct values:
    $session->elementAttributeContains('css', '#edit-question', 'value', 'Test');
    $session->elementAttributeContains('css', '#edit-solution', 'value', 'Test');
    $session->checkboxChecked('edit-status');
    // Change the riddle:
    $page->fillField('edit-question', 'Test Question?');
    $page->fillField('edit-solution', 'Test Solution!');
    $page->checkField('edit-status');
    $page->pressButton('edit-submit');
    // See if the riddle got changed:
    $session->statusCodeEquals(200);
    $session->pageTextContains('Updated CAPTCHA Riddler question: "Test Question?".');
    // See if the changed values appear in the table:
    $session->elementTextEquals('css', 'body > div > div > main > div > div > table > tbody > tr:nth-child(2) > td:nth-child(1)', 'Test Question?');
    $session->elementTextEquals('css', 'body > div > div > main > div > div > table > tbody > tr:nth-child(2) > td:nth-child(2)', 'Test Solution!');
  }

  /**
   * Method for testing to edit a riddle via UI.
   */
  public function testDeleteRiddle() {
    $session = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->riddleStorage->create([
      'id' => 'test',
      'question' => 'Test Question?',
      'solution' => 'Test Answer!',
      'status' => TRUE,
    ])->save();
    // Go to riddle delete page:
    $this->drupalGet('/admin/config/people/captcha/riddler-riddle/test/delete');
    $session->statusCodeEquals(200);
    $session->pageTextContains('Are you sure you want to delete the riddle test?');
    // Delete the riddle:
    $page->pressButton('edit-submit');
    // See if the riddle got delted:
    $session->statusCodeEquals(200);
    $session->pageTextContains('The riddle test has been deleted.');
    $session->pageTextNotContains('Test Question?');
    $session->pageTextNotContains('Test Answer!');
  }

  /**
   * Testing the protection of the user log in form using riddler.
   */
  public function testRiddlerOnLoginForm() {
    $session = $this->assertSession();
    // Create user and test log in without CAPTCHA:
    $this->drupalLogout();
    $user = $this->drupalCreateUser();
    $this->drupalLogin($user);
    // Log out again.
    $this->drupalLogout();

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
    $session->pageTextContains('Do you really hate Spam?');

    // Try to log in, which should fail:
    $edit = [
      'name' => $user->getDisplayName(),
      'pass' => $user->pass_raw,
      'captcha_response' => '?',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');
    // Check for error message.
    $session->pageTextContains('The answer you entered for the CAPTCHA was not correct.');

    // Check that the user really hasn't logged in:
    $this->drupalGet('user');
    $session->fieldExists('name');
    $session->fieldExists('pass');

    // Try to log in with correct answer:
    $edit = [
      'name' => $user->getDisplayName(),
      'pass' => $user->pass_raw,
      'captcha_response' => 'Yes',
    ];
    $this->submitForm($edit, 'Log in', 'user-login-form');
    $this->drupalGet('user');

    // Check that the user is logged in now:
    $this->drupalGet('user');
    $session->fieldNotExists('name');
    $session->fieldNotExists('pass');
    $session->pageTextContains($user->getDisplayName());
  }

}
