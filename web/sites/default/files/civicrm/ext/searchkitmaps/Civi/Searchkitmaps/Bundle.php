<?php
/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

namespace Civi\Searchkitmaps;

use Civi;
use Civi\Core\Event\GenericHookEvent;
use CRM_Core_Resources_Bundle;
use CRM_Searchkitmaps_ExtensionUtil as E;
use CRM_Utils_Hook;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This class defines the `searchkitmaps.js` assets which puts the services
 * in the `CRM.searchkitmaps` namespace.
 *
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 * @service bundle.searchkitmaps
 */
class Bundle extends CRM_Core_Resources_Bundle implements EventSubscriberInterface, Civi\Core\Service\AutoServiceInterface {

  use Civi\Core\Service\AutoServiceTrait;

  const NAME = 'searchkitmaps';
  const JS_FILE = 'searchkitmaps.js';
  const CSS_FILE = 'searchkitmaps.css';

  protected bool $initialized = FALSE;

  public static function getSubscribedEvents(): array {
    return [
      'hook_civicrm_buildAsset' => [['buildAssetJs', 0], ['buildAssetCss', 0]],
    ];
  }

  public function __construct() {
    parent::__construct(static::NAME,
      ['script', 'scriptFile', 'scriptUrl', 'settings', 'style', 'styleFile', 'styleUrl', 'markup']);
  }

  public function getAll(): iterable {
    if (!$this->initialized) {
      $this->addScriptUrl(Civi::service('asset_builder')->getUrl(static::JS_FILE));
      $this->addStyleUrl(Civi::service('asset_builder')->getUrl(static::CSS_FILE));

      CRM_Utils_Hook::alterBundle($this);
      $this->fillDefaults();
    }
    return parent::getAll();
  }

  /**
   * Generate asset content (when accessed via AssetBuilder).
   *
   * @param \Civi\Core\Event\GenericHookEvent $event
   *
   * @see CRM_Utils_hook::buildAsset()
   * @see \Civi\Core\AssetBuilder
   */
  public static function buildAssetJs(GenericHookEvent $event): void {
    if ($event->asset !== static::JS_FILE) {
      return;
    }

    $files = [
      'leaflet' => 'js/leaflet.js',
      'markercluster' => 'js/leaflet.markercluster.js',
      'extramarkers' => 'js/leaflet.extra-markers.js',
    ];

    $content = [];
    $content[] = "(function(){";
    $content[] = "var backups = {leaflet: window.leaflet, markercluster: window.markercluster}";
    $content[] = 'window.CRM = window.CRM || {};';
    $content[] = 'CRM.searchkitmaps = CRM.searchkitmaps || {};';
    foreach ($files as $file) {
      $content[] = file_get_contents(E::path($file));
    }
    $content[] = "CRM.searchkitmaps.leaflet = leaflet;";
    foreach ($files as $var => $file) {
      $content[] = "window.$var = backups.$var;";
    }
    $content[] = "})();";

    $event->mimeType = 'application/javascript';
    $event->content = implode("\n", $content);
  }

  /**
   * Generate asset content (when accessed via AssetBuilder).
   *
   * @param \Civi\Core\Event\GenericHookEvent $event
   *
   * @see CRM_Utils_hook::buildAsset()
   * @see \Civi\Core\AssetBuilder
   */
  public static function buildAssetCss(GenericHookEvent $event): void {
    if ($event->asset !== static::CSS_FILE) {
      return;
    }

    $files = [
      'css/leaflet.css',
      'css/MarkerCluster.css',
      'css/MarkerCluster.Default.css',
      'css/leaflet.extra-markers.min.css',
    ];

    $content = [];
    foreach ($files as $file) {
      $content[] = file_get_contents(E::path($file));
    }

    $event->mimeType = 'text/css';
    $event->content = implode("\n", $content);
  }

}
