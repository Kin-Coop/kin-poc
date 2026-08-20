<?php
/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

/**
 * @group headless
 */
require_once('TestBase.php');
class CRM_Stripe_ApiTest extends CRM_Stripe_TestBase {

  public function setUp(): void {
    parent::setUp();
  }

  public function tearDown(): void {
    parent::tearDown();
  }

  /**
   * getDetailsFromBalanceTransactionByChargeID('') must return [] without
   * making a Stripe API call - an empty charge ID means there's nothing to
   * look up (eg. the charge_id was never recovered from Stripe), and
   * calling charges->retrieve('') would just error. Pass NULL for the
   * payment processor: if the guard is removed, this test errors instead of
   * silently passing, because the method would try to call a method on NULL.
   */
  public function testGetDetailsFromBalanceTransactionByChargeIDReturnsEmptyForEmptyChargeID() {
    $api = new \Civi\Stripe\Api(NULL);
    $this->assertEquals([], $api->getDetailsFromBalanceTransactionByChargeID(''));
  }

}
