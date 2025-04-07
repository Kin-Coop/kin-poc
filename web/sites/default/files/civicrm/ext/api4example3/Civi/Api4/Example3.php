<?php

namespace Civi\Api4;

/**
 * Example of an _ad-hoc_ API.
 *
 * This demonstrates how to create your own API for any arbitrary data source,
 * implementing all the usual actions + a few extras.
 *
 * Try creating some Example records in the _API Explorer_ using `Create` or `Save`.
 * Then try `Get` (with `select`, `where`, `orderBy`, etc.),
 * `Update`, `Replace` and `Delete` too!
 *
 * The API entity `Example` is "declared" simply by the presence of our `Civi\Api4\Example` class.
 * Just that one file is all that's needed for an API to exist; but non-trivial APIs will usually
 * be organized into different files, as we've done in this extension.
 *
 * _The "@method" annotation helps IDEs with the virtual action provided by our `Random` class._
 * @method static Action\Example\Random random()
 * _Annotations for virtual methods are helpful but not required_
 * _(when mixing an action into an entity outside this extension, it wouldn't be possible)._
 *
 * **Note:** this docblock will appear in the _API Explorer_, as will these links:
 * @see https://lab.civicrm.org/extensions/api4example
 * @see https://docs.civicrm.org/dev/en/latest/api/v4/architecture/
 *
 * @package Civi\Api4
 */
class Example3 extends Generic\AbstractEntity {

  /**
   * Every entity **must** implement `getFields`.
   *
   * This tells the action classes what input/output fields to expect,
   * and also populates the _API Explorer_.
   *
   * The `BasicGetFieldsAction` takes a callback function. We could have defined the function elsewhere
   * and passed a `callable` reference to it, but passing in an anonymous function works too.
   *
   * The callback function takes the `BasicGetFieldsAction` object as a parameter in case we need to access its properties.
   * Especially useful is the `getAction()` method as we may need to adjust the list of fields per action.
   *
   * Note that it's possible to bypass this function if an action class lists its own fields by declaring a `fields()` method.
   *
   * Read more about how to implement your own `GetFields` action:
   * @see \Civi\Api4\Generic\BasicGetFieldsAction
   *
   * @param bool $checkPermissions
   *
   * @return Generic\BasicGetFieldsAction
   */
  public static function getFields($checkPermissions = TRUE) {
    return (new Generic\BasicGetFieldsAction(__CLASS__, __FUNCTION__, function($getFieldsAction) {
      return [
        [
          'name' => 'id',
          'data_type' => 'Integer',
          'description' => 'Unique identifier. If it were named something other than "id" we would need to override the getInfo() function to supply "primary_key".',
        ],
        [
          'name' => 'example_str',
          'description' => "Example string field. We don't need to specify data_type as String is the default.",
        ],
        [
          'name' => 'example_int',
          'data_type' => 'Integer',
          'description' => "Example number field. The Api Explorer will present this as numeric input.",
        ],
        [
          'name' => 'example_bool',
          'data_type' => 'Boolean',
          'description' => "Example boolean field. The Api Explorer will present true/false options.",
        ],
        [
          'name' => 'example_options',
          'description' => "Example field with option list. The Api Explorer will display these options.",
          'options' => ['r' => 'Red', 'b' => 'Blue', 'g' => 'Green'],
        ],
      ];
    }))->setCheckPermissions($checkPermissions);
  }

