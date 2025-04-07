<?php

use Civi\Api4\Example3;
use Civi\Test\HeadlessInterface;

/**
 * Unit test for the Example entity
 * @group headless
 */
class CRM_Api4_ExampleTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface {

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp(): void {
    // Delete any existing example data before running test
    Example::delete(FALSE)->addWhere('id', '>', 0)->execute();
    parent::setUp();
  }

  /**
   * Test creating, getting, updating and deleting an example record
   */
  public function testCrud() {
    // Create an example record
    Example::create()
      ->addValue('example_str', 'foo')
      ->addValue('example_int', 42)
      ->addValue('example_bool', FALSE)
      ->addValue('example_options', ['r', 'g'])
      ->execute();

    // Get the record we just created
    $result = Example::get()
      ->execute()->single();

    // Assert that the values are the ones we put in
    $this->assertEquals('foo', $result['example_str']);
    $this->assertEquals(42, $result['example_int']);
    $this->assertEquals(FALSE, $result['example_bool']);
    $this->assertEquals(['r', 'g'], $result['example_options']);

    // Update record with a new value for 'example_options'
    Example::update()
      ->addWhere('id', '=', $result['id'])
      // See https://docs.civicrm.org/dev/en/latest/api/v4/pseudoconstants/
      ->addValue('example_options:label', ['Blue', 'Green'])
      ->execute();

    $result = Example::get()
      ->addSelect('*', 'example_options:label')
      ->execute()->single();

    // Value updated
    $this->assertEquals(['b', 'g'], $result['example_options']);
    // We also selected the labels to be returned
    $this->assertEquals(['Blue', 'Green'], $result['example_options:label']);

    // Ensure that updating a single value doesn't change other values
    $this->assertEquals('foo', $result['example_str']);
    $this->assertEquals(42, $result['example_int']);
    $this->assertEquals(FALSE, $result['example_bool']);

    Example::delete()
      ->addWhere('id', '=', $result['id'])
      ->execute();

    $result = Example::get()
      ->execute();

    // Ensure the record is gone after delete
    $this->assertCount(0, $result);
  }

}
