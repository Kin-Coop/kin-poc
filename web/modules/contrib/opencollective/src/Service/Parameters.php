<?php

namespace Drupal\opencollective\Service;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Handles parameters used for open collective embeds.
 */
class Parameters {

  use StringTranslationTrait;

  /**
   * Parameter options.
   *
   * @var \Drupal\opencollective\Service\ParameterOptions
   */
  private ParameterOptions $parameterOptions;

  /**
   * Construct.
   *
   * @param \Drupal\opencollective\Service\ParameterOptions $parameterOptions
   *   Parameter options service.
   */
  public function __construct(ParameterOptions $parameterOptions) {
    $this->parameterOptions = $parameterOptions;
  }

  /**
   * Get the ParameterOptions service.
   *
   * @return \Drupal\opencollective\Service\ParameterOptions
   *   ParameterOptions service.
   */
  public function getParameterOptions(): ParameterOptions {
    return $this->parameterOptions;
  }

  /**
   * Check if the given key exists in the given array.
   *
   * @param string $key
   *   Key to search for.
   * @param array $enum
   *   Array of enum values.
   *
   * @return bool
   *   True if key exists, otherwise false.
   */
  public function keyExists(string $key, array $enum): bool {
    $keys = array_map('strtolower', array_keys($enum));
    return in_array(strtolower($key), $keys);
  }

  /**
   * Get the default value from the given array (first key).
   *
   * @param array $array
   *   Array to get value from.
   *
   * @return string
   *   Default value.
   */
  public function getDefault(array $array): string {
    return array_keys($array)[0];
  }

  /**
   * Get all parameter defaults.
   *
   * @param array $parameters
   *   Parameters.
   *
   * @return array
   *   Default values, key by parameter name.
   */
  public function getParametersDefaults(array $parameters): array {
    return array_combine(array_keys($parameters), array_column($parameters, 'default'));
  }

  /**
   * Get parameters used by contributors image.
   *
   * @return array[]
   *   Parameters used by contributors image.
   */
  public function contributorsImageUrlParameters(): array {
    return [
      'width' => [
        'name' => 'width',
        'type' => 'integer',
        'label' => $this->t('Width'),
        'description' => $this->t('Width of the resulting image.'),
        'default' => NULL,
      ],
      'height' => [
        'name' => 'height',
        'type' => 'integer',
        'label' => $this->t('Height'),
        'description' => $this->t('Height of the resulting image.'),
        'default' => NULL,
      ],
      'limit' => [
        'name' => 'limit',
        'type' => 'integer',
        'label' => $this->t('Limit'),
        'description' => $this->t('Number of members to show.'),
        'default' => NULL,
      ],
      'avatarHeight' => [
        'name' => 'avatarHeight',
        'type' => 'integer',
        'label' => $this->t('Avatar Height'),
        'description' => $this->t('Max height of each avatar or logo.'),
        'default' => NULL,
      ],
      'button' => [
        'name' => 'button',
        'type' => 'boolean',
        'label' => $this->t('Show Button'),
        'description' => $this->t('Whether or not to show the "Become a backer/sponsor" button.'),
        'default' => TRUE,
      ],
      'format' => [
        'name' => 'format',
        'type' => 'string',
        'label' => $this->t('Image Format'),
        'description' => $this->t('Select the format for the image that will be produced.'),
        'default' => 'png',
        'options' => [
          'jpg' => 'jpg',
          'png' => 'png',
          'svg' => 'svg',
        ],
      ],
    ];
  }

