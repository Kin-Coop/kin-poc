# Development Guide: make your entity available for import/export

In this guide I will show how you can make your custom entity able to be exported and imported with Configuration Loader extension.

This guide is based on the [Phone Input Mask extension](https://lab.civicrm.org/extensions/phoneinputmask) which provides
its own entity for defining valid input masks for phone numbers.

## Requirements

The steps below require that API version 4 is available for our entity.

## Step 1: Create a compiler pass

Create the file `Civi\PhoneInputMask\CompilerPass.php` with the following contents:

```php

namespace Civi\PhoneInputMask;

use Civi\ConfigItems\Entity\Factory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class CompilerPass implements CompilerPassInterface {

  public function process(ContainerBuilder $container) {
    if ($container->hasDefinition('civiconfig_entity_factory')) {
      $container->getDefinition('civiconfig_entity_factory')
        ->addMethodCall('addEntityDefinition', [new Definition('\Civi\ConfigItems\Entity\SimpleEntity\Definition', ['PhoneInputMask']), Factory::EARLY_PRIORITY]);
    }
  }

}

```

## Step 2: make the compiler pass known

Make sure the compiler pass is loaded by editing `phoneinputmask.php` and add the following hook:

```php

/**
 * Implements hook_civicrm_container()
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_container/
 */
function phoneinputmask_civicrm_container($container) {
  $container->addCompilerPass(new Civi\PhoneInputMask\CompilerPass());
}

```
## Explanation

In the `CompilerPass` we pass on the API version 4 entity name of our entity. That way the CiviCRM Configuration Loader
knows that this entity can be exported and imported.
