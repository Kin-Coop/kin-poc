# KinpaymentsPayment — Contribution Matching

This document covers the matching logic added to the `kinpayments` extension.

---

## Files added / changed

| File | Purpose |
|---|---|
| `schema/KinpaymentsPayment.entityType.php` | Schema — adds optional `match_score` field (0–100) |
| `Civi/Kinpayments/PaymentMatcher.php` | Core matching & scoring service |
| `Civi/Api4/Action/KinpaymentsPayment/MatchPaymentsAction.php` | APIv4 action class |
| `Civi/Api4/KinpaymentsPayment.php` | APIv4 entity — exposes `matchPayments()` |
| `api/v3/KinpaymentsPayment.php` | APIv3 wrapper for Scheduled Jobs / drush |
| `kinpayments.php` | Extension hooks — auto-trigger after CSV import |

---

## How matching works

### 1 — Fast path: bank account number (most reliable)

If `customer_account_number` (the sort-code + account concatenation from the CSV
`Additional Info` column) has already been stored on the contact as
`Kin_Groups.Bank_Number` (custom_154), the matcher uses this to look up the
contact directly, then filters contributions by amount and date. No fuzzy
logic is needed.

### 2 — Scored candidate search

For each pending `KinpaymentsPayment` record, contributions are fetched that:
- have the **same amount** (hard requirement — no score awarded, just a gate)
- have a `receive_date` **within ±5 days** of the payment `datetime`
- optionally belong to the contact whose ID appears in the `bank_reference` prefix

Each candidate is scored out of 100:

| Signal | Max pts | Notes |
|---|---|---|
| Bank reference exact match to `custom_61` | 40 | Strongest signal |
| Contact ID prefix in bank reference | 15 | e.g. `518-1654R` → contact 518 |
| Account number matches `custom_154` | 30 | Reliable once populated |
| Date proximity (±5 days) | 15 | Full 15 for same day; linear decay |
| Customer reference name similarity | 15 | Handles initials, surname-first, fuzzy |

**Total: 115 theoretical max** — capped at 100.

### Thresholds

| Score | Action |
|---|---|
| ≥ 60 | **Auto-match** — populates `contribution_id`, `contact_id`, sets status to Matched (3) |
| 30–59 | **Pending review** — score stored, status unchanged; human checks it |
| < 30 | **No match** — status set to Not Matched (2), score stored |

### After a match

- `KinpaymentsPayment.contribution_id` and `contact_id` are populated.
- `KinpaymentsPayment.payment_status_id` → **3 (Matched)**.
- `KinpaymentsPayment.match_score` → computed score.
- If the matched Contribution was **Pending** (status 2), it is updated to **Completed** (status 1).
- If the contact's `Kin_Groups.Bank_Number` (`custom_154`) is empty, it is **populated** from `customer_account_number`. On all future imports for this contact, the fast path applies.

---

## Name matching detail

Bank customer references are typically surname-first with initials, e.g. `MACKAY E`.
The matcher:
1. Compares the first token against the contact's `last_name` (allows up to 2 edit-distance).
2. Checks remaining tokens against `first_name` — either full match or initial match.
3. Falls back to `similar_text()` as a catch-all percentage.

---

## Running the matcher

### Automatically after CSV import

The extension hooks into CiviCRM's `postProcess` event and
`hook_civicrm_import_post_import`. After a successful import of
`KinpaymentsPayment` records, matching runs automatically.

If the CSV Import to API extension fires a different hook, use the
**Scheduled Job** approach below as a reliable fallback.

### As a Scheduled Job

1. Go to **Administer → System Settings → Scheduled Jobs**.
2. Add a new job:
   - **Entity**: `KinpaymentsPayment`
   - **Action**: `match_payments`
   - **Parameters**: `include_unmatched=0`
   - **Frequency**: As needed (e.g. Hourly, or triggered manually).

To also re-process previously unmatched records:

```
include_unmatched=1
```

### Via drush

```bash
# Process pending only
drush -r /path/to/drupal cvapi KinpaymentsPayment.match_payments

# Also reprocess unmatched
drush -r /path/to/drupal cvapi KinpaymentsPayment.match_payments include_unmatched=1

# Dry run (no writes)
drush -r /path/to/drupal cvapi KinpaymentsPayment.match_payments dry_run=1
```

### Via APIv4 in PHP

```php
$result = \Civi\Api4\KinpaymentsPayment::matchPayments()
  ->setIncludeUnmatched(true)
  ->execute()
  ->first();

// $result = ['matched' => N, 'unmatched' => N, 'pending' => N, 'errors' => N]
```

---

## Schema change — `match_score`

The updated `KinpaymentsPayment.entityType.php` adds a `match_score` field
(`tinyint unsigned`, 0–100). After editing the schema file, rebuild with:

```bash
drush -r /path/to/drupal civicrm-upgrade-db
# or via the UI: Administer → System Settings → Cleanup Caches
```

---

## Troubleshooting

**No candidates found for a payment**
- The amount must match exactly — check for pence rounding in the CSV.
- The date window is ±5 days — contributions outside this window won't be found.

**Score is high but no match made**
- If `match_score` is between 30–59 it sits in pending for manual review. Lower
  `SCORE_AUTO_MATCH` in `PaymentMatcher.php` if you trust your data more.

**Bank reference doesn't match `custom_61`**
- This is expected for some records. The other signals (amount, date, name, bank
  number) should still get you to ≥ 60 for clear cases.

**Duplicate `contribution_id` conflict**
- `contribution_id` has a UNIQUE index on `civicrm_kinpayments_payment`. If a
  contribution is already linked to another payment, the update will fail with a
  duplicate-key error logged to `civicrm.log`. This prevents two payments from
  claiming the same contribution.