  /**
   * `BasicGetAction` is the most complex basic action class, but is easy to implement.
   *
   * Simply pass it a function that returns the full array of records (known as the "getter" function),
   * and the API takes care of all the sorting and filtering automatically.
   *
   * Alternately, if performance is a concern and it isn't practical to return all records,
   * your getter can take advantage of some helper functions to optimize for e.g. fetching item(s) by id
   * (the getter receives the `BasicGetAction` object as its argument).
   *
   * Read more about how to implement your own `Get` action:
   * @see \Civi\Api4\Generic\BasicGetAction
   *
   * @param bool $checkPermissions
   *
   * @return Generic\BasicGetAction
   */
  public static function get($checkPermissions = TRUE) {
    return (new Generic\BasicGetAction(__CLASS__, __FUNCTION__, 'getApi4exampleRecords'))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * This demonstrates overriding a basic action class instead of using it directly.
   *
   * @param bool $checkPermissions
   *
   * @return Action\Example\Create
   */
  public static function create($checkPermissions = TRUE) {
    return (new Action\Example\Create(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * `BasicUpdateAction` allows a single record to be updated.
   *
   * We pass it a setter function which takes two arguments:
   *  1. The record to be updated (as an array). Note this only contains an `id` plus fields to be updated,
   *     not existing data unless the `reload` parameter is set.
   *  2. The `BasicUpdateAction` object, in case we need to access any of its properties e.g. `getCheckPermissions()`.
   *
   * Our setter is responsible for matching by `id` to an existing record, combining existing data with new values,
   * and storing the updated record. Optionally, if no existing record was found with the supplied id, it could throw an exception.
   *
   * If our records' unique identifying field was named something other than `id` (like `name` or `key`) then we'd pass
   * that to the `BasicUpdateAction` constructor.
   *
   * Read more about how to implement your own `Update` action:
   * @see \Civi\Api4\Generic\BasicUpdateAction
   *
   * @param bool $checkPermissions
   *
   * @return Generic\BasicUpdateAction
   */
  public static function update($checkPermissions = TRUE) {
    return (new Generic\BasicUpdateAction(__CLASS__, __FUNCTION__, 'writeApi4exampleRecord'))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * `BasicSaveAction` allows multiple records to be created or updated at once.
   *
   * We pass it a setter function which is called once per record, so we can re-use the exact same function
   * from our `Create` and `Update` actions. It takes two arguments:
   *
   *  1. The record to be creted or updated (as an array). Note that for existing records this array is not guaranteed
   *     to contain existing data, only the `id` plus fields to be updated.
   *  2. The `BasicSaveAction` object, in case we need to access any of its properties e.g. `getCheckPermissions()`.
   *
   * Our setter can tell the difference between a record to be created vs updated by the presence of an `id`.
   *
   * If our records' unique identifying field was named something other than `id` (like `name` or `key`) then we'd pass
   * that to the `BasicSaveAction` constructor.
   *
   * Read more about how to implement your own `Save` action:
   * @see \Civi\Api4\Generic\BasicSaveAction
   *
   * @param bool $checkPermissions
   *
   * @return Generic\BasicSaveAction
   */
  public static function save($checkPermissions = TRUE) {
    return (new Generic\BasicSaveAction(__CLASS__, __FUNCTION__, 'writeApi4exampleRecord'))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * Our `Delete` action uses the `BasicBatchAction` class.
   *
   * There is no `BasicDeleteAction` because that isn't structurally different from other batch-style actions.
   * The only difference is what the callback function does with the record passed to it.
   *
   * The callback for `BasicBatchAction` takes two arguments:
   *  1. The record to be updated (as an array). Note this only contains an "id" plus fields to be updated,
   *     not existing data unless the "reload" parameter is set.
   *  2. The `BasicBatchAction` object, in case we need to access any of its properties e.g. `getCheckPermissions()`.
   *
   * Read more about batch actions:
   * @see \Civi\Api4\Generic\BasicBatchAction
   *
   * @param bool $checkPermissions
   *
   * @return Generic\BasicBatchAction
   */
  public static function delete($checkPermissions = TRUE) {
    return (new Generic\BasicBatchAction(__CLASS__, __FUNCTION__, 'deleteApi4exampleRecord'))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * Unlike the other Basic action classes, `Replace` does not require any callback.
   *
   * This is because it calls `Get`, `Save` and `Delete` internally - those must be defined for an entity to implement `Replace`.
   *
   * Read more about the `Replace` action:
   * @inheritDoc
   * @see \Civi\Api4\Generic\BasicReplaceAction
   * @return Generic\BasicReplaceAction
   */
  public static function replace($checkPermissions = TRUE) {
    return (new Generic\BasicReplaceAction(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

}
