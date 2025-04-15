<?php
/**
 * Copyright (C) 2021  Jaap Jansma (jaap.jansma@civicoop.org)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Civi\ConfigItems\Entity\Extension;

use Civi\ConfigItems\Entity\EntityDefinition;
use CRM_Civiconfig_ExtensionUtil as E;

class Definition extends EntityDefinition {

  /**
   * @return \Civi\ConfigItems\Entity\EntityImporter
   */
  protected $importer;

  /**
   * @return \Civi\ConfigItems\Entity\EntityExporter
   */
  protected $exporter;

  /**
   * @param array $afterEntities
   * @param array $beforeEntities
   */
  public function __construct($afterEntities=[], $beforeEntities=[]) {
    parent::__construct('Extension', $afterEntities, $beforeEntities);
    $this->title_plural = E::ts('Extensions');
    $this->title_single = E::ts('Extension');
  }

  /**
   * @return \Civi\ConfigItems\Entity\EntityImporter
   */
  public function getImporterClass() {
    if (!$this->importer) {
      $this->importer = new Importer($this);
    }
    return $this->importer;
  }

  /**
   * @return \Civi\ConfigItems\Entity\EntityExporter
   */
  public function getExporterClass() {
    if (!$this->exporter) {
      $this->exporter = new Exporter($this);
    }
    return $this->exporter;
  }

}
