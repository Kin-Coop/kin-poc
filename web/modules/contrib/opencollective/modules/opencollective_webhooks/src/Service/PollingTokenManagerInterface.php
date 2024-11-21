<?php

namespace Drupal\opencollective_webhooks\Service;

/**
 * Responsible for generated and validating polling access tokens.
 */
interface PollingTokenManagerInterface {

  /**
   * Generate and store a new polling access token.
   *
   * @return string
   *   Newly generated access token.
   *
   * @throws \Exception
   */
  public function generateNewToken(): string;

  /**
   * Determine if given access token is valid.
   *
   * @param string $access_token
   *   Access token.
   *
   * @return bool
   *   True if valid (not expired), otherwise false.
   */
  public function isTokenValid(string $access_token): bool;

}
