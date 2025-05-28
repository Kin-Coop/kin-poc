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

namespace Civi\ConfigItems\Entity\OptionValue;

use Civi\ConfigItems\Entity\SimpleEntity\Definition as SimpleEntityDefinition;
use CRM_Civiconfig_ExtensionUtil as E;

class Definition extends SimpleEntityDefinition {

  /**
   * @var String
   */
  protected $option_group;

  public function __construct($option_group_name, $afterEntities = [], $beforeEntities = []) {
    parent::__construct($option_group_name, 'OptionValue', $afterEntities, $beforeEntities);
    $this->option_group = \Civi\Api4\OptionGroup::get()
      ->addWhere('name', '=', $option_group_name)
      ->execute()
      ->first();
    $this->setIdAttribute('id');
    $this->setTitleAttribute('label');
  }

  /**
   * @return string
   */
  public function getTitlePlural() {
    return $this->option_group['title'];
  }

  /**
   * @return string
   */
  public function getTitleSingle() {
    return $this->option_group['title'];
  }

  /**
   * Returns additional where clauses for api4.
   *
   * @return array
   */
  public function getAdditionalWhereClauses() {
    return [
      ['option_group_id', '=', $this->option_group['id']]
    ];
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

  /**
   * @return String
   */
  public function getFileName() {
    return \CRM_Utils_String::convertStringToCamel($this->option_group['name']);
  }

  /**
   * @return int
   */
  public function getOptionGroupId() {
    return $this->option_group['id'];
  }


}
