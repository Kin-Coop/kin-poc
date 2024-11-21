<?php

namespace Drupal\opencollective_funding\Plugin\Funding\Provider;

use Drupal\funding\Exception\InvalidFundingProviderData;
use Drupal\opencollective_funding\Plugin\Funding\FundingProviderBase;

/**
 * Plugin implementation of the funding_provider.
 *
 * @FundingProvider(
 *   id = "open_collective_button",
 *   label = @Translation("Open Collective - Button"),
 *   description = @Translation("Handles processing for the open_collective_button funding namespace."),
 *   enabledByDefault = TRUE,
 * )
 */
class Button extends FundingProviderBase {

  /**
   * {@inheritdoc}
   */
  public function examples(): array {
    return [
      'open_collective_button: funding-tools',
      'open_collective_button:
         collective: funding-tools
         color: blue
         verb: contribute',
      'open_collective_button:
         collective: funding-tools
         color: white
         verb: donate',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validate($data): bool {
    parent::validate($data);

    if (is_array($data)) {
      $this->validateOptionalPropertyIsString($data, 'color');
      $this->validateOptionalPropertyIsString($data, 'verb');

      if (isset($data['color'])) {
        if (!$this->openCollectiveParameters->keyExists($data['color'], $this->openCollectiveParameters->getParameterOptions()->embedButtonColors())) {
          throw new InvalidFundingProviderData("{$data['color']} is not a valid button color. Valid button colors are: " . implode(', ', array_keys($this->openCollectiveParameters->getParameterOptions()->embedButtonColors())));
        }
      }

      if (isset($data['verb'])) {
        if (!$this->openCollectiveParameters->keyExists($data['verb'], $this->openCollectiveParameters->getParameterOptions()->embedButtonVerbs())) {
          throw new InvalidFundingProviderData("{$data['verb']} is not a valid button verb. Valid button verbs are: " . implode(', ', array_keys($this->openCollectiveParameters->getParameterOptions()->embedButtonVerbs())));
        }
      }
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function build($data): array {
    if (is_string($data)) {
      return [
        '#theme' => 'opencollective_button',
        '#collective' => $data,
      ];
    }

    if (is_array($data)) {
      return [
        '#theme' => 'opencollective_button',
        '#collective' => $data['collective'],
        '#color' => $data['color'] ?? 'blue',
        '#verb' => $data['verb'] ?? 'contribute',
      ];
    }

    return [];
  }

}
