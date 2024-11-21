<?php

namespace Drupal\opencollective_openid_connect\Plugin\OpenIdConnectClient;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\opencollective_api\Service\ApiClientFactory;
use Drupal\openid_connect\OpenIDConnectAutoDiscover;
use Drupal\openid_connect\OpenIDConnectStateTokenInterface;
use Drupal\openid_connect\Plugin\OpenIDConnectClientBase;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Open Collective OpenID Connect client.
 *
 * Used primarily to login to Drupal sites powered by opencollective.com.
 *
 * @OpenIDConnectClient(
 *   id = "opencollective",
 *   label = @Translation("Open Collective")
 * )
 */
class OpenIDConnectOpenCollective extends OpenIDConnectClientBase {

  /**
   * Open Collective api client factory.
   *
   * @var \Drupal\opencollective_api\Service\ApiClientFactory
   */
  private ApiClientFactory $apiClientFactory;

  /**
   * The constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin identifier.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The http client.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Component\Datetime\TimeInterface $datetime_time
   *   The datetime.time service.
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $page_cache_kill_switch
   *   Policy evaluating to static::DENY when the kill switch was triggered.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\openid_connect\OpenIDConnectStateTokenInterface $state_token
   *   The OpenID state token service.
   * @param \Drupal\openid_connect\OpenIDConnectAutoDiscover $auto_discover
   *   The OpenID well-known discovery service.
   * @param \Drupal\opencollective_api\Service\ApiClientFactory $apiClientFactory
   *   Open Collective api client factory.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    $plugin_definition,
    RequestStack $request_stack,
    ClientInterface $http_client,
    LoggerChannelFactoryInterface $logger_factory,
    TimeInterface $datetime_time,
    KillSwitch $page_cache_kill_switch,
    LanguageManagerInterface $language_manager,
    OpenIDConnectStateTokenInterface $state_token,
    OpenIDConnectAutoDiscover $auto_discover,
    ApiClientFactory $apiClientFactory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $request_stack, $http_client, $logger_factory, $datetime_time, $page_cache_kill_switch, $language_manager, $state_token, $auto_discover);

    $this->apiClientFactory = $apiClientFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack'),
      $container->get('http_client'),
      $container->get('logger.factory'),
      $container->get('datetime.time'),
      $container->get('page_cache_kill_switch'),
      $container->get('language_manager'),
      $container->get('openid_connect.state_token'),
      $container->get('openid_connect.autodiscover'),
      $container->get('opencollective_api.client_factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getClientScopes(): ?array {
    return ['email'];
  }

  /**
   * {@inheritdoc}
   */
  public function getEndpoints(): array {
    return [
      'authorization' => 'https://opencollective.com/oauth/authorize',
      'token' => 'https://opencollective.com/oauth/token',
      // Uses GraphQL API for userinfo.
      'userinfo' => NULL,
      'end_session' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function usesUserInfo(): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveUserInfo(string $access_token): ?array {
    $apiClient = $this->apiClientFactory->createBearerClient($access_token);
    $results = $apiClient->request('{ me { description email id legalName longDescription name slug tags type } }');
    $user_info = $results['data']['me'] ?? [];

    if (isset($user_info['id'])) {
      $user_info['sub'] = $user_info['id'];
    }

    return $user_info;
  }

}
