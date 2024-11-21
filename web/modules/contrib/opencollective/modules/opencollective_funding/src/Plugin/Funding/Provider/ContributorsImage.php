<?php

namespace Drupal\opencollective_funding\Plugin\Funding\Provider;

use Drupal\opencollective_funding\Plugin\Funding\FundingProviderBase;

/**
 * Plugin implementation of the funding_provider.
 *
 * @FundingProvider(
 *   id = "open_collective_contributors_image",
 *   label = @Translation("Open Collective - Contributors Image"),
 *   description = @Translation("Handles processing for the open_collective_contributors_image funding namespace."),
 *   enabledByDefault = TRUE,
 * )
 */
class ContributorsImage extends FundingProviderBase {

  /**
   * {@inheritdoc}
   */
  public function examples(): array {
    return [
      'open_collective_contributors_image: funding-tools',

      'open_collective_contributors_image:
        collective: funding-tools
        members_role: backers
        query:
          label: Backers
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
      $this->validateOptionalPropertyIsArray($data, 'query');
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function build($data): array {
    if (is_string($data)) {
      return [
        '#theme' => 'opencollective_contributors_image',
        '#collective' => $data,
        '#members_role' => 'backers',
      ];
    }

    if (is_array($data)) {
      return [
        '#theme' => 'opencollective_contributors_image',
        '#collective' => $data['collective'],
        '#members_role' => $data['members_role'] ?? 'backers',
        '#query' => $data['query'] ?? [],
      ];
    }

    return [];
  }

}
