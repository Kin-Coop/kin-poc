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

namespace Civi\ConfigItems\FileFormat;

use Civi\ConfigItems\Entity\EntityExporter;
use CRM_Civiconfig_ExtensionUtil as E;

class ZipFileFileFormat extends AbstractFileFormat {

  /**
   * Creates an export file and returns the file name.
   *
   * @param $config_item_set
   * @return string
   */
  public function export($config_item_set) {
    $tempDir = $this->generateExportDirectory($config_item_set);
    $tempZipFile = \CRM_Utils_File::tempnam($this->getFilenameWithoutExtension($config_item_set)) . '.zip';

    // Initialize archive object
    $zip = new \ZipArchive();
    $zip->open($tempZipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

    // Create recursive directory iterator
    /** @var \SplFileInfo[] $files */
    $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tempDir), \RecursiveIteratorIterator::LEAVES_ONLY);

    foreach ($files as $name => $file)
    {
      // Skip directories (they would be added automatically)
      if (!$file->isDir())
      {
        // Get real and relative path for current file
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($tempDir));

        // Add current file to archive
        $zip->addFile($filePath, $relativePath);
      }
    }

    // Zip archive will be created only after closing object
    $zip->close();
    return $tempZipFile;
  }

  /**
   * Redirects the output as a download.
   * @param $config_item_set
   */
  public function download($config_item_set) {
    $tempZipFile = $this->export($config_item_set);
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Type: application/zip");
    header("Content-Length:" . filesize($tempZipFile));
    header("Content-Disposition: attachment; filename=" . $this->getFilenameWithoutExtension($config_item_set) . ".zip");
    header("Content-Transfer-Encoding: binary");
    readfile($tempZipFile);
    exit;
  }

  /**
   * Uploads and extract the file.
   * Returns the configuration in the imported file.
   *
   * @param string $file
   *   The path to the uploaded file
   * @param array $config_item_set
   * @return array
   */
  public function upload($file, $config_item_set) {
    $zip = new \ZipArchive;
    $zip->open($file);
    $zip->extractTo($this->getImportDirectory($config_item_set));
    $zip->close();
    return $config_item_set;
  }

  /**
   * Validates the uploaded file.
   *
   * @param string $file
   *   The path to the uploaded file
   * @return false|array
   *  Returns FALSE when validation failed. Returns an array containing the config
   *  item set.
   */
  public function validate($file) {
    $zip = new \ZipArchive;
    if ($zip->open($file) !== TRUE) {
      return FALSE;
    }
    $config_item_set = $zip->getFromName('info.json');
    $zip->close();
    if ($config_item_set === FALSE) {
      return FALSE;
    }
    $config_item_set = json_decode($config_item_set, TRUE);
    return $this->validateConfigItemSetForImport($config_item_set);
  }

}
