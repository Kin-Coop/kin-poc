<?php

namespace Drupal\opencollective_funding\Plugin\Funding\Provider;

use Drupal\funding\Exception\InvalidFundingProviderData;
use Drupal\opencollective_funding\Plugin\Funding\FundingProviderBase;

/**
 * Plugin implementation of the funding_provider.
 *
 * @FundingProvider(
 *   id = "open_collective_contribution_flow",
 *   label = @Translation("Open Collective - Contribution Flow"),
 *   description = @Translation("Handles processing for the open_collective_contribution_flow funding namespace."),
 *   enabledByDefault = FALSE
 * )
 */
class ContributionFlow extends FundingProviderBase {

  /**
   * {@inheritdoc}
   */
  public function examples(): array {
    return [
      'open_collective_contribution_flow: funding-tools',
      'open_collective_contribution_flow:
         collective: funding-tools
         tier: backer-14068',
      'open_collective_contribution_flow:
         collective: funding-tools
         query:
           amount: 5
           quantity: 1
           redirect: "https://drupal.org/project/opencollective"',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validate($data): bool {
    parent::validate($data);

    if (is_array($data)) {
      $this->validateOptionalPropertyIsString($data, 'tier');
      $this->validateOptionalPropertyIsArray($data, 'query');

      if (isset($data['query'])) {
        $parameters = $this->openCollectiveParameters->contributionFlowUrlParameters();
        foreach ($data['query'] as $key => $value) {
          if (!$this->openCollectiveParameters->keyExists($key, $parameters)) {
            throw new InvalidFundingProviderData("Contribution flow query key '{$key}' is not a valid URL parameter.");
          }
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
        '#theme' => 'opencollective_contribution_flow',
        '#collective' => $data,
      ];
    }

    if (is_array($data)) {
      return [
        '#theme' => 'opencollective_contribution_flow',
        '#collective' => $data['collective'],
        '#tier' => $data['tier'] ?? NULL,
        '#query' => $data['query'] ?? [],
      ];
    }

    return [];
  }

}
