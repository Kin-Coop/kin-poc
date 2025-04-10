-- +--------------------------------------------------------------------+
-- | Copyright CiviCRM LLC. All rights reserved.                        |
-- |                                                                    |
-- | This work is published under the GNU AGPLv3 license with some      |
-- | permitted exceptions and without any warranty. For full license    |
-- | and copyright information, see https://civicrm.org/licensing       |
-- +--------------------------------------------------------------------+
--
-- Generated from schema.tpl
-- DO NOT EDIT.  Generated by CRM_Core_CodeGen
--
-- /*******************************************************
-- *
-- * Clean up the existing tables - this section generated from drop.tpl
-- *
-- *******************************************************/

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `civicrm_inlay_config_set`;
DROP TABLE IF EXISTS `civicrm_inlay`;

SET FOREIGN_KEY_CHECKS=1;
-- /*******************************************************
-- *
-- * Create new tables
-- *
-- *******************************************************/

-- /*******************************************************
-- *
-- * civicrm_inlay
-- *
-- * Instances of different Inlay Types
-- *
-- *******************************************************/
CREATE TABLE `civicrm_inlay` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Inlay ID',
  `public_id` char(12) NOT NULL COMMENT 'Public Inlay ID used in script tags.',
  `name` varchar(255) NOT NULL COMMENT 'Administrative name',
  `class` varchar(140) NOT NULL COMMENT 'Class name that implements this Inlay Type',
  `config` longtext NOT NULL COMMENT 'JSON blob of config.',
  `status` varchar(20) NOT NULL DEFAULT "on" COMMENT 'on, off or broken',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `public_id`(public_id)
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_inlay_config_set
-- *
-- * Holds sets of config defined against arbitrary schemas provided by inlay type extensions.
-- *
-- *******************************************************/
CREATE TABLE `civicrm_inlay_config_set` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique InlayConfigSet ID',
  `schema_name` varchar(64) NOT NULL COMMENT 'Machine name of schema that owns this, typically prefixed with the inlay extension shortname, e.g. inlaypay_stylesets',
  `set_name` varchar(128) NOT NULL COMMENT 'Machine name of this config item, where needed, must be unique within schema.',
  `label` varchar(255) COMMENT 'Human friendly admin name for the set',
  `config` longtext NOT NULL COMMENT 'JSON blob of config.',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `index_schema_setname`(schema_name, set_name)
)
ENGINE=InnoDB;
