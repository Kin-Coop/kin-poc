<?php

namespace Drupal\opencollective_api\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * ApiQuery plugin manager.
 */
class ApiQueryPluginManager extends DefaultPluginManager {

  /**
   * Constructs ApiQueryPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/OpenCollective/ApiQuery',
      $namespaces,
      $module_handler,
      'Drupal\opencollective_api\ApiQueryInterface',
      'Drupal\opencollective_api\Annotation\OpenCollectiveApiQuery'
    );
    $this->alterInfo('opencollective_api_query_info');
    $this->setCacheBackend($cache_backend, 'opencollective_api_query_plugins');
  }

  /**
   * Create an instance of a plugin.
   *
   * @param string $plugin_id
   *   The id of the setup plugin.
   * @param array $configuration
   *   Configuration data for the setup plugin.
   *
   * @return \Drupal\opencollective_api\ApiQueryInterface
   *   Instance of the plugin.
   */
  public function createInstance($plugin_id, array $configuration = []) {
    return $this->getFactory()->createInstance($plugin_id, $configuration);
  }

}
