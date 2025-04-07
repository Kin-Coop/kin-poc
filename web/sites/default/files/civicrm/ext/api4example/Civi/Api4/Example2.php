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
class Example2 extends Generic\AbstractEntity
{

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
     * @param bool $checkPermissions
     *
     * @return Generic\BasicGetFieldsAction
     * @see \Civi\Api4\Generic\BasicGetFieldsAction
     *
     */
    public static function getFields($checkPermissions = TRUE)
    {
        return (new Generic\BasicGetFieldsAction(__CLASS__, __FUNCTION__, function ($getFieldsAction) {
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
     * @param bool $checkPermissions
     *
     * @return Generic\BasicGetAction
     * @see \Civi\Api4\Generic\BasicGetAction
     *
     */
    public static function get($checkPermissions = TRUE)
    {
        return (new Generic\BasicGetAction(__CLASS__, __FUNCTION__, 'getApi4exampleRecords'))
            ->setCheckPermissions($checkPermissions);
    }

}