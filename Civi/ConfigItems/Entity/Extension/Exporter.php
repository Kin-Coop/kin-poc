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

use Civi\ConfigItems\Entity\EntityExporter;

class Exporter implements EntityExporter {

  /**
   * @var \Civi\ConfigItems\Entity\Extension\Definition;
   */
  protected $entityDefinition;

  /**
   * @var \Civi\ConfigItems\Entity\Extension\ExportForm
   */
  protected $form;

  public function __construct(Definition $entityDefinition) {
    $this->entityDefinition = $entityDefinition;
  }

  /**
   * Returns the help text.
   * Return an empty string if no help is available.
   *
   * @return string
   */
  public function getHelpText() {
    return '';
  }


  /**
   * Exports the entity
   *
   * @param $configuration
   * @param $config_item_set
   * @param string $directory
   *
   * @return array
   */
  public function export($configuration, $config_item_set, $directory = '') {
    return $configuration;
  }

  /**
   * Returns the export configuration form.
   * Returns false if this entity does not have a configuration for export.
   *
   * @return false|\Civi\ConfigItems\Entity\Extension\ExportForm
   */
  public function getExportConfigurationForm() {
    if (!$this->form) {
      $this->form = new ExportForm($this);
    }
    return $this->form;
  }

  /**
   * Returns the entity definition
   *
   * @return \Civi\ConfigItems\Entity\EntityDefinition
   */
  public function getEntityDefinition() {
    return $this->entityDefinition;
  }


}
