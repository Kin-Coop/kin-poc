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

namespace Civi\ConfigItems;

use Civi\ConfigItems\Entity\Factory;
use Civi\ConfigItems\Entity\OptionValue\CampaignTypeDefinition;
use Civi\ConfigItems\Entity\SimpleEntity\CaseType;
use Civi\ConfigItems\Entity\SimpleEntity\MembershipType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class CompilerPass implements CompilerPassInterface {

  public function process(ContainerBuilder $container) {
    $queueServiceDefinition = $this->createDefinitionClass('Civi\ConfigItems\QueueService');
    $container->setDefinition('civiconfig_queue_service', $queueServiceDefinition);

    $fileFormatFactoryDefinition = $this->createDefinitionClass('Civi\ConfigItems\FileFormat\Factory');
    $container->setDefinition('civiconfig_fileformat_factory', $fileFormatFactoryDefinition);

    $factoryDefinition = $this->createDefinitionClass('Civi\ConfigItems\Entity\Factory');

    $factoryDefinition->addMethodCall('addEntityDefinition', [new Definition('\Civi\ConfigItems\Entity\Extension\Definition'), Factory::EARLIEST_PRIORITY]);
    $factoryDefinition->addMethodCall('addEntityDefinition', [new Definition('\Civi\ConfigItems\Entity\SimpleEntity\ContactType')]);
    $factoryDefinition->addMethodCall('addEntityDefinition', [new Definition('\Civi\ConfigItems\Entity\SimpleEntity\RelationshipType', [['ContactType']])]);
    $factoryDefinition->addMethodCall('addEntityDefinition', [new Definition('\Civi\ConfigItems\Entity\OptionGroup\Definition')]);
    $factoryDefinition->addMethodCall('addEntityDefinition', [new Definition('\Civi\ConfigItems\Entity\OptionValue\Definition', ['event_type'])]);
    $factoryDefinition->addMethodCall('addEntityDefinition', [new Definition('\Civi\ConfigItems\Entity\OptionValue\Definition', ['activity_type'])]);
    if(CaseType::isAvailable()) {
      $factoryDefinition->addMethodCall('addEntityDefinition', [
        new Definition('\Civi\ConfigItems\Entity\SimpleEntity\CaseType', [
          [
            'activity_type',
            'RelationshipType'
          ]
        ])
      ]);
    }
    if(CampaignTypeDefinition::isAvailable()) {
      $factoryDefinition->addMethodCall('addEntityDefinition', [new Definition('\Civi\ConfigItems\Entity\OptionValue\CampaignTypeDefinition')]);
    }
    if(MembershipType::isAvailable()) {
      $factoryDefinition->addMethodCall('addEntityDefinition', [new Definition('\Civi\ConfigItems\Entity\SimpleEntity\MembershipType')]);
    }
    $factoryDefinition->addMethodCall('addEntityDefinition', [new Definition('\Civi\ConfigItems\Entity\SimpleEntity\LocationType')]);
    $factoryDefinition->addMethodCall('addEntityDefinition', [new Definition('\Civi\ConfigItems\Entity\SimpleEntity\MessageTemplate')]);
    $factoryDefinition->addMethodCall('addEntityDefinition', [new Definition('\Civi\ConfigItems\Entity\CustomGroup\Definition', [['OptionGroup']])]);
    // Decorators
    $factoryDefinition->addMethodCall('addDecorator', [new Definition('\Civi\ConfigItems\UrlReplacer\Decorator')]);

    $container->setDefinition('civiconfig_entity_factory', $factoryDefinition);
  }


  /**
   * Returns a definition class.
   * We have to set is private to false on the definition class
   * with newer versions of civicrm (especially with civicrm and drupal 9)
   * with older versions of civicrm the setPrivate method is not available so we cannot set it.
   *
   * @param null $class
   * @param array $arguments
   * @return \Symfony\Component\DependencyInjection\Definition
   */
  private function createDefinitionClass($class = null, array $arguments = array()) {
    $definition = new Definition($class, $arguments);
    $definition->setPublic(true);
    if (method_exists(Definition::class, 'setPrivate')) {
      $definition->setPrivate(FALSE);
    }
    return $definition;
  }



}
