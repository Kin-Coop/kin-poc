<?php

declare(strict_types = 1);

namespace Drupal\watchdog_search;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorableConfigBase;
use Drupal\Core\Config\StorageInterface;

/**
 * Overrides the watchdog view configuration to disable it.
 */
class ConfigOverride implements ConfigFactoryOverrideInterface {

  /**
   * The watchdog view config file.
   */
  protected const WATCHDOG_VIEW_CONFIG = 'views.view.watchdog';

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names): array {
    $overrides = [];
    if (in_array(self::WATCHDOG_VIEW_CONFIG, $names, TRUE)) {
      $overrides[self::WATCHDOG_VIEW_CONFIG]['status'] = FALSE;
    }
    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix(): string {
    return 'watchdog_search_config_override';
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION): ?StorableConfigBase {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name): CacheableMetadata {
    return new CacheableMetadata();
  }

}
