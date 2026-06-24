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

namespace Civi\Api4\Action\StripeWebhook;

use Civi\Api4\Generic\BasicCreateAction;
use Civi\Api4\Generic\Result;
use Civi\Api4\PaymentProcessor;
use Civi\Api4\StripeWebhook;

/**
 * @inheritDoc
 */
class Update extends BasicCreateAction {

  /**
   * The CiviCRM Payment Processor ID
   *
   * @var int
   */
  protected int $paymentProcessorID;

  /**
   * @param \Civi\Api4\Generic\Result $result
   *
   * @return void
   * @throws \CRM_Core_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   * @throws \Stripe\Exception\ApiErrorException
   */
  public function _run(Result $result) {
    $processor = \Civi\Payment\System::singleton()->getById($this->paymentProcessorID);

    if (!($processor instanceof \CRM_Core_Payment_Stripe)) {
      throw new \CRM_Core_Exception('Not a Stripe Payment Processor.');
    }
    if ($processor->stripeClient === NULL) {
      // This means we only configured live OR test and not both.
      throw new \CRM_Core_Exception('Payment Processor is not configured.');
    }

    $webhooks = StripeWebhook::getFromStripe(FALSE)
      ->setPaymentProcessorID($this->paymentProcessorID)
      ->execute();

    foreach ($webhooks as $webhook) {
      if (str_starts_with($webhook->url, \CRM_Mjwshared_Webhook::getWebhookPath($this->paymentProcessorID))) {
        // This webhook is for this payment processor
        if (($webhook->api_version === \Civi\Stripe\Check::API_VERSION)
          && ($webhook->status === 'enabled')
          && \CRM_Stripe_Webhook::checkEnabledWebhookEvents($webhook)) {
          // This is a valid, enabled webhook with the current API version and the correct set of enabled events
          $webhooksWithCurrentAPIVersion[] = $webhook;
        }
        elseif ($webhook->status !== 'disabled') {
          // This is an enabled webhook for our paymentprocessor, but API version does not match current version
          //   so we need to disable it.
          $webhooksToDisable[] = $webhook;
        }
      }
    }

    if (empty($webhooksWithCurrentAPIVersion)) {
      // Need to create a new webhook
      $currentWebhook = StripeWebhook::create(FALSE)
        ->setPaymentProcessorID($this->paymentProcessorID)
        ->setDisabled(TRUE)
        ->execute();
    }
    else {
      $currentWebhook = array_pop($webhooksWithCurrentAPIVersion);
      if (!empty($webhooksWithCurrentAPIVersion)) {
        \Civi::log('stripe')->warning('You have more than one Stripe webhook enabled for PaymentProcessorID: ' . $this->paymentProcessorID . '. All except the last one will be disabled.');
        $webhooksToDisable = array_merge($webhooksToDisable ?? [], $webhooksWithCurrentAPIVersion);
      }
      $currentWebhook = $currentWebhook->toArray();
    }
    if (!empty($webhooksToDisable)) {
      foreach ($webhooksToDisable as $webhook) {
        $processor->stripeClient->webhookEndpoints->update($webhook->id, ['disabled' => TRUE]);
      }
    }

    if ($currentWebhook['status'] === 'disabled') {
      $updatedWebhook = $processor->stripeClient->webhookEndpoints->update($currentWebhook['id'], ['disabled' => FALSE]);
      $currentWebhook['status'] = $updatedWebhook->status;
    }

    if (isset($currentWebhook['secret'])) {
      // Update the webhook secret on the PaymentProcessor in CiviCRM
      // The secret will only be provided when the webhook is first created
      PaymentProcessor::update(FALSE)
        ->addValue('signature', $currentWebhook['secret'])
        ->addWhere('id', '=', $this->paymentProcessorID)
        ->execute();
    }

    unset($currentWebhook['secret']);

    $result->exchangeArray($currentWebhook);
  }

}
