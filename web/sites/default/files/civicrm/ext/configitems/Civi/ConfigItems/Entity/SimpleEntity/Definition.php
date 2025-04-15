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

namespace Civi\ConfigItems\Entity\SimpleEntity;

use Civi\ConfigItems\Entity\EntityDefinition;
use CRM_Civiconfig_ExtensionUtil as E;

class Definition extends EntityDefinition {

  /**
   * @var array
   */
  protected $entityInformation;

  /**
   * @return \Civi\ConfigItems\Entity\EntityImporter
   */
  protected $importer;

  /**
   * @return \Civi\ConfigItems\Entity\EntityExporter
   */
  protected $exporter;

  /**
   * @var string
   */
  protected $nameAttribute;

  /**
   * @var string
   */
  protected $titleAttribute;

  /**
   * @var string
   */
  protected $idAttribute;

  public function __construct($name, $apiEntityName=null, $afterEntities=[], $beforeEntities=[]) {
    parent::__construct($name, $afterEntities, $beforeEntities);
    if (empty($apiEntityName)) {
      $apiEntityName = $name;
    }
    $entities = civicrm_api4('Entity', 'get', [
      'where' => [
        ['name', '=', $apiEntityName],
      ],
      'limit' => 1,
    ]);
    $this->entityInformation = $entities->first();
    $this->title_plural = $this->entityInformation['title_plural'];
    $this->title_single = $this->entityInformation['title'];
    $this->nameAttribute = 'name';
    $this->titleAttribute = 'title';
    $this->idAttribute = 'id';
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
   * Returns the attribute in entity data for the name.
   *
   * @return string
   */
  public function getNameAttribute() {
    return $this->nameAttribute;
  }

  /**
   * Returns the attribute in entity data for the title.
   *
   * @return string
   */
  public function getTitleAttribute() {
    return $this->titleAttribute;
  }

  /**
   * Returns the attribute in entity data for the title.
   *
   * @return string
   */
  public function getIdAttribute() {
    return $this->idAttribute;
  }

  /**
   * Sets the attribute in entity data for the name.
   *
   * @param string $nameAttribute
   * @return \Civi\ConfigItems\Entity\SimpleEntity\Definition
   */
  public function setNameAttribute($nameAttribute) {
    $this->nameAttribute = $nameAttribute;
    return $this;
  }

  /**
   * Sets the attribute in entity data for the title.
   *
   * @param string $titleAttribute
   * @return \Civi\ConfigItems\Entity\SimpleEntity\Definition
   */
  public function setTitleAttribute($titleAttribute) {
    $this->titleAttribute = $titleAttribute;
    return $this;
  }

  /**
   * Sets the attribute in entity data for the id.
   *
   * @param string $idAttribute
   * @return \Civi\ConfigItems\Entity\SimpleEntity\Definition
   */
  public function setIdAttribute(string $idAttribute) {
    $this->idAttribute = $idAttribute;
    return $this;
  }

  /**
   * Returns additional where clauses for api4.
   *
   * @return array
   */
  public function getAdditionalWhereClauses() {
    return [];
  }

  /**
   * @return String
   */
  public function getApiEntityName() {
    return $this->entityInformation['name'];
  }

  /**
   * @return String
   */
  public function getFileName() {
    return $this->entityInformation['name'];
  }

  /**
   * Returns the help text.
   * Return an empty string if no help is available.
   *
   * @return string
   */
  public function getExportHelpText() {
    return '';
  }

  /**
   * Returns the help text.
   * Return an empty string if no help is available.
   *
   * @return string
   */
  public function getImportHelpText() {
    return '';
  }

}
