<?php

namespace Drupal\opencollective\Service;

use Drupal\Core\Locale\CountryManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Open collective options for embeds and graphql enums.
 */
class ParameterOptions {

  use StringTranslationTrait;

  /**
   * Embed button colors.
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function embedButtonColors(): array {
    return [
      'blue' => $this->t('Blue'),
      'white' => $this->t('White'),
    ];
  }

  /**
   * Embed button verbs.
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function embedButtonVerbs(): array {
    return [
      'contribute' => $this->t('Contribute'),
      'donate' => $this->t('Donate'),
    ];
  }

  /**
   * Get all account cache types.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/AccountCacheType
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function accountCacheTypes(): array {
    return [
      'CLOUDFLARE' => $this->t('CLOUDFLARE'),
      'GRAPHQL_QUERIES' => $this->t('GRAPHQL_QUERIES'),
      'CONTRIBUTORS' => $this->t('CONTRIBUTORS'),
    ];
  }

  /**
   * Get all account freeze actions.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/AccountFreezeAction
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function accountFreezeActions(): array {
    return [
      'FREEZE' => $this->t('FREEZE'),
      'UNFREEZE' => $this->t('UNFREEZE'),
    ];
  }

  /**
   * Get all account orders filters.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/AccountOrdersFilter
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function accountOrdersFilters(): array {
    return [
      'INCOMING' => $this->t('INCOMING'),
      'OUTGOING' => $this->t('OUTGOING'),
    ];
  }

  /**
   * Get all account types.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/AccountType
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function accountTypes(): array {
    return [
      'BOT' => $this->t('BOT'),
      'COLLECTIVE' => $this->t('COLLECTIVE'),
      'EVENT' => $this->t('EVENT'),
      'FUND' => $this->t('FUND'),
      'INDIVIDUAL' => $this->t('INDIVIDUAL'),
      'ORGANIZATION' => $this->t('ORGANIZATION'),
      'PROJECT' => $this->t('PROJECT'),
      'VENDOR' => $this->t('VENDOR'),
    ];
  }

  /**
   * Get all activity and classes types.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/ActivityAndClassesType
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function activityAndClassesTypes(): array {
    return [
      'ACTIVITY_ALL' => $this->t('ACTIVITY_ALL'),
      'CONNECTED_ACCOUNT_CREATED' => $this->t('CONNECTED_ACCOUNT_CREATED'),
      'CONNECTED_ACCOUNT_ERROR' => $this->t('CONNECTED_ACCOUNT_ERROR'),
      'COLLECTIVE_CREATED_GITHUB' => $this->t('COLLECTIVE_CREATED_GITHUB'),
      'COLLECTIVE_APPLY' => $this->t('COLLECTIVE_APPLY'),
      'COLLECTIVE_APPROVED' => $this->t('COLLECTIVE_APPROVED'),
      'COLLECTIVE_REJECTED' => $this->t('COLLECTIVE_REJECTED'),
      'COLLECTIVE_CREATED' => $this->t('COLLECTIVE_CREATED'),
      'COLLECTIVE_EDITED' => $this->t('COLLECTIVE_EDITED'),
      'COLLECTIVE_DELETED' => $this->t('COLLECTIVE_DELETED'),
      'COLLECTIVE_UNHOSTED' => $this->t('COLLECTIVE_UNHOSTED'),
      'ORGANIZATION_COLLECTIVE_CREATED' => $this->t('ORGANIZATION_COLLECTIVE_CREATED'),
      'COLLECTIVE_FROZEN' => $this->t('COLLECTIVE_FROZEN'),
      'COLLECTIVE_UNFROZEN' => $this->t('COLLECTIVE_UNFROZEN'),
      'COLLECTIVE_CONVERSATION_CREATED' => $this->t('COLLECTIVE_CONVERSATION_CREATED'),
      'UPDATE_COMMENT_CREATED' => $this->t('UPDATE_COMMENT_CREATED'),
      'EXPENSE_COMMENT_CREATED' => $this->t('EXPENSE_COMMENT_CREATED'),
      'CONVERSATION_COMMENT_CREATED' => $this->t('CONVERSATION_COMMENT_CREATED'),
      'COLLECTIVE_EXPENSE_CREATED' => $this->t('COLLECTIVE_EXPENSE_CREATED'),
      'COLLECTIVE_EXPENSE_DELETED' => $this->t('COLLECTIVE_EXPENSE_DELETED'),
      'COLLECTIVE_EXPENSE_UPDATED' => $this->t('COLLECTIVE_EXPENSE_UPDATED'),
      'COLLECTIVE_EXPENSE_REJECTED' => $this->t('COLLECTIVE_EXPENSE_REJECTED'),
      'COLLECTIVE_EXPENSE_APPROVED' => $this->t('COLLECTIVE_EXPENSE_APPROVED'),
      'COLLECTIVE_EXPENSE_UNAPPROVED' => $this->t('COLLECTIVE_EXPENSE_UNAPPROVED'),
      'COLLECTIVE_EXPENSE_MOVED' => $this->t('COLLECTIVE_EXPENSE_MOVED'),
      'COLLECTIVE_EXPENSE_PAID' => $this->t('COLLECTIVE_EXPENSE_PAID'),
      'COLLECTIVE_EXPENSE_MARKED_AS_UNPAID' => $this->t('COLLECTIVE_EXPENSE_MARKED_AS_UNPAID'),
      'COLLECTIVE_EXPENSE_MARKED_AS_SPAM' => $this->t('COLLECTIVE_EXPENSE_MARKED_AS_SPAM'),
      'COLLECTIVE_EXPENSE_MARKED_AS_INCOMPLETE' => $this->t('COLLECTIVE_EXPENSE_MARKED_AS_INCOMPLETE'),
      'COLLECTIVE_EXPENSE_PROCESSING' => $this->t('COLLECTIVE_EXPENSE_PROCESSING'),
      'COLLECTIVE_EXPENSE_SCHEDULED_FOR_PAYMENT' => $this->t('COLLECTIVE_EXPENSE_SCHEDULED_FOR_PAYMENT'),
      'COLLECTIVE_EXPENSE_UNSCHEDULED_FOR_PAYMENT' => $this->t('COLLECTIVE_EXPENSE_UNSCHEDULED_FOR_PAYMENT'),
      'COLLECTIVE_EXPENSE_ERROR' => $this->t('COLLECTIVE_EXPENSE_ERROR'),
      'COLLECTIVE_EXPENSE_INVITE_DRAFTED' => $this->t('COLLECTIVE_EXPENSE_INVITE_DRAFTED'),
      'COLLECTIVE_EXPENSE_RECURRING_DRAFTED' => $this->t('COLLECTIVE_EXPENSE_RECURRING_DRAFTED'),
      'COLLECTIVE_EXPENSE_MISSING_RECEIPT' => $this->t('COLLECTIVE_EXPENSE_MISSING_RECEIPT'),
      'TAXFORM_REQUEST' => $this->t('TAXFORM_REQUEST'),
      'COLLECTIVE_VIRTUAL_CARD_ADDED' => $this->t('COLLECTIVE_VIRTUAL_CARD_ADDED'),
      'COLLECTIVE_VIRTUAL_CARD_MISSING_RECEIPTS' => $this->t('COLLECTIVE_VIRTUAL_CARD_MISSING_RECEIPTS'),
      'COLLECTIVE_VIRTUAL_CARD_SUSPENDED' => $this->t('COLLECTIVE_VIRTUAL_CARD_SUSPENDED'),
      'COLLECTIVE_VIRTUAL_CARD_DELETED' => $this->t('COLLECTIVE_VIRTUAL_CARD_DELETED'),
      'VIRTUAL_CARD_REQUESTED' => $this->t('VIRTUAL_CARD_REQUESTED'),
      'VIRTUAL_CARD_CHARGE_DECLINED' => $this->t('VIRTUAL_CARD_CHARGE_DECLINED'),
      'VIRTUAL_CARD_PURCHASE' => $this->t('VIRTUAL_CARD_PURCHASE'),
      'COLLECTIVE_MEMBER_INVITED' => $this->t('COLLECTIVE_MEMBER_INVITED'),
      'COLLECTIVE_MEMBER_CREATED' => $this->t('COLLECTIVE_MEMBER_CREATED'),
      'COLLECTIVE_CORE_MEMBER_ADDED' => $this->t('COLLECTIVE_CORE_MEMBER_ADDED'),
      'COLLECTIVE_CORE_MEMBER_INVITED' => $this->t('COLLECTIVE_CORE_MEMBER_INVITED'),
      'COLLECTIVE_CORE_MEMBER_INVITATION_DECLINED' => $this->t('COLLECTIVE_CORE_MEMBER_INVITATION_DECLINED'),
      'COLLECTIVE_CORE_MEMBER_REMOVED' => $this->t('COLLECTIVE_CORE_MEMBER_REMOVED'),
      'COLLECTIVE_CORE_MEMBER_EDITED' => $this->t('COLLECTIVE_CORE_MEMBER_EDITED'),
      'COLLECTIVE_TRANSACTION_CREATED' => $this->t('COLLECTIVE_TRANSACTION_CREATED'),
      'COLLECTIVE_UPDATE_CREATED' => $this->t('COLLECTIVE_UPDATE_CREATED'),
      'COLLECTIVE_UPDATE_PUBLISHED' => $this->t('COLLECTIVE_UPDATE_PUBLISHED'),
      'COLLECTIVE_CONTACT' => $this->t('COLLECTIVE_CONTACT'),
      'HOST_APPLICATION_CONTACT' => $this->t('HOST_APPLICATION_CONTACT'),
      'CONTRIBUTION_REJECTED' => $this->t('CONTRIBUTION_REJECTED'),
      'SUBSCRIPTION_ACTIVATED' => $this->t('SUBSCRIPTION_ACTIVATED'),
      'SUBSCRIPTION_CANCELED' => $this->t('SUBSCRIPTION_CANCELED'),
      'TICKET_CONFIRMED' => $this->t('TICKET_CONFIRMED'),
      'ORDER_CANCELED_ARCHIVED_COLLECTIVE' => $this->t('ORDER_CANCELED_ARCHIVED_COLLECTIVE'),
      'ORDER_PENDING' => $this->t('ORDER_PENDING'),
      'ORDER_PENDING_CRYPTO' => $this->t('ORDER_PENDING_CRYPTO'),
      'ORDER_PENDING_CONTRIBUTION_NEW' => $this->t('ORDER_PENDING_CONTRIBUTION_NEW'),
      'ORDER_PENDING_CONTRIBUTION_REMINDER' => $this->t('ORDER_PENDING_CONTRIBUTION_REMINDER'),
      'ORDER_PROCESSING' => $this->t('ORDER_PROCESSING'),
      'ORDER_PAYMENT_FAILED' => $this->t('ORDER_PAYMENT_FAILED'),
      'ORDER_THANKYOU' => $this->t('ORDER_THANKYOU'),
      'ORDER_PENDING_CREATED' => $this->t('ORDER_PENDING_CREATED'),
      'ORDER_PENDING_FOLLOWUP' => $this->t('ORDER_PENDING_FOLLOWUP'),
      'ORDER_PENDING_RECEIVED' => $this->t('ORDER_PENDING_RECEIVED'),
      'ORDERS_SUSPICIOUS' => $this->t('ORDERS_SUSPICIOUS'),
      'BACKYOURSTACK_DISPATCH_CONFIRMED' => $this->t('BACKYOURSTACK_DISPATCH_CONFIRMED'),
      'PAYMENT_FAILED' => $this->t('PAYMENT_FAILED'),
      'PAYMENT_CREDITCARD_CONFIRMATION' => $this->t('PAYMENT_CREDITCARD_CONFIRMATION'),
      'PAYMENT_CREDITCARD_EXPIRING' => $this->t('PAYMENT_CREDITCARD_EXPIRING'),
      'USER_CREATED' => $this->t('USER_CREATED'),
      'USER_NEW_TOKEN' => $this->t('USER_NEW_TOKEN'),
      'USER_SIGNIN' => $this->t('USER_SIGNIN'),
      'USER_RESET_PASSWORD' => $this->t('USER_RESET_PASSWORD'),
      'OAUTH_APPLICATION_AUTHORIZED' => $this->t('OAUTH_APPLICATION_AUTHORIZED'),
      'TWO_FACTOR_CODE_ADDED' => $this->t('TWO_FACTOR_CODE_ADDED'),
      'TWO_FACTOR_CODE_DELETED' => $this->t('TWO_FACTOR_CODE_DELETED'),
      'USER_CHANGE_EMAIL' => $this->t('USER_CHANGE_EMAIL'),
      'USER_PAYMENT_METHOD_CREATED' => $this->t('USER_PAYMENT_METHOD_CREATED'),
      'USER_PASSWORD_SET' => $this->t('USER_PASSWORD_SET'),
      'USER_CARD_CLAIMED' => $this->t('USER_CARD_CLAIMED'),
      'USER_CARD_INVITED' => $this->t('USER_CARD_INVITED'),
      'WEBHOOK_STRIPE_RECEIVED' => $this->t('WEBHOOK_STRIPE_RECEIVED'),
      'WEBHOOK_PAYPAL_RECEIVED' => $this->t('WEBHOOK_PAYPAL_RECEIVED'),
      'COLLECTIVE_MONTHLY_REPORT' => $this->t('COLLECTIVE_MONTHLY_REPORT'),
      'ACTIVATED_COLLECTIVE_AS_HOST' => $this->t('ACTIVATED_COLLECTIVE_AS_HOST'),
      'ACTIVATED_COLLECTIVE_AS_INDEPENDENT' => $this->t('ACTIVATED_COLLECTIVE_AS_INDEPENDENT'),
      'DEACTIVATED_COLLECTIVE_AS_HOST' => $this->t('DEACTIVATED_COLLECTIVE_AS_HOST'),
      'ADDED_FUND_TO_ORG' => $this->t('ADDED_FUND_TO_ORG'),
      'COLLECTIVE_TRANSACTION_PAID' => $this->t('COLLECTIVE_TRANSACTION_PAID'),
      'COLLECTIVE_USER_ADDED' => $this->t('COLLECTIVE_USER_ADDED'),
      'COLLECTIVE_VIRTUAL_CARD_ASSIGNED' => $this->t('COLLECTIVE_VIRTUAL_CARD_ASSIGNED'),
      'COLLECTIVE_VIRTUAL_CARD_CREATED' => $this->t('COLLECTIVE_VIRTUAL_CARD_CREATED'),
      'SUBSCRIPTION_CONFIRMED' => $this->t('SUBSCRIPTION_CONFIRMED'),
      'COLLECTIVE_COMMENT_CREATED' => $this->t('COLLECTIVE_COMMENT_CREATED'),
      'COLLECTIVE' => $this->t('COLLECTIVE'),
      'EXPENSES' => $this->t('EXPENSES'),
      'CONTRIBUTIONS' => $this->t('CONTRIBUTIONS'),
      'ACTIVITIES_UPDATES' => $this->t('ACTIVITIES_UPDATES'),
      'VIRTUAL_CARDS' => $this->t('VIRTUAL_CARDS'),
      'FUND_EVENTS' => $this->t('FUND_EVENTS'),
      'REPORTS' => $this->t('REPORTS'),
    ];
  }

  /**
   * Get all activity channels.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/ActivityChannel
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function activityChannels(): array {
    return [
      'gitter' => $this->t('gitter'),
      'slack' => $this->t('slack'),
      'twitter' => $this->t('twitter'),
      'webhook' => $this->t('webhook'),
      'email' => $this->t('email'),
    ];
  }

  /**
   * Get all activity types.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/ActivityType
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function activityTypes(): array {
    return [
      'ACTIVITY_ALL' => $this->t('ACTIVITY_ALL'),
      'CONNECTED_ACCOUNT_CREATED' => $this->t('CONNECTED_ACCOUNT_CREATED'),
      'CONNECTED_ACCOUNT_ERROR' => $this->t('CONNECTED_ACCOUNT_ERROR'),
      'COLLECTIVE_CREATED_GITHUB' => $this->t('COLLECTIVE_CREATED_GITHUB'),
      'COLLECTIVE_APPLY' => $this->t('COLLECTIVE_APPLY'),
      'COLLECTIVE_APPROVED' => $this->t('COLLECTIVE_APPROVED'),
      'COLLECTIVE_REJECTED' => $this->t('COLLECTIVE_REJECTED'),
      'COLLECTIVE_CREATED' => $this->t('COLLECTIVE_CREATED'),
      'COLLECTIVE_EDITED' => $this->t('COLLECTIVE_EDITED'),
      'COLLECTIVE_DELETED' => $this->t('COLLECTIVE_DELETED'),
      'COLLECTIVE_UNHOSTED' => $this->t('COLLECTIVE_UNHOSTED'),
      'ORGANIZATION_COLLECTIVE_CREATED' => $this->t('ORGANIZATION_COLLECTIVE_CREATED'),
      'COLLECTIVE_FROZEN' => $this->t('COLLECTIVE_FROZEN'),
      'COLLECTIVE_UNFROZEN' => $this->t('COLLECTIVE_UNFROZEN'),
      'COLLECTIVE_CONVERSATION_CREATED' => $this->t('COLLECTIVE_CONVERSATION_CREATED'),
      'UPDATE_COMMENT_CREATED' => $this->t('UPDATE_COMMENT_CREATED'),
      'EXPENSE_COMMENT_CREATED' => $this->t('EXPENSE_COMMENT_CREATED'),
      'CONVERSATION_COMMENT_CREATED' => $this->t('CONVERSATION_COMMENT_CREATED'),
      'COLLECTIVE_EXPENSE_CREATED' => $this->t('COLLECTIVE_EXPENSE_CREATED'),
      'COLLECTIVE_EXPENSE_DELETED' => $this->t('COLLECTIVE_EXPENSE_DELETED'),
      'COLLECTIVE_EXPENSE_UPDATED' => $this->t('COLLECTIVE_EXPENSE_UPDATED'),
      'COLLECTIVE_EXPENSE_REJECTED' => $this->t('COLLECTIVE_EXPENSE_REJECTED'),
      'COLLECTIVE_EXPENSE_APPROVED' => $this->t('COLLECTIVE_EXPENSE_APPROVED'),
      'COLLECTIVE_EXPENSE_UNAPPROVED' => $this->t('COLLECTIVE_EXPENSE_UNAPPROVED'),
      'COLLECTIVE_EXPENSE_MOVED' => $this->t('COLLECTIVE_EXPENSE_MOVED'),
      'COLLECTIVE_EXPENSE_PAID' => $this->t('COLLECTIVE_EXPENSE_PAID'),
      'COLLECTIVE_EXPENSE_MARKED_AS_UNPAID' => $this->t('COLLECTIVE_EXPENSE_MARKED_AS_UNPAID'),
      'COLLECTIVE_EXPENSE_MARKED_AS_SPAM' => $this->t('COLLECTIVE_EXPENSE_MARKED_AS_SPAM'),
      'COLLECTIVE_EXPENSE_MARKED_AS_INCOMPLETE' => $this->t('COLLECTIVE_EXPENSE_MARKED_AS_INCOMPLETE'),
      'COLLECTIVE_EXPENSE_PROCESSING' => $this->t('COLLECTIVE_EXPENSE_PROCESSING'),
      'COLLECTIVE_EXPENSE_SCHEDULED_FOR_PAYMENT' => $this->t('COLLECTIVE_EXPENSE_SCHEDULED_FOR_PAYMENT'),
      'COLLECTIVE_EXPENSE_UNSCHEDULED_FOR_PAYMENT' => $this->t('COLLECTIVE_EXPENSE_UNSCHEDULED_FOR_PAYMENT'),
      'COLLECTIVE_EXPENSE_ERROR' => $this->t('COLLECTIVE_EXPENSE_ERROR'),
      'COLLECTIVE_EXPENSE_INVITE_DRAFTED' => $this->t('COLLECTIVE_EXPENSE_INVITE_DRAFTED'),
      'COLLECTIVE_EXPENSE_RECURRING_DRAFTED' => $this->t('COLLECTIVE_EXPENSE_RECURRING_DRAFTED'),
      'COLLECTIVE_EXPENSE_MISSING_RECEIPT' => $this->t('COLLECTIVE_EXPENSE_MISSING_RECEIPT'),
      'TAXFORM_REQUEST' => $this->t('TAXFORM_REQUEST'),
      'COLLECTIVE_VIRTUAL_CARD_ADDED' => $this->t('COLLECTIVE_VIRTUAL_CARD_ADDED'),
      'COLLECTIVE_VIRTUAL_CARD_MISSING_RECEIPTS' => $this->t('COLLECTIVE_VIRTUAL_CARD_MISSING_RECEIPTS'),
      'COLLECTIVE_VIRTUAL_CARD_SUSPENDED' => $this->t('COLLECTIVE_VIRTUAL_CARD_SUSPENDED'),
      'COLLECTIVE_VIRTUAL_CARD_DELETED' => $this->t('COLLECTIVE_VIRTUAL_CARD_DELETED'),
      'VIRTUAL_CARD_REQUESTED' => $this->t('VIRTUAL_CARD_REQUESTED'),
      'VIRTUAL_CARD_CHARGE_DECLINED' => $this->t('VIRTUAL_CARD_CHARGE_DECLINED'),
      'VIRTUAL_CARD_PURCHASE' => $this->t('VIRTUAL_CARD_PURCHASE'),
      'COLLECTIVE_MEMBER_INVITED' => $this->t('COLLECTIVE_MEMBER_INVITED'),
      'COLLECTIVE_MEMBER_CREATED' => $this->t('COLLECTIVE_MEMBER_CREATED'),
      'COLLECTIVE_CORE_MEMBER_ADDED' => $this->t('COLLECTIVE_CORE_MEMBER_ADDED'),
      'COLLECTIVE_CORE_MEMBER_INVITED' => $this->t('COLLECTIVE_CORE_MEMBER_INVITED'),
      'COLLECTIVE_CORE_MEMBER_INVITATION_DECLINED' => $this->t('COLLECTIVE_CORE_MEMBER_INVITATION_DECLINED'),
      'COLLECTIVE_CORE_MEMBER_REMOVED' => $this->t('COLLECTIVE_CORE_MEMBER_REMOVED'),
      'COLLECTIVE_CORE_MEMBER_EDITED' => $this->t('COLLECTIVE_CORE_MEMBER_EDITED'),
      'COLLECTIVE_TRANSACTION_CREATED' => $this->t('COLLECTIVE_TRANSACTION_CREATED'),
      'COLLECTIVE_UPDATE_CREATED' => $this->t('COLLECTIVE_UPDATE_CREATED'),
      'COLLECTIVE_UPDATE_PUBLISHED' => $this->t('COLLECTIVE_UPDATE_PUBLISHED'),
      'COLLECTIVE_CONTACT' => $this->t('COLLECTIVE_CONTACT'),
      'HOST_APPLICATION_CONTACT' => $this->t('HOST_APPLICATION_CONTACT'),
      'CONTRIBUTION_REJECTED' => $this->t('CONTRIBUTION_REJECTED'),
      'SUBSCRIPTION_ACTIVATED' => $this->t('SUBSCRIPTION_ACTIVATED'),
      'SUBSCRIPTION_CANCELED' => $this->t('SUBSCRIPTION_CANCELED'),
      'TICKET_CONFIRMED' => $this->t('TICKET_CONFIRMED'),
      'ORDER_CANCELED_ARCHIVED_COLLECTIVE' => $this->t('ORDER_CANCELED_ARCHIVED_COLLECTIVE'),
      'ORDER_PENDING' => $this->t('ORDER_PENDING'),
      'ORDER_PENDING_CRYPTO' => $this->t('ORDER_PENDING_CRYPTO'),
      'ORDER_PENDING_CONTRIBUTION_NEW' => $this->t('ORDER_PENDING_CONTRIBUTION_NEW'),
      'ORDER_PENDING_CONTRIBUTION_REMINDER' => $this->t('ORDER_PENDING_CONTRIBUTION_REMINDER'),
      'ORDER_PROCESSING' => $this->t('ORDER_PROCESSING'),
      'ORDER_PAYMENT_FAILED' => $this->t('ORDER_PAYMENT_FAILED'),
      'ORDER_THANKYOU' => $this->t('ORDER_THANKYOU'),
      'ORDER_PENDING_CREATED' => $this->t('ORDER_PENDING_CREATED'),
      'ORDER_PENDING_FOLLOWUP' => $this->t('ORDER_PENDING_FOLLOWUP'),
      'ORDER_PENDING_RECEIVED' => $this->t('ORDER_PENDING_RECEIVED'),
      'ORDERS_SUSPICIOUS' => $this->t('ORDERS_SUSPICIOUS'),
      'BACKYOURSTACK_DISPATCH_CONFIRMED' => $this->t('BACKYOURSTACK_DISPATCH_CONFIRMED'),
      'PAYMENT_FAILED' => $this->t('PAYMENT_FAILED'),
      'PAYMENT_CREDITCARD_CONFIRMATION' => $this->t('PAYMENT_CREDITCARD_CONFIRMATION'),
      'PAYMENT_CREDITCARD_EXPIRING' => $this->t('PAYMENT_CREDITCARD_EXPIRING'),
      'USER_CREATED' => $this->t('USER_CREATED'),
      'USER_NEW_TOKEN' => $this->t('USER_NEW_TOKEN'),
      'USER_SIGNIN' => $this->t('USER_SIGNIN'),
      'USER_RESET_PASSWORD' => $this->t('USER_RESET_PASSWORD'),
      'OAUTH_APPLICATION_AUTHORIZED' => $this->t('OAUTH_APPLICATION_AUTHORIZED'),
      'TWO_FACTOR_CODE_ADDED' => $this->t('TWO_FACTOR_CODE_ADDED'),
      'TWO_FACTOR_CODE_DELETED' => $this->t('TWO_FACTOR_CODE_DELETED'),
      'USER_CHANGE_EMAIL' => $this->t('USER_CHANGE_EMAIL'),
      'USER_PAYMENT_METHOD_CREATED' => $this->t('USER_PAYMENT_METHOD_CREATED'),
      'USER_PASSWORD_SET' => $this->t('USER_PASSWORD_SET'),
      'USER_CARD_CLAIMED' => $this->t('USER_CARD_CLAIMED'),
      'USER_CARD_INVITED' => $this->t('USER_CARD_INVITED'),
      'WEBHOOK_STRIPE_RECEIVED' => $this->t('WEBHOOK_STRIPE_RECEIVED'),
      'WEBHOOK_PAYPAL_RECEIVED' => $this->t('WEBHOOK_PAYPAL_RECEIVED'),
      'COLLECTIVE_MONTHLY_REPORT' => $this->t('COLLECTIVE_MONTHLY_REPORT'),
      'ACTIVATED_COLLECTIVE_AS_HOST' => $this->t('ACTIVATED_COLLECTIVE_AS_HOST'),
      'ACTIVATED_COLLECTIVE_AS_INDEPENDENT' => $this->t('ACTIVATED_COLLECTIVE_AS_INDEPENDENT'),
      'DEACTIVATED_COLLECTIVE_AS_HOST' => $this->t('DEACTIVATED_COLLECTIVE_AS_HOST'),
      'ADDED_FUND_TO_ORG' => $this->t('ADDED_FUND_TO_ORG'),
      'COLLECTIVE_TRANSACTION_PAID' => $this->t('COLLECTIVE_TRANSACTION_PAID'),
      'COLLECTIVE_USER_ADDED' => $this->t('COLLECTIVE_USER_ADDED'),
      'COLLECTIVE_VIRTUAL_CARD_ASSIGNED' => $this->t('COLLECTIVE_VIRTUAL_CARD_ASSIGNED'),
      'COLLECTIVE_VIRTUAL_CARD_CREATED' => $this->t('COLLECTIVE_VIRTUAL_CARD_CREATED'),
      'SUBSCRIPTION_CONFIRMED' => $this->t('SUBSCRIPTION_CONFIRMED'),
      'COLLECTIVE_COMMENT_CREATED' => $this->t('COLLECTIVE_COMMENT_CREATED'),
    ];
  }

  /**
   * Get all application types.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/ApplicationType
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function applicationTypes(): array {
    return [
      'API_KEY' => $this->t('API_KEY'),
      'OAUTH' => $this->t('OAUTH'),
    ];
  }

  /**
   * Get all captcha providers.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/CaptchaProvider
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function captchaProviders(): array {
    return [
      'HCAPTCHA' => $this->t('HCAPTCHA'),
      'RECAPTCHA' => $this->t('RECAPTCHA'),
    ];
  }

  /**
   * Get all collective feature statuses.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/CollectiveFeatureStatus
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function collectiveFeatureStatuses(): array {
    return [
      'ACTIVE' => $this->t('ACTIVE'),
      'AVAILABLE' => $this->t('AVAILABLE'),
      'DISABLED' => $this->t('DISABLED'),
      'UNSUPPORTED' => $this->t('UNSUPPORTED'),
    ];
  }

  /**
   * Get all connected account services.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/ConnectedAccountService
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function connectedAccountServices(): array {
    return [
      'paypal' => $this->t('paypal'),
      'stripe' => $this->t('stripe'),
      'stripe_customer' => $this->t('stripe_customer'),
      'github' => $this->t('github'),
      'twitter' => $this->t('twitter'),
      'transferwise' => $this->t('transferwise'),
      'privacy' => $this->t('privacy'),
      'thegivingblock' => $this->t('thegivingblock'),
    ];
  }

  /**
   * Get all contribution frequencies.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/ContributionFrequency
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function contributionFrequencies(): array {
    return [
      'MONTHLY' => $this->t('MONTHLY'),
      'YEARLY' => $this->t('YEARLY'),
      'ONETIME' => $this->t('ONETIME'),
    ];
  }

  /**
   * Get all contributor roles.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/ContributorRole
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function contributorRoles(): array {
    return [
      'ACCOUNTANT' => $this->t('Accountant'),
      'ADMIN' => $this->t('Administrator'),
      'ATTENDEE' => $this->t('Attendee'),
      'BACKER' => $this->t('Backer'),
      'CONNECTED_COLLECTIVE' => $this->t('Connected Collective'),
      'CONTRIBUTOR' => $this->t('Contributor'),
      'FOLLOWER' => $this->t('Follower'),
      'HOST' => $this->t('Host'),
      'MEMBER' => $this->t('Member'),
    ];
  }

  /**
   * Get all countries w/ iso codes.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/CountryISO
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function countryIsoCodes(): array {
    return CountryManager::getStandardList();
  }

  /**
   * Known Cryptocurrency types.
   *
   * Note: Seems like there isn't documentation for supported types.
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function cryptoCurrencyTypes(): array {
    return [
      'BTC' => $this->t('BitCoin'),
      'ETH' => $this->t('Ethereum'),
    ];
  }

  /**
   * List of OC currency types, without deprecated items.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/Currency
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function currencyTypes(): array {
    return [
      'USD' => $this->t('US Dollar'),
      'AED' => $this->t('UAE Dirham'),
      'AFN' => $this->t('Afghani'),
      'ALL' => $this->t('Lek'),
      'AMD' => $this->t('Armenian Dram'),
      'ANG' => $this->t('Netherlands Antillean Guilder'),
      'AOA' => $this->t('Kwanza'),
      'ARS' => $this->t('Argentine Peso'),
      'AUD' => $this->t('Australian Dollar'),
      'AWG' => $this->t('Aruban Florin'),
      'AZN' => $this->t('Azerbaijanian Manat'),
      'BAM' => $this->t('Convertible Mark'),
      'BBD' => $this->t('Barbados Dollar'),
      'BDT' => $this->t('Taka'),
      'BGN' => $this->t('Bulgarian Lev'),
      'BIF' => $this->t('Burundi Franc'),
      'BMD' => $this->t('Bermudian Dollar'),
      'BND' => $this->t('Brunei Dollar'),
      'BOB' => $this->t('Boliviano'),
      'BRL' => $this->t('Brazilian Real'),
      'BSD' => $this->t('Bahamian Dollar'),
      'BWP' => $this->t('Pula'),
      'BYN' => $this->t('Belarussian Ruble'),
      'BZD' => $this->t('Belize Dollar'),
      'CAD' => $this->t('Canadian Dollar'),
      'CDF' => $this->t('Congolese Franc'),
      'CHF' => $this->t('Swiss Franc'),
      'CLP' => $this->t('Chilean Peso'),
      'CNY' => $this->t('Yuan Renminbi'),
      'COP' => $this->t('Colombian Peso'),
      'CRC' => $this->t('Costa Rican Colon'),
      'CVE' => $this->t('Cabo Verde Escudo'),
      'CZK' => $this->t('Czech Koruna'),
      'DJF' => $this->t('Djibouti Franc'),
      'DKK' => $this->t('Danish Krone'),
      'DOP' => $this->t('Dominican Peso'),
      'DZD' => $this->t('Algerian Dinar'),
      'EGP' => $this->t('Egyptian Pound'),
      'ETB' => $this->t('Ethiopian Birr'),
      'EUR' => $this->t('Euro'),
      'FJD' => $this->t('Fiji Dollar'),
      'FKP' => $this->t('Falkland Islands Pound'),
      'GBP' => $this->t('Pound Sterling'),
      'GEL' => $this->t('Lari'),
      'GIP' => $this->t('Gibraltar Pound'),
      'GMD' => $this->t('Dalasi'),
      'GNF' => $this->t('Guinea Franc'),
      'GTQ' => $this->t('Quetzal'),
      'GYD' => $this->t('Guyana Dollar'),
      'HKD' => $this->t('Hong Kong Dollar'),
      'HNL' => $this->t('Lempira'),
      'HRK' => $this->t('Kuna'),
      'HTG' => $this->t('Gourde'),
      'HUF' => $this->t('Forint'),
      'IDR' => $this->t('Rupiah'),
      'ILS' => $this->t('New Israeli Sheqel'),
      'INR' => $this->t('Indian Rupee'),
      'ISK' => $this->t('Iceland Krona'),
      'JMD' => $this->t('Jamaican Dollar'),
      'JPY' => $this->t('Yen'),
      'KES' => $this->t('Kenyan Shilling'),
      'KGS' => $this->t('Som'),
      'KHR' => $this->t('Riel'),
      'KMF' => $this->t('Comoro Franc'),
      'KRW' => $this->t('Won'),
      'KYD' => $this->t('Cayman Islands Dollar'),
      'KZT' => $this->t('Tenge'),
      'LAK' => $this->t('Kip'),
      'LBP' => $this->t('Lebanese Pound'),
      'LKR' => $this->t('Sri Lanka Rupee'),
      'LRD' => $this->t('Liberian Dollar'),
      'LSL' => $this->t('Loti'),
      'MAD' => $this->t('Moroccan Dirham'),
      'MDL' => $this->t('Moldovan Leu'),
      'MGA' => $this->t('Malagasy Ariary'),
      'MKD' => $this->t('Denar'),
      'MMK' => $this->t('Kyat'),
      'MNT' => $this->t('Tugrik'),
      'MOP' => $this->t('Pataca'),
      'MUR' => $this->t('Mauritius Rupee'),
      'MVR' => $this->t('Rufiyaa'),
      'MWK' => $this->t('Kwacha'),
      'MXN' => $this->t('Mexican Peso'),
      'MYR' => $this->t('Malaysian Ringgit'),
      'MZN' => $this->t('Mozambique Metical'),
      'NAD' => $this->t('Namibia Dollar'),
      'NGN' => $this->t('Naira'),
      'NIO' => $this->t('Cordoba Oro'),
      'NOK' => $this->t('Norwegian Krone'),
      'NPR' => $this->t('Nepalese Rupee'),
      'NZD' => $this->t('New Zealand Dollar'),
      'PAB' => $this->t('Balboa'),
      'PEN' => $this->t('Nuevo Sol'),
      'PGK' => $this->t('Kina'),
      'PHP' => $this->t('Philippine Peso'),
      'PKR' => $this->t('Pakistan Rupee'),
      'PLN' => $this->t('Zloty'),
      'PYG' => $this->t('Guarani'),
      'QAR' => $this->t('Qatari Rial'),
      'RON' => $this->t('Romanian Leu'),
      'RSD' => $this->t('Serbian Dinar'),
      'RUB' => $this->t('Russian Ruble'),
      'RWF' => $this->t('Rwanda Franc'),
      'SAR' => $this->t('Saudi Riyal'),
      'SBD' => $this->t('Solomon Islands Dollar'),
      'SCR' => $this->t('Seychelles Rupee'),
      'SEK' => $this->t('Swedish Krona'),
      'SGD' => $this->t('Singapore Dollar'),
      'SHP' => $this->t('Saint Helena Pound'),
      'SLL' => $this->t('Leone'),
      'SOS' => $this->t('Somali Shilling'),
      'SRD' => $this->t('Surinam Dollar'),
      'SZL' => $this->t('Lilangeni'),
      'THB' => $this->t('Baht'),
      'TJS' => $this->t('Somoni'),
      'TOP' => $this->t('Pa’anga'),
      'TRY' => $this->t('Turkish Lira'),
      'TTD' => $this->t('Trinidad and Tobago Dollar'),
      'TWD' => $this->t('New Taiwan Dollar'),
      'TZS' => $this->t('Tanzanian Shilling'),
      'UAH' => $this->t('Hryvnia'),
      'UGX' => $this->t('Uganda Shilling'),
      'UYU' => $this->t('Peso Uruguayo'),
      'UZS' => $this->t('Uzbekistan Sum'),
      'VND' => $this->t('Dong'),
      'VUV' => $this->t('Vatu'),
      'WST' => $this->t('Tala'),
      'XAF' => $this->t('CFA Franc BEAC'),
      'XCD' => $this->t('East Caribbean Dollar'),
      'XOF' => $this->t('CFA Franc BCEAO'),
      'XPF' => $this->t('CFP Franc'),
      'YER' => $this->t('Yemeni Rial'),
      'ZAR' => $this->t('Rand'),
      'ZMW' => $this->t('Zambian Kwacha'),
    ];
  }

  /**
   * Get currency exchange rate source types.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/CurrencyExchangeRateSourceType
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function currencyExchangeRateSourceTypes(): array {
    return [
      'OPENCOLLECTIVE' => $this->t('Open Collective internal system, relying on caching and 3rd party APIs'),
      'PAYPAL' => $this->t('PayPal API'),
      'WISE' => $this->t('Wise API'),
    ];
  }

  /**
   * Get all expense currency sources.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/ExpenseCurrencySource
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function expenseCurrencySource(): array {
    return [
      'HOST' => $this->t('HOST'),
      'ACCOUNT' => $this->t('ACCOUNT'),
      'EXPENSE' => $this->t('EXPENSE'),
      'CREATED_BY_ACCOUNT' => $this->t('CREATED_BY_ACCOUNT'),
    ];
  }

  /**
   * Get all expense process actions.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/ExpenseProcessAction
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function expenseProcessActions(): array {
    return [
      'APPROVE' => $this->t('APPROVE'),
      'UNAPPROVE' => $this->t('UNAPPROVE'),
      'REJECT' => $this->t('REJECT'),
      'MARK_AS_UNPAID' => $this->t('MARK_AS_UNPAID'),
      'SCHEDULE_FOR_PAYMENT' => $this->t('SCHEDULE_FOR_PAYMENT'),
      'UNSCHEDULE_PAYMENT' => $this->t('UNSCHEDULE_PAYMENT'),
      'PAY' => $this->t('PAY'),
      'MARK_AS_SPAM' => $this->t('MARK_AS_SPAM'),
      'MARK_AS_INCOMPLETE' => $this->t('MARK_AS_INCOMPLETE'),
    ];
  }

  /**
   * Get all expense statuses.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/ExpenseStatus
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function expenseStatuses(): array {
    return [
      'DRAFT' => $this->t('DRAFT'),
      'UNVERIFIED' => $this->t('UNVERIFIED'),
      'PENDING' => $this->t('PENDING'),
      'INCOMPLETE' => $this->t('INCOMPLETE'),
      'APPROVED' => $this->t('APPROVED'),
      'REJECTED' => $this->t('REJECTED'),
      'PROCESSING' => $this->t('PROCESSING'),
      'ERROR' => $this->t('ERROR'),
      'PAID' => $this->t('PAID'),
      'SCHEDULED_FOR_PAYMENT' => $this->t('SCHEDULED_FOR_PAYMENT'),
      'SPAM' => $this->t('SPAM'),
      'CANCELED' => $this->t('CANCELED'),
    ];
  }

  /**
   * Get all expense status filters.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/ExpenseStatusFilter
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function expenseStatusFilters(): array {
    return [
      'DRAFT' => $this->t('DRAFT'),
      'UNVERIFIED' => $this->t('UNVERIFIED'),
      'PENDING' => $this->t('PENDING'),
      'INCOMPLETE' => $this->t('INCOMPLETE'),
      'APPROVED' => $this->t('APPROVED'),
      'REJECTED' => $this->t('REJECTED'),
      'PROCESSING' => $this->t('PROCESSING'),
      'ERROR' => $this->t('ERROR'),
      'PAID' => $this->t('PAID'),
      'SCHEDULED_FOR_PAYMENT' => $this->t('SCHEDULED_FOR_PAYMENT'),
      'SPAM' => $this->t('SPAM'),
      'CANCELED' => $this->t('CANCELED'),
      'READY_TO_PAY' => $this->t('READY_TO_PAY'),
    ];
  }

  /**
   * Get all expense types.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/ExpenseType
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function expenseTypes(): array {
    return [
      'INVOICE' => $this->t('INVOICE'),
      'RECEIPT' => $this->t('RECEIPT'),
      'FUNDING_REQUEST' => $this->t('FUNDING_REQUEST'),
      'GRANT' => $this->t('GRANT'),
      'UNCLASSIFIED' => $this->t('UNCLASSIFIED'),
      'CHARGE' => $this->t('CHARGE'),
      'SETTLEMENT' => $this->t('SETTLEMENT'),
    ];
  }

  /**
   * Get all fee payers.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/FeesPayer
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function feesPayers(): array {
    return [
      'COLLECTIVE' => $this->t('COLLECTIVE'),
      'PAYEE' => $this->t('PAYEE'),
    ];
  }

  /**
   * Get all host application statuses.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/HostApplicationStatus
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function hostApplicationStatuses(): array {
    return [
      'PENDING' => $this->t('PENDING'),
      'APPROVED' => $this->t('APPROVED'),
      'REJECTED' => $this->t('REJECTED'),
      'EXPIRED' => $this->t('EXPIRED'),
    ];
  }

  /**
   * Get all host fee structures.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/HostFeeStructure
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function hostFeeStructures(): array {
    return [
      'DEFAULT' => $this->t('DEFAULT'),
      'CUSTOM_FEE' => $this->t('CUSTOM_FEE'),
      'MONTHLY_RETAINER' => $this->t('MONTHLY_RETAINER'),
    ];
  }

  /**
   * Get all image formats.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/ImageFormat
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function imageFormats(): array {
    return [
      'txt' => $this->t('txt'),
      'png' => $this->t('png'),
      'jpg' => $this->t('jpg'),
      'gif' => $this->t('gif'),
      'svg' => $this->t('svg'),
    ];
  }

  /**
   * Get all legal document types.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/LegalDocumentType
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function legalDocumentTypes(): array {
    return [
      'US_TAX_FORM' => $this->t('US tax form (W9)'),
    ];
  }

  /**
   * OC member roles.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/MemberRole
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function memberRoles(): array {
    return [
      'ACCOUNTANT' => $this->t('Accountant'),
      'ADMIN' => $this->t('Administrator'),
      'ATTENDEE' => $this->t('Attendee'),
      'BACKER' => $this->t('Backer'),
      'CONNECTED_ACCOUNT' => $this->t('Connected Account'),
      'CONTRIBUTOR' => $this->t('Contributor'),
      'FOLLOWER' => $this->t('Follower'),
      'HOST' => $this->t('Host'),
      'MEMBER' => $this->t('Member'),
    ];
  }

  /**
   * Get all OAuth scopes.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/OAuthScope
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function oAuthScopes(): array {
    return [
      'account' => $this->t('account'),
      'applications' => $this->t('applications'),
      'connectedAccounts' => $this->t('connectedAccounts'),
      'conversations' => $this->t('conversations'),
      'email' => $this->t('email'),
      'expenses' => $this->t('expenses'),
      'host' => $this->t('host'),
      'incognito' => $this->t('incognito'),
      'orders' => $this->t('orders'),
      'root' => $this->t('root'),
      'transactions' => $this->t('transactions'),
      'updates' => $this->t('updates'),
      'virtualCards' => $this->t('virtualCards'),
      'webhooks' => $this->t('webhooks'),
    ];
  }

  /**
   * Get all order by field types.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/OrderByFieldType
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function orderByFieldTypes(): array {
    return [
      'CREATED_AT' => $this->t('CREATED_AT'),
      'MEMBER_COUNT' => $this->t('MEMBER_COUNT'),
      'TOTAL_CONTRIBUTED' => $this->t('TOTAL_CONTRIBUTED'),
      'ACTIVITY' => $this->t('ACTIVITY'),
      'RANK' => $this->t('RANK'),
    ];
  }

  /**
   * Get all order directions.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/OrderDirection
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function orderDirections(): array {
    return [
      'ASC' => $this->t('Ascending'),
      'DESC' => $this->t('Descending'),
    ];
  }

  /**
   * Get all order statuses.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/OrderStatus
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function orderStatuses(): array {
    return [
      'NEW' => $this->t('NEW'),
      'REQUIRE_CLIENT_CONFIRMATION' => $this->t('REQUIRE_CLIENT_CONFIRMATION'),
      'PAID' => $this->t('PAID'),
      'ERROR' => $this->t('ERROR'),
      'PROCESSING' => $this->t('PROCESSING'),
      'ACTIVE' => $this->t('ACTIVE'),
      'CANCELLED' => $this->t('CANCELLED'),
      'PENDING' => $this->t('PENDING'),
      'EXPIRED' => $this->t('EXPIRED'),
      'PLEDGED' => $this->t('PLEDGED'),
      'REJECTED' => $this->t('REJECTED'),
      'DISPUTED' => $this->t('DISPUTED'),
      'REFUNDED' => $this->t('REFUNDED'),
      'IN_REVIEW' => $this->t('IN_REVIEW'),
    ];
  }

  /**
   * Get all order tax types.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/OrderTaxType
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function orderTaxType(): array {
    return [
      'VAT' => $this->t('European Value Added Tax'),
      'GST' => $this->t('New Zealand Good and Services Tax'),
    ];
  }

  /**
   * Get all legacy payment method types.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/PaymentMethodLegacyType
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function paymentMethodLegacyTypes(): array {
    return [
      'ACCOUNT_BALANCE' => $this->t('Account Balance'),
      'ADDED_FUNDS' => $this->t('Added Funds'),
      'ALIPAY' => $this->t('Ali Pay'),
      'BACS_DEBIT' => $this->t('BACS Debit'),
      'BANCONTACT' => $this->t('BANCONTACT'),
      'BANK_TRANSFER' => $this->t('Bank Transfer'),
      'CREDIT_CARD' => $this->t('Credit card'),
      'CRYPTO' => $this->t('Crypto'),
      'GIFT_CARD' => $this->t('Gift card'),
      'PAYPAL' => $this->t('Paypal'),
      'PAYMENT_INTENT' => $this->t('Payment Intent'),
      'PREPAID_BUDGET' => $this->t('Prepaid Budget'),
      'SEPA_DEBIT' => $this->t('SEPA Debit'),
      'US_BANK_ACCOUNT' => $this->t('US Band Account'),
    ];
  }

  /**
   * Get all payment method services.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/PaymentMethodService
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function paymentMethodServices(): array {
    return [
      'OPENCOLLECTIVE' => $this->t('Open Collective'),
      'PAYPAL' => $this->t('Paypal'),
      'PREPAID' => $this->t('Prepaid'),
      'STRIPE' => $this->t('Stripe'),
      'THEGIVINGBLOCK' => $this->t('The Giving Block'),
    ];
  }

  /**
   * Payment method types.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/PaymentMethodType
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function paymentMethodTypes(): array {
    return [
      'ADAPTIVE' => $this->t('Adaptive'),
      'ALIPAY' => $this->t('Ali Pay'),
      'BACS_DEBIT' => $this->t('BACS Debit'),
      'BANCONTACT' => $this->t('BANCONTACT'),
      'COLLECTIVE' => $this->t('Collective'),
      'CREDITCARD' => $this->t('Credit card'),
      'CRYPTO' => $this->t('Crypto'),
      'GIFTCARD' => $this->t('Gift card'),
      'HOST' => $this->t('Host'),
      'MANUAL' => $this->t('Manual'),
      'PAYMENT' => $this->t('Payment'),
      'PAYMENT_INTENT' => $this->t('Payment Intent'),
      'PREPAID' => $this->t('Prepaid'),
      'SEPA_DEBIT' => $this->t('SEPA Debit'),
      'SUBSCRIPTION' => $this->t('Subscription'),
      'US_BANK_ACCOUNT' => $this->t('US Band Account'),
    ];
  }

  /**
   * Get all payout types.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/PayoutMethodType
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function payoutMethodTypes(): array {
    return [
      'OTHER' => $this->t('OTHER'),
      'PAYPAL' => $this->t('PAYPAL'),
      'BANK_ACCOUNT' => $this->t('BANK_ACCOUNT'),
      'ACCOUNT_BALANCE' => $this->t('ACCOUNT_BALANCE'),
      'CREDIT_CARD' => $this->t('CREDIT_CARD'),
    ];
  }

  /**
   * Get all policy applications.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/PolicyApplication
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function policyApplications(): array {
    return [
      'ALL_COLLECTIVES' => $this->t('ALL_COLLECTIVES'),
      'NEW_COLLECTIVES' => $this->t('NEW_COLLECTIVES'),
    ];
  }

  /**
   * Get all process host application actions.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/ProcessHostApplicationAction
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function processHostApplicationActions(): array {
    return [
      'APPROVE' => $this->t('APPROVE'),
      'REJECT' => $this->t('REJECT'),
      'SEND_PRIVATE_MESSAGE' => $this->t('SEND_PRIVATE_MESSAGE'),
      'SEND_PUBLIC_MESSAGE' => $this->t('SEND_PUBLIC_MESSAGE'),
    ];
  }

  /**
   * Get all process order actions.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/ProcessOrderAction
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function processOrderActions(): array {
    return [
      'MARK_AS_EXPIRED' => $this->t('MARK_AS_EXPIRED'),
      'MARK_AS_PAID' => $this->t('MARK_AS_PAID'),
    ];
  }

  /**
   * Get all recurring expense intervals.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/RecurringExpenseInterval
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function recurringExpenseIntervals(): array {
    return [
      'day' => $this->t('Day'),
      'week' => $this->t('Week'),
      'month' => $this->t('Month'),
      'quarter' => $this->t('Quarter'),
      'year' => $this->t('Year'),
    ];
  }

  /**
   * Get all security check levels.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/SecurityCheckLevel
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function securityCheckLevels(): array {
    return [
      'PASS' => $this->t('PASS'),
      'LOW' => $this->t('LOW'),
      'MEDIUM' => $this->t('MEDIUM'),
      'HIGH' => $this->t('HIGH'),
    ];
  }

  /**
   * Get all security check scopes.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/SecurityCheckScope
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function securityCheckScopes(): array {
    return [
      'USER' => $this->t('USER'),
      'COLLECTIVE' => $this->t('COLLECTIVE'),
      'PAYEE' => $this->t('PAYEE'),
      'PAYOUT_METHOD' => $this->t('PAYOUT_METHOD'),
    ];
  }

  /**
   * Get all social link types.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/SocialLinkType
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function socialLinkTypes(): array {
    return [
      'TWITTER' => $this->t('TWITTER'),
      'TUMBLR' => $this->t('TUMBLR'),
      'MASTODON' => $this->t('MASTODON'),
      'MATTERMOST' => $this->t('MATTERMOST'),
      'SLACK' => $this->t('SLACK'),
      'LINKEDIN' => $this->t('LINKEDIN'),
      'MEETUP' => $this->t('MEETUP'),
      'FACEBOOK' => $this->t('FACEBOOK'),
      'INSTAGRAM' => $this->t('INSTAGRAM'),
      'DISCORD' => $this->t('DISCORD'),
      'YOUTUBE' => $this->t('YOUTUBE'),
      'GITHUB' => $this->t('GITHUB'),
      'GITLAB' => $this->t('GITLAB'),
      'GIT' => $this->t('GIT'),
      'WEBSITE' => $this->t('WEBSITE'),
      'DISCOURSE' => $this->t('DISCOURSE'),
      'PIXELFED' => $this->t('PIXELFED'),
      'GHOST' => $this->t('GHOST'),
      'PEERTUBE' => $this->t('PEERTUBE'),
      'TIKTOK' => $this->t('TIKTOK'),
    ];
  }

  /**
   * Get all tag search operators.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/TagSearchOperator
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function tagSearchOperators(): array {
    return [
      'AND' => $this->t('And'),
      'OR' => $this->t('Or'),
    ];
  }

  /**
   * Get all tax types.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/TaxType
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function taxTypes(): array {
    return [
      'VAT' => $this->t('European Value Added Tax'),
      'GST' => $this->t('New Zealand Good and Services Tax'),
    ];
  }

  /**
   * Get all tier amount types.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/TierAmountType
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function tierAmountTypes(): array {
    return [
      'FIXED' => $this->t('Fixed'),
      'FLEXIBLE' => $this->t('Flexible'),
    ];
  }

  /**
   * Get all tier frequencies.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/TierFrequency
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function tierFrequencies(): array {
    return [
      'MONTHLY' => $this->t('MONTHLY'),
      'YEARLY' => $this->t('YEARLY'),
      'ONETIME' => $this->t('ONETIME'),
      'FLEXIBLE' => $this->t('FLEXIBLE'),
    ];
  }

  /**
   * Get all tier intervals.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/TierInterval
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function tierIntervals(): array {
    return [
      'month' => $this->t('Month'),
      'year' => $this->t('Year'),
      'flexible' => $this->t('Flexible'),
    ];
  }

  /**
   * Get all tier types.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/TierType
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function tierTypes(): array {
    return [
      'TIER' => $this->t('TIER'),
      'MEMBERSHIP' => $this->t('MEMBERSHIP'),
      'DONATION' => $this->t('DONATION'),
      'TICKET' => $this->t('TICKET'),
      'SERVICE' => $this->t('SERVICE'),
      'PRODUCT' => $this->t('PRODUCT'),
    ];
  }

  /**
   * Get all time units.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/TimeUnit
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function timeUnits(): array {
    return [
      'SECOND' => $this->t('SECOND'),
      'MINUTE' => $this->t('MINUTE'),
      'HOUR' => $this->t('HOUR'),
      'DAY' => $this->t('DAY'),
      'WEEK' => $this->t('WEEK'),
      'MONTH' => $this->t('MONTH'),
      'YEAR' => $this->t('YEAR'),
    ];
  }

  /**
   * Get all transaction kinds.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/TransactionKind
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function transactionKinds(): array {
    return [
      'ADDED_FUNDS' => $this->t('ADDED_FUNDS'),
      'BALANCE_TRANSFER' => $this->t('BALANCE_TRANSFER'),
      'CONTRIBUTION' => $this->t('CONTRIBUTION'),
      'EXPENSE' => $this->t('EXPENSE'),
      'HOST_FEE' => $this->t('HOST_FEE'),
      'HOST_FEE_SHARE' => $this->t('HOST_FEE_SHARE'),
      'HOST_FEE_SHARE_DEBT' => $this->t('HOST_FEE_SHARE_DEBT'),
      'PAYMENT_PROCESSOR_COVER' => $this->t('PAYMENT_PROCESSOR_COVER'),
      'PAYMENT_PROCESSOR_DISPUTE_FEE' => $this->t('PAYMENT_PROCESSOR_DISPUTE_FEE'),
      'PAYMENT_PROCESSOR_FEE' => $this->t('PAYMENT_PROCESSOR_FEE'),
      'PLATFORM_FEE' => $this->t('PLATFORM_FEE'),
      'PLATFORM_TIP' => $this->t('PLATFORM_TIP'),
      'PLATFORM_TIP_DEBT' => $this->t('PLATFORM_TIP_DEBT'),
      'PREPAID_PAYMENT_METHOD' => $this->t('PREPAID_PAYMENT_METHOD'),
    ];
  }

  /**
   * Get all transaction settlement statuses.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/TransactionSettlementStatus
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function transactionSettlementStatuses(): array {
    return [
      'OWED' => $this->t('OWED'),
      'INVOICED' => $this->t('INVOICED'),
      'SETTLED' => $this->t('SETTLED'),
    ];
  }

  /**
   * Get all transaction types.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/TransactionType
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function transactionTypes(): array {
    return [
      'DEBIT' => $this->t('DEBIT'),
      'CREDIT' => $this->t('CREDIT'),
    ];
  }

  /**
   * Get all update audiences.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/UpdateAudience
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function updateAudiences(): array {
    return [
      'ALL' => $this->t('ALL'),
      'COLLECTIVE_ADMINS' => $this->t('COLLECTIVE_ADMINS'),
      'FINANCIAL_CONTRIBUTORS' => $this->t('FINANCIAL_CONTRIBUTORS'),
      'NO_ONE' => $this->t('NO_ONE'),
    ];
  }

  /**
   * Get all update datetime fields.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/UpdateDateTimeField
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function updateDateTimeFields(): array {
    return [
      'CREATED_AT' => $this->t('CREATED_AT'),
      'PUBLISHED_AT' => $this->t('PUBLISHED_AT'),
    ];
  }

  /**
   * Get all virtual card limit intervals.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/VirtualCardLimitInterval
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function virtualCardLimitIntervals(): array {
    return [
      'PER_AUTHORIZATION' => $this->t('PER_AUTHORIZATION'),
      'DAILY' => $this->t('DAILY'),
      'WEEKLY' => $this->t('WEEKLY'),
      'MONTHLY' => $this->t('MONTHLY'),
      'YEARLY' => $this->t('YEARLY'),
      'ALL_TIME' => $this->t('ALL_TIME'),
    ];
  }

  /**
   * Get all virtual card providers.
   *
   * @link https://graphql-docs-v2.opencollective.com/types/VirtualCardLimitInterval
   *
   * @return array
   *   Keys are codes, value is label.
   */
  public function virtualCardProviders(): array {
    return [
      'PRIVACY' => $this->t('PRIVACY'),
      'STRIPE' => $this->t('STRIPE'),
    ];
  }

}