  /**
   * Array of url parameter configuration from OC documentation.
   *
   * @link https://docs.opencollective.com/help/collectives/contribution-flow#url-parameters
   *
   * @return array[]
   *   Array of url parameter.
   */
  public function contributionFlowUrlParameters(): array {
    return [
      'amount' => [
        'name' => 'amount',
        'type' => 'decimal',
        'label' => $this->t('Amount'),
        'description' => $this->t('Default contribution amount.'),
        'default' => NULL,
      ],
      'quantity' => [
        'name' => 'quantity',
        'type' => 'integer',
        'label' => $this->t('Quantity'),
        'description' => $this->t('Default number of units (for products and tickets only).'),
        'default' => 1,
      ],
      'interval' => [
        'name' => 'interval',
        'type' => 'string',
        'label' => $this->t('Contribution Interval'),
        'description' => $this->t('The contribution interval (must be supported by the selected tier, if any).'),
        'default' => NULL,
        'options' => $this->parameterOptions->tierIntervals(),
      ],
      'paymentMethod' => [
        'name' => 'paymentMethod',
        'type' => 'string',
        'label' => $this->t('Payment Method'),
        'description' => $this->t('ID of the payment method to use. Will fallback to another payment method if not available.'),
        'default' => NULL,
      ],
      'contributeAs' => [
        'name' => 'contributeAs',
        'type' => 'string',
        'label' => $this->t('Contribute As'),
        'description' => $this->t('Slug of the default profile to use to contribute'),
        'default' => NULL,
      ],
      'email' => [
        'name' => 'email',
        'type' => 'string',
        'label' => $this->t('Email'),
        'description' => $this->t('Guest contributions only: The email to use to contribute'),
        'default' => NULL,
      ],
      'name' => [
        'name' => 'name',
        'type' => 'string',
        'label' => $this->t('name'),
        'description' => $this->t('Guest contributions only: The name to use to contribute'),
        'default' => NULL,
      ],
      'legalName' => [
        'name' => 'legalName',
        'type' => 'string',
        'label' => $this->t('Legal Name'),
        'description' => $this->t('Guest contributions only: The legal name to use to contribute'),
        'default' => NULL,
      ],
      'disabledPaymentMethodTypes' => [
        'name' => 'disabledPaymentMethodTypes',
        'type' => 'array',
        'label' => $this->t('Disable PaymentMethod Types'),
        'description' => $this->t('Disable specific payment method types.'),
        'notes' => 'comma-separated list',
        'default' => [],
        'options' => $this->parameterOptions->paymentMethodTypes(),
      ],
      'redirect' => [
        'name' => 'redirect',
        'type' => 'string',
        'label' => $this->t('Redirect URL'),
        'description' => $this->t('The URL to redirect to after a successful contribution.'),
        'default' => NULL,
      ],
      'tags' => [
        'name' => 'tags',
        'type' => 'string',
        'label' => $this->t('Tags'),
        'description' => $this->t('Comma-separated list of tags to attach to the contribution.'),
        'default' => NULL,
      ],
      'hideSteps' => [
        'name' => 'hideSteps',
        'type' => 'boolean',
        'label' => $this->t('Hide Steps'),
        'description' => $this->t('To hide the steps on top. Will also hide the "previous" button on step payment.'),
        'default' => FALSE,
      ],
      'cryptoCurrency' => [
        'name' => 'cryptoCurrency',
        'type' => 'string',
        'label' => $this->t('cryptoCurrency'),
        'description' => $this->t('Cryptocurrency type; BTC, ETH etc.'),
        'default' => NULL,
        'options' => $this->parameterOptions->cryptoCurrencyTypes(),
      ],
      'cryptoAmount' => [
        'name' => 'cryptoAmount',
        'type' => 'float',
        'label' => $this->t('Crypto Amount'),
        'description' => $this->t('Cryptocurrency amount.'),
        'default' => NULL,
      ],
      'hideFAQ' => [
        'name' => 'hideFAQ',
        'type' => 'boolean',
        'label' => $this->t('Hide FAQ'),
        'description' => $this->t('Embed only: Whether we need to hide the right-column FAQ.'),
        'default' => FALSE,
      ],
      'hideHeader' => [
        'name' => 'hideHeader',
        'type' => 'boolean',
        'label' => $this->t('Hide Header'),
        'description' => $this->t('Embed only: Whether we need to hide the contribution flow header.'),
        'default' => FALSE,
      ],
      'backgroundColor' => [
        'name' => 'backgroundColor',
        'type' => 'string',
        'label' => $this->t('backgroundColor'),
        'description' => $this->t('Embed only: A custom hexcode color to use as the background color of the contribution flow.'),
        'default' => NULL,
      ],
      'useTheme' => [
        'name' => 'useTheme',
        'type' => 'boolean',
        'label' => $this->t('Use Theme'),
        'description' => $this->t('Embed only: Whether to use the collective theme (custom colors).'),
        'default' => FALSE,
      ],
      'shouldRedirectParent' => [
        'name' => 'shouldRedirectParent',
        'type' => 'boolean',
        'label' => $this->t('shouldRedirectParent'),
        'description' => $this->t('Embed only: Whether to redirect the parent of the iframe rather than the iframe itself. The iframe needs to have attribute sandbox="allow-top-navigation" for this to work.'),
        'default' => FALSE,
      ],
    ];
  }

}
