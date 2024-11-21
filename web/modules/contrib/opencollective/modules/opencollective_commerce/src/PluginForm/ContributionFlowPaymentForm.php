<?php

namespace Drupal\opencollective_commerce\PluginForm;

use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Renders Open Collective Contribution Flow for payment during checkout.
 */
class ContributionFlowPaymentForm extends PaymentOffsiteForm implements ContainerInjectionInterface {

  /**
   * Construct.
   */
  public function __construct() {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    /**
     * @var \Drupal\commerce_payment\Entity\PaymentInterface $payment
     * @var \Drupal\commerce_payment\Entity\PaymentGatewayInterface $payment_gateway
     * @var \Drupal\opencollective_commerce\Plugin\Commerce\PaymentGateway\ContributionFlowPaymentGateway $payment_gateway_plugin
     */
    $payment = $this->entity;
    $payment_gateway = $payment->getPaymentGateway();
    $payment_gateway_plugin = $payment_gateway->getPlugin();
    $order = $payment->getOrder();
    $billing = $order->getBillingProfile();
    $names = [
      $billing?->address?->given_name,
      $billing?->address?->family_name,
    ];
    $name = implode(' ', array_filter($names));
    $collective = $order->getStore()->field_collective_slug->value;

    $form['contribution_flows'] = [
      '#type' => 'container',
    ];
    foreach ($order->getItems() as $orderItem) {
      $variant = $orderItem->getPurchasedEntity();
      $product = $variant->product_id->entity;
      $event = $product->field_event_slug?->value;
      $tier = $variant?->sku->value;

      $form['contribution_flows']["{$collective}_{$event}_{$tier}"] = [
        '#theme' => 'opencollective_contribution_flow',
        '#collective' => $collective,
        '#event' => $event,
        '#tier' => $tier,
        '#query' => [
          'shouldRedirectParent' => TRUE,
          'redirect' => $payment_gateway_plugin->getReturnUrl($order),
          'amount' => $orderItem->getTotalPrice()->getNumber(),
          'email' => $order->getEmail(),
          'hideFAQ' => TRUE,
          //'hideHeader' => TRUE,
          'name' => $name,
          'legalName' => $name,
          'interval' => 'oneTime',
          'quantity' => (int) $orderItem->getQuantity(),
          'useTheme' => TRUE,
        ],
      ];

      // @todo - Make this better.
      // We can only buy 1 product at a time.
      break;
    }

    return $form;
  }

}
