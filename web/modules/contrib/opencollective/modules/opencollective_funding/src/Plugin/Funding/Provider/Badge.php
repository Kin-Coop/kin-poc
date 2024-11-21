<?php

namespace Drupal\opencollective_funding\Plugin\Funding\Provider;

use Drupal\opencollective_funding\Plugin\Funding\FundingProviderBase;

/**
 * Plugin implementation of the funding_provider.
 *
 * @FundingProvider(
 *   id = "open_collective_badge",
 *   label = @Translation("Open Collective - Badge"),
 *   description = @Translation("Handles processing for the open_collective_badge funding namespace."),
 *   enabledByDefault = TRUE,
 * )
 */
class Badge extends FundingProviderBase {

  /**
   * {@inheritdoc}
   */
  public function examples(): array {
    return [
      'open_collective_badge: funding-tools',

      'open_collective_badge:
        collective: funding-tools
        members_role: backers
        label: Backers!
        color: red',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validate($data): bool {
    parent::validate($data);

    if (is_array($data)) {
      $this->validateOptionalPropertyIsString($data, 'members_role');
      $this->validateOptionalPropertyIsString($data, 'label');
      $this->validateOptionalPropertyIsString($data, 'color');
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function build($data): array {
    if (is_string($data)) {
      return [
        '#theme' => 'opencollective_badge',
        '#collective' => $data,
        '#members_role' => 'backers',
      ];
    }

    if (is_array($data)) {
      return [
        '#theme' => 'opencollective_badge',
        '#collective' => $data['collective'],
        '#members_role' => $data['members_role'] ?? 'backers',
        '#label' => $data['label'] ?? NULL,
        '#color' => $data['color'] ?? NULL,
      ];
    }

    return [];
  }

}
