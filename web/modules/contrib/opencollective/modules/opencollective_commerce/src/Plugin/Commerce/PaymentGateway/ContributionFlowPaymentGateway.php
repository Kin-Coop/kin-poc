<?php

namespace Drupal\opencollective_commerce\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles the gateway configuration form and creation of payment onNotify().
 *
 * @CommercePaymentGateway(
 *   id = "opencollective_commerce_contribution_flow",
 *   label = "Open Collective - Contribution Flow",
 *   display_label = "Contribution Flow",
 *   forms = {
 *     "offsite-payment" = "\Drupal\opencollective_commerce\PluginForm\ContributionFlowPaymentForm",
 *   },
 *   requires_billing_information = TRUE,
 * )
 */
class ContributionFlowPaymentGateway extends OffsitePaymentGatewayBase {

  const PLUGIN_ID = 'opencollective_commerce_contribution_flow';

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    return $form;
  }

  /**
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *
   * @return string
   */
  public function getReturnUrl(OrderInterface $order): string {
    return Url::fromRoute('commerce_payment.checkout.return', [
      'commerce_order' => $order->id(),
      'step' => 'payment',
    ], ['absolute' => TRUE])->toString();
  }

  /**
   * {@inheritdoc}
   */
  public function onReturn(OrderInterface $order, Request $request) {
    $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');
    $payment = $payment_storage->create([
      'state' => 'completed',
      'amount' => $order->getBalance(),
      'payment_gateway' => $this->parentEntity->id(),
      'order_id' => $order->id(),
      'remote_id' => $request->query->get('orderId'),
      'remote_state' => $request->query->get('status'),
    ]);
    $payment->save();
  }

}
