<?php

namespace Drupal\opencollective_api_funding\Plugin\Funding\Provider;

use Drupal\funding\Exception\InvalidFundingProviderData;
use Drupal\opencollective_api_funding\Plugin\Funding\ApiFundingProviderBase;

/**
 * Plugin implementation of the funding_provider.
 *
 * @FundingProvider(
 *   id = "open_collective_members",
 *   label = @Translation("Open Collective - Members"),
 *   description = @Translation("Handles processing for the open_collective_members funding namespace."),
 *   enabledByDefault = FALSE,
 * )
 */
class ApiMembers extends ApiFundingProviderBase {

  /**
   * {@inheritdoc}
   */
  public function examples(): array {
    return [
      'open_collective_members: funding-tools',
      'open_collective_members:
         collective: funding-tools
         members_role: backer
         members_limit: 20',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validate($data): bool {
    parent::validate($data);

    if (is_array($data)) {
      $this->validateOptionalPropertyIsString($data, 'members_role');
      $this->validateOptionalPropertyIsInteger($data, 'members_limit');

      if (isset($data['members_role'])) {
        if (!$this->openCollectiveParameters->keyExists($data['members_role'], $this->openCollectiveParameters->getParameterOptions()->memberRoles())) {
          throw new InvalidFundingProviderData("{$data['members_role']} is not a valid Open Collective member role. Valid members_role values: " . implode(', ', array_keys($this->openCollectiveParameters->getParameterOptions()->memberRoles())));
        }
      }
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isReady(): bool {
    return $this->apiClient->isReady();
  }

  /**
   * {@inheritdoc}
   */
  public function build($data): array {
    if (is_string($data)) {
      return [
        '#theme' => 'opencollective_api_members',
        '#collective' => $data,
      ];
    }

    if (is_array($data)) {
      return [
        '#theme' => 'opencollective_api_members',
        '#collective' => $data['collective'],
        '#members_role' => $data['members_role'] ?? NULL,
        '#members_limit' => isset($data['members_limit']) ? (int) $data['members_limit'] : 20,
        // Static member properties for now.
        '#member_properties' => 'id slug name imageUrl',
      ];
    }

    return [];
  }

}
