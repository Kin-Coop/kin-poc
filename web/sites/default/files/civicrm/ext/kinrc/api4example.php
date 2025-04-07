<?php

/**
 * Callback for BasicGetAction.
 *
 * For demonstration purposes, this API stores records in a simple json file.
 *
 * This function returns an array of all records.
 * They are keyed by `id` for our own convenience, but `BasicGetAction` doesn't care about the keys.
 *
 * `BasicGetAction` is able to perform sorting/filtering on the results of this function,
 * without this function doing anything special.
 *
 * @param \Civi\Api4\Generic\AbstractAction $actionObject
 * @return array
 */
function getApi4exampleRecords($actionObject) {
  $path = api4ExampleFilePath();
  $contents = @file_get_contents($path);

  // We can pass the action any debugging info we want.
  // It will only be displayed if debug mode is enabled and the user has permission to view debug output.
  $actionObject->_debugOutput['file_path'] = $path;
  $actionObject->_debugOutput['originalContents'] = $contents;

  return $contents ? json_decode($contents, TRUE) : [];
}

/**
 * Callback for `BasicCreateAction`, `BasicUpdateAction` & `BasicSaveAction`.
 *
 * Creates or updates a record (based on the presence of `id`).
 *
 * @param array $record
 * @param \Civi\Api4\Generic\AbstractAction $actionObject
 * @return array
 */
function writeApi4exampleRecord($record, $actionObject) {
  $records = getApi4exampleRecords($actionObject);
  $maxId = $records ? max(array_keys($records)) : 0;

  // Generate an id for new records
  $id = $record['id'] ?? $maxId + 1;

  // If updating an existing record, merge new & existing data
  // IMPORTANT GOTCHA: The Basic Save/Update actions only pass the fields to be altered.
  // It's up to you to merge those fields with the ones already in the existing record.
  if (isset($record['id']) && isset($records[$id])) {
    $record += $records[$id];
  }

  // Insert or update in the array of all records
  $record['id'] = $id;
  $records[$id] = $record;
  $contents = json_encode($records, JSON_PRETTY_PRINT);

  // We can pass the action any debugging info we want.
  // It will only be displayed if debug mode is enabled and the user has permission to view debug output.
  $actionObject->_debugOutput['newContents'] = $contents;

  file_put_contents(api4ExampleFilePath(), $contents);
  return $record;
}

/**
 * Callback for BasicDeleteAction.
 *
 * Removes one record with supplied `id`.
 *
 * @param array $record
 * @param \Civi\Api4\Generic\AbstractAction $actionObject
 * @return array
 */
function deleteApi4exampleRecord($record, $actionObject) {
  $records = getApi4exampleRecords($actionObject);

  unset($records[$record['id']]);

  $contents = json_encode($records, JSON_PRETTY_PRINT);

  // We can pass the action any debugging info we want.
  // It will only be displayed if debug mode is enabled and the user has permission to view debug output.
  $actionObject->_debugOutput['newContents'] = $contents;

  file_put_contents(api4ExampleFilePath(), $contents);

  return $record;
}

/**
 * @return string
 */
function api4ExampleFilePath() {
  return \Civi::paths()->getPath('[civicrm.files]/example-api4-data.json');
}
