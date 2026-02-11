<?php
namespace Civi\Api4\Action\Contact;

use Civi\Api4\Generic\Result;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\EntityTag;
use Civi\Api4\Contact;

/**
 * Contact.CheckMembership (APIv4)
 *
 * Checks all contacts tagged "Recurring_Member" (ID: 10).
 * If Membership_Valid_Until (custom_79) is today or earlier,
 * removes that tag and adds "Pending_Member" (ID: 8).
 * Skips trashed contacts (is_deleted = 1).
 */
class CheckMembership extends AbstractAction {

  public function _run(Result $result) {
    $today = (new \DateTime('today'))->format('Y-m-d');

    // 1️⃣ Find all qualifying contacts
    $expiredContacts = Contact::get()
      ->addSelect('id')
      ->addJoin('EntityTag AS entity_tag', 'INNER', ['id', '=', 'entity_tag.entity_id'])
      ->addWhere('entity_tag.tag_id', '=', 10)
      ->addWhere('entity_tag.entity_table', '=', 'civicrm_contact')
      ->addWhere('is_deleted', '=', 0)
      ->addWhere('Membership.Membership_Valid_Until', '<=', $today)
      ->execute();

    $ids = array_column(iterator_to_array($expiredContacts), 'id');

    if (empty($ids)) {
      $result[] = ['message' => 'No expired recurring members found.'];
      return;
    }

    // 2️⃣ Remove "Recurring_Member" tag (ID 10)
    EntityTag::delete()
      ->addWhere('entity_table', '=', 'civicrm_contact')
      ->addWhere('entity_id', 'IN', $ids)
      ->addWhere('tag_id', '=', 10)
      ->execute();

    // 3️⃣ Add "Pending_Member" tag (ID 8)
    foreach ($ids as $cid) {
      EntityTag::create()
        ->addValue('entity_table', 'civicrm_contact')
        ->addValue('entity_id', $cid)
        ->addValue('tag_id', 8)
        ->execute();
    }

    $count = count($ids);
    $result[] = ['message' => "Updated {$count} contacts (excluding trashed)."];
  }
}
