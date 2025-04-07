<?php

namespace Civi\Api4;

/**
 * Inlay entity.
 *
 * Provided by the Inlay extension.
 *
 * @package Civi\Api4
 **/

class Kinrc extends Generic\AbstractEntity
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
        return (new Generic\BasicGetAction(__CLASS__, __FUNCTION__))
            ->setCheckPermissions($checkPermissions);
    }

    /**
     * This demonstrates overriding a basic action class instead of using it directly.
     *
     * @param bool $checkPermissions
     *
     * @return Action\Kinrc\Create
     */
    public static function create($checkPermissions = TRUE) {
        return (new Action\Kinrc\Create(__CLASS__, __FUNCTION__, 'getApi4exampleRecords'))
            ->setCheckPermissions($checkPermissions);
    }

}

