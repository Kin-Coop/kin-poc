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

use Civi\ConfigItems\FileFormat\EntityImportDataException;
use Civi\ConfigItems\Commands\Git;
use Civi\ConfigItems\Commands\Composer;

class Installer {

  /**
   * @param $name
   * @param string|null $parentDirectory
   *
   * @return string|FALSE
   * @throws \Civi\ConfigItems\FileFormat\EntityImportDataException
   */
  public static function downloadFromCiviCRM($name, $parentDirectory=null) {
    $extensionSystem = \CRM_Extension_System::singleton();
    $info = $extensionSystem->getBrowser()->getExtension($name);
    if (empty($info) || empty($info->downloadUrl)) {
      throw new EntityImportDataException("Could not download Extension " .$name." from CiviCRMs extension directory.");
    }
    return self::downloadZip($name, $info->downloadUrl, $parentDirectory);
  }

  /**
   * Install extension from git.
   *
   * @param string $name
   * @param string $repoUrl
   * @param string|null $branch
   * @param string|null $parentDirectory
   *
   * @return string|FALSE
   * @throws \Civi\ConfigItems\FileFormat\EntityImportDataException
   */
  public static function downloadFromGit($name, $repoUrl, $branch=null, $parentDirectory=null) {
    $extensionSystem = \CRM_Extension_System::singleton();
    try {
      if (empty($parentDirectory)) {
        $parentDirectory = $extensionSystem->getDefaultContainer()->getBaseDir();
      }
      $extensionPath = $parentDirectory . DIRECTORY_SEPARATOR . $name;
      if (file_exists($extensionPath)) {
        \CRM_Utils_File::cleanDir($extensionPath, TRUE, FALSE);
      }
      Git::cloneRepository($repoUrl, $parentDirectory, $name, $branch);
      Composer::install($extensionPath, $parentDirectory);
      return $extensionPath;
    } catch (\RuntimeException $ex) {
      throw new EntityImportDataException("Could not create or update Extension with name " .$name.". Error: " . $ex->getMessage());
    }
  }

  /**
   * Download zip file and install it as an extension.
   *
   * @param string $name
   * @param string $url
   * @param string|null $parentDirectory
   *
   * @return string|FALSE
   * @throws \Civi\ConfigItems\FileFormat\EntityImportDataException
   */
  public static function downloadZip($name, $url, $parentDirectory=null) {
    $extensionSystem = \CRM_Extension_System::singleton();
    $extensionDownloader = $extensionSystem->getDownloader();
    $filename = $extensionDownloader->tmpDir . DIRECTORY_SEPARATOR . $name . '.zip';
    if (!$extensionDownloader->fetch($url, $filename)) {
      throw new EntityImportDataException("Could not create or update Extension with name " .$name.". Error: could not download zipfile.");
    }
    return self::unzipExtension($filename, $name, $parentDirectory);
  }

  /**
   * Extract zip file.
   *
   * @param string $zipFile
   *   The local path to a .zip file.
   * @param string $extensionKey
   *   The key of the expected extension.
   * @param string $parentDirectory
   * @return string|FALSE
   *   The key of the extension.
   */
  private static function unzipExtension($zipFile, $extensionKey, $parentDirectory=null) {
    $tmpDir = \CRM_Utils_File::tempdir();
    $extensionSystem = \CRM_Extension_System::singleton();
    if (empty($parentDirectory)) {
      $parentDirectory = $extensionSystem->getDefaultContainer()->getBaseDir();
    }
    $zip = new \ZipArchive();
    $res = $zip->open($zipFile);
    if ($res === TRUE) {
      $zipSubDir = \CRM_Utils_Zip::guessBasedir($zip, $extensionKey);
      if ($zipSubDir === FALSE) {
        return FALSE;
      }
      $extractedZipPath = $tmpDir . DIRECTORY_SEPARATOR . $zipSubDir;
      if (is_dir($extractedZipPath)) {
        if (!\CRM_Utils_File::cleanDir($extractedZipPath, TRUE, FALSE)) {
          return FALSE;
        }
      }
      if (!$zip->extractTo($tmpDir)) {
        return FALSE;
      }
      $zip->close();
      Composer::install($extractedZipPath, $parentDirectory);
      return $extractedZipPath;
    }
    else {
      return FALSE;
    }
  }

}
