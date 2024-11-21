<?php

namespace Drupal\opencollective_webhooks\Service;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\KeyValueStore\KeyValueExpirableFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreExpirableInterface;

/**
 * Default implementation of PollingTokenManagerInterface.
 */
class PollingTokenManager implements PollingTokenManagerInterface {

  /**
   * Key Value collection of expirable tokens.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreExpirableInterface
   */
  private KeyValueStoreExpirableInterface $accessTokens;

  /**
   * Module settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private ImmutableConfig $config;

  /**
   *
   */
  public function __construct(KeyValueExpirableFactoryInterface $keyValueExpirableFactory, ConfigFactoryInterface $configFactory) {
    $this->accessTokens = $keyValueExpirableFactory->get('opencollective.access_tokens');
    $this->config = $configFactory->get('opencollective_webhooks.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function generateNewToken(): string {
    $token = Crypt::hashBase64(random_bytes(16));
    $this->accessTokens->setWithExpireIfNotExists($token, $token, $this->config->get('poll_access_token_length') ?? 300);
    return $token;
  }

  /**
   * {@inheritdoc}
   */
  public function isTokenValid(string $access_token): bool {
    return (bool) $this->accessTokens->get($access_token);
  }

}
