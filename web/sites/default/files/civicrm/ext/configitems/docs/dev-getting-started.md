# Getting started with Development

## Reference

**Entity definition**

An entity definition defines how an entity can be exported and imported. It provides the following:

* `EntityExporter`: class for exporting the entity. This class also provides a configuration form.
* `EntityImporter`: class for importing the entity. This class also provides a configuration form.
* `ConfigurationForm`: Class which holds the configuration form the entity, the interface of this class is the same
for import and export but you can use different forms for exporting and importing.

With the `hook_civicrm_container` or with a `CompilerPass` class you can add entities to CiviConfig. See the code snippet below for an example.

```php

// File: Civi\PhoneInputMask\CompilerPass.php
// Check whether the civiconfig entity factory exists.
if ($container->hasDefinition('civiconfig_entity_factory')) {
  $factory = $container->getDefinition('civiconfig_entity_factory');
  // Create a Symfony definition which reference the SimpleEntity\Definition class
  // And provide a constructor parameter with the name of the entity. Which expects an APIv4 entity LocationType
  // with the methods: get, create, update and delete.
  $phoneInputMaskDefinition = new Definition('\Civi\ConfigItems\Entity\SimpleEntity\Definition', ['LocationType']);
  $factory->addMethodCall('addEntityDefinition', [$phoneInputMaskDefinition]);
}

```

With the code snippet above the `LocationType` entity is available for export and import.

**Entity definition classes**

This extension provides the following entity definition classes.

* `EntityDefinition`: This interface class contains an entity definition. An entity definition defines how
an entity can be exported and imported.
* `SimpleEntity\Definition`: This entity definition is for a straight forward entity, such as Location Type.
This entity definition uses APIv4 to fetch entity data during export and uses APIv4 to create and update data during import.
This entity definition also provides a user interace for exporting importing.
* `OptionValue\Definition`: This entity definition is used to export and import certain specific option groups.
Such as Activity Type, or Campaign Type.
* `OptionGroup\Definition`: This entity definition provides a way to export and import option groups and values.
* `CustomGroup\Definition`: This entity definition provides a way to export and import custom groups and fields.
* `Extension\Definition`: This entity definition provides a way to export and import extensions.

## Guides

- [Developement Guide: make your entity available for import/export](./dev-simple-entity-integration.md)
