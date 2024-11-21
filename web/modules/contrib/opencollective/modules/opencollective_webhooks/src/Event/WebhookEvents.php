<?php

namespace Drupal\opencollective_webhooks\Event;

/**
 * All webhook payload types as Drupal event names.
 */
final class WebhookEvents {

  /**
   * Event name for WebhookEvent payload type: 'activated.collective.as.host '.
   *
   * @const string
   */
  const COLLECTIVE_ACTIVATED_AS_HOST = 'opencollective.activated.collective.as.host ';

  /**
   * Event name for WebhookEvent payload type: 'deactivated.collective.as.host'.
   *
   * @const string
   */
  const COLLECTIVE_DEACTIVATED_AS_HOST = 'opencollective.deactivated.collective.as.host';

  /**
   * Event name for WebhookEvent payload type: 'collective.expense.approved'.
   *
   * @const string
   */
  const EXPENSE_APPROVED = 'opencollective.collective.expense.approved';

  /**
   * Event name for WebhookEvent payload type: 'collective.expense.created'.
   *
   * @const string
   */
  const EXPENSE_CREATED = 'opencollective.collective.expense.created';

  /**
   * Event name for WebhookEvent payload type: 'collective.expense.deleted'.
   *
   * @const string
   */
  const EXPENSE_DELETED = 'opencollective.collective.expense.deleted';

  /**
   * Event name for WebhookEvent payload type: 'collective.expense.paid'.
   *
   * @const string
   */
  const EXPENSE_PAID = 'opencollective.collective.expense.paid';

  /**
   * Event name for WebhookEvent payload type: 'collective.expense.rejected'.
   *
   * @const string
   */
  const EXPENSE_REJECTED = 'opencollective.collective.expense.rejected';

  /**
   * Event name for WebhookEvent payload type: 'collective.expense.updated'.
   *
   * @const string
   */
  const EXPENSE_UPDATED = 'opencollective.collective.expense.updated';

  /**
   * Event name for WebhookEvent payload type: 'collective.member.created'.
   *
   * @const string
   */
  const MEMBER_CREATED = 'opencollective.collective.member.created';

  /**
   * Event name for WebhookEvent payload type: 'ticket.confirmed'.
   *
   * @const string
   */
  const TICKET_CONFIRMED = 'opencollective.ticket.confirmed';

  /**
   * Event name for WebhookEvent payload type: 'collective.transaction.created'.
   *
   * @const string
   */
  const TRANSACTION_CREATED = 'opencollective.collective.transaction.created';

  /**
   * Event name for WebhookEvent payload type: 'collective.update.published'.
   *
   * @const string
   */
  const UPDATE_PUBLISHED = 'opencollective.collective.update.published';

  /**
   * Event name for WebhookEvent payload type: 'update.comment.created'.
   *
   * @const string
   */
  const UPDATE_COMMENT_CREATED = 'opencollective.update.comment.created';

  /**
   * List of valid payload types.
   *
   * @const string[]
   */
  const VALID_TYPES = [
    self::COLLECTIVE_ACTIVATED_AS_HOST,
    self::COLLECTIVE_DEACTIVATED_AS_HOST,
    self::EXPENSE_APPROVED,
    self::EXPENSE_CREATED,
    self::EXPENSE_DELETED,
    self::EXPENSE_PAID,
    self::EXPENSE_REJECTED,
    self::EXPENSE_UPDATED,
    self::MEMBER_CREATED,
    self::TICKET_CONFIRMED,
    self::TRANSACTION_CREATED,
    self::UPDATE_PUBLISHED,
    self::UPDATE_COMMENT_CREATED,
  ];

}
