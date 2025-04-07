<?php

namespace Civi\Api4\Action\Inlay;

/**
 * Provides way to update the .js bundles for each inlay.
 */

use CRM_Inlay_ExtensionUtil as E;
use Civi\Api4\Generic\Result;
use Civi\Inlay\Config as InlayConfig;
use Civi\Api4\Generic\Traits\DAOActionTrait;
use Civi;

/**
 * InlayType.get action
 *
 * @package Civi\Api4
 */
class CreateBundle extends \Civi\Api4\Generic\AbstractQueryAction {

  /**
   * Criteria for selecting items to update.
   *
   * @var array
   */
  protected $where = [];

  /**
   * @inheritDoc
   */
  public function _run(Result $result) {

    $recs = $this->getBatchRecords();
    $inlayConfig = InlayConfig::singleton();
    $settings = $inlayConfig->getSettings();

    $libraryCode = '';
    if ($settings['polyfill']) {
      // Only include polyfills if we want them.
      $libraryCode = '/*polyfill*/' . file_get_contents(E::path('dist/polyfills.js')) . '/* polyfill-end */';
    }
    $libraryCode .= file_get_contents(E::path('dist/inlay.js'));

    $common = ['inlayEndpoint' => $inlayConfig->getApiEndPoint()];


    foreach ($recs as $rec) {

      if (empty($rec['error'])) {
        // (I'm not clear when 'error' would be set?)
        if (($rec['status'] ?? 'off') !== 'off') {
          // If it's not deliberately off, try to load it.
          $inlay = \Civi\Inlay\Type::fromArray($rec);
          $updates = [];
          if ($inlay->getStatus() === 'broken') {
            $rec['error'] = "Inlay (config) is BROKEN.";
          }
          if ($inlay->getStatus() !== $rec['status']) {
            // Increase visibility of brokeness. It is the way to healing.
            // Also, note when we have healed.
            \Civi::log()->info("Inlay $rec[id] changed status to " . $inlay->getStatus());
            \Civi\Api4\Inlay::update(FALSE)
              ->setUpdateBundle(FALSE) /* avoid infinite loop... */
              ->addWhere('id', '=', $inlay->getID())
              ->addValue('status', $inlay->getStatus())
              ->execute();
          }
        }
        else {
          $rec['error'] = "Inlay is OFF.";
        }
      }

      if (!empty($rec['error'])) {
        $publicID = $rec['public_id'];
        $filename = Civi::paths()->getPath("[civicrm.files]/inlay-$publicID.js");
        file_put_contents($filename, "console.log('Inlay unavailable: $publicID');");
        $error = "Inlay $rec[id] of class $rec[class] cannot be built because: $rec[error]";
        \Civi::log()->notice($error);
        $result[] = ['id' => $rec['id'], 'public_id' => $rec['public_id'], 'name' => $rec['name'], 'error' => $error];
        continue;
      }

      $publicID = json_encode($inlay->getPublicID());
      $initData = json_encode($inlay->getInitData() + $common);

      // - The following Javascript updates or creates a global object called CiviCRMInlay.
      // - On that object's .inlays property is an object keyed by the public ID
      //   strings whose values are the init data for that Inlay.
      // - If our main app has already been loaded, we trigger a reboot. Otherwise the
      //   app will boot itself when it loads.
      $externalScript = $inlay->getExternalScript();
      $now = time() * 1000;
      $data = <<<JAVASCRIPT
        $libraryCode
        CiviCRMInlay.app.bundleInfo($now, $publicID);
        $externalScript
        ((i) => {
          i.inlays[$publicID] = $initData;
          i.app.bootWhenReady();
        })(window.CiviCRMInlay);
        JAVASCRIPT;

      // Get public storage.
      $publicID = $inlay->getPublicID();
      $filename = Civi::paths()->getPath("[civicrm.files]/inlay-$publicID.js");
      file_put_contents("$filename.tmp", $data);
      // Atomically (on linux based OSes at least) replace the original file,
      // preventing a partially written file from being read.
      rename("$filename.tmp", $filename);

      $url = $inlay->getBundleUrl();
      $result[] = ['id' => $inlay->getID(), 'public_id' => $inlay->getPublicID(), 'type' => $inlay->getTypeName(), 'name' => $inlay->getName(), 'javascript' => $url];
    }

    if (empty($this->where) && empty($this->limit) && empty($this->offset)) {
      // We know of every inlay.
      $valid = $result->column('public_id');
      $filenameStub = Civi::paths()->getPath("[civicrm.files]/inlay-");
      $foundFiles = glob($filenameStub . '*.js');
      foreach ($foundFiles as $file) {
        $foundPublidID = substr(substr($file, strlen($filenameStub)), 0, -3);
        if (!in_array($foundPublidID, $valid)) {
          $mtime = date('H:i j M Y', filemtime($file));
          if (preg_match('/^[0-9a-fA-F]{12}$/', $foundPublidID)) {
            unlink($file);
            \Civi::log()->warning("Deleted old inlay bundle for $foundPublidID\nat $file updated $mtime as it has no definition.");
          }
          else {
            \Civi::log()->warning("Found unrecognised inlay file at $file (updated $mtime). Consider deleting this.");
          }
        }
      }
    }
  }

  /**
   * @return array
   */
  protected function getBatchRecords() {
    $params = [
      // 'select' => [],
      'checkPermissions' => $this->checkPermissions,
      'where' => $this->where,
      'orderBy' => $this->orderBy,
      'limit' => $this->limit,
      'offset' => $this->offset,
    ];

    return (array) civicrm_api4($this->getEntityName(), 'get', $params);
  }
}
