<?php
namespace Civi\Api4\Action\Inlay;

use Civi\Api4\Generic\DAOSaveAction;
use Civi\Api4\Generic\Result;

class Save extends DAOSaveAction {
  use WriteTrait;

  /**
   * @inheritDoc
   */
  public function _run(Result $result) {
    parent::_run($result);
    $this->updateBundle($result);
  }

  /**
   * Loop the *fully populated* array in $this->values
   * which contain new values applied over existing data with.
   */
  protected function validateValues() {
    $this->ensurePublicID($this->records);
    // Return parent but I think it returns void anyway.
    return parent::validateValues();
  }
}

