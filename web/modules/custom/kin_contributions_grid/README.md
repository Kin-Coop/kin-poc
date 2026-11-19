# Kin contributions by date

A small Drupal module that adds a page at `/member/group/{group}/contributionsbydate`. It shows a group's transactions as a grid: one row per member, and one column per week or month.

- Every member appears as a row, including people with nothing in the range.
- Each payment shows separately in its cell, with its date, request type and a "View" link for the note.
- Gifts out show in red with a minus sign. Every total is money in minus money out.
- It always shows 6 weeks (or 6 months), ending with the current one. The "Up to" date and the Earlier/Later buttons move through time 6 at a time.
- On narrow screens the grid scrolls sideways inside the card, with the Name and Total columns kept in place.
- The filters match the history page: Contributor and Request type.

No CiviCRM settings, Views or database tables are changed. Uninstalling the module removes the page and nothing else.

## Install in DDEV

1. Copy the `kin_contributions_grid` folder into `web/modules/custom/` (next to your other custom modules).
2. Run:

   ```
   ddev drush en kin_contributions_grid -y
   ddev drush cr
   ```

3. Open `/member/group/2671/contributionsbydate` on your DDEV site.

To remove it: `ddev drush pmu kin_contributions_grid -y`

## Add the buttons (two small Views edits)

Go to Structure › Views › Contributions › Edit. Both edits are in a header text area. If a text editor toolbar appears, switch it to "Source" before pasting, so it doesn't strip the `data-bs-` attributes.

**1. Group page (display: Contributions Block).** Under Header, open "Global: Text area". Replace the last line,

```
<a class="btn btn-secondary" href="{{ id }}/contributions">See all transactions</a></div>
```

with:

```
<div class="d-flex gap-2"><a class="btn btn-secondary" href="{{ id }}/contributionsbydate">By date</a>
<a class="btn btn-secondary" href="{{ id }}/contributions">See all transactions</a></div></div>
```

**2. History page (display: Contributions Page).** Under Header, open "Global: Text area". Replace the last line,

```
<a class="btn btn-secondary" href="/member/group/{{ id }}"><i class="bi bi-arrow-left"></i> Back to group page</a></div>
```

with:

```
<div class="d-flex gap-2"><a class="btn btn-secondary" href="/member/group/{{ id }}/contributionsbydate">By date</a>
<a class="btn btn-secondary" href="/member/group/{{ id }}"><i class="bi bi-arrow-left"></i> Back to group page</a></div></div>
```

Click Apply, then Save the view.

## Check it works

These use the test group's transactions, checked during the week of 16 November 2026.

The "Total to date" column is each person's real all-time total, whichever weeks are on screen. The bottom row shows each week's (or month's) total.

- [ ] The default weekly view shows w/c 12.10.26 to w/c 16.11.26: Rob £50.00 in w/c 12.10 and 09.11, and Guillermo £50.00 in w/c 19.10. There is no "Later" button.
- [ ] On every view, Total to date reads Rob £250.00, Guillermo £200.00 and Group £450.00, matching the history page.
- [ ] "Earlier 6 weeks" shows w/c 31.08 to w/c 05.10: Rob in 14.09 and Guillermo in 21.09.
- [ ] "Earlier 6 weeks" again shows w/c 20.07 to w/c 24.08: Guillermo has two separate £50.00 lines in w/c 24.08 (one with a note), and Rob has £50.00 in 10.08.
- [ ] The monthly view shows Jun to Nov 2026, with two lines in Guillermo's August cell.
- [ ] Typing "Rob" in Contributor shows only Rob's row, and Group total to date becomes £250.00.
- [ ] A member with no payments shows "–" in every cell and a £0.00 total.
- [ ] A gift out shows in red with a minus sign and reduces the totals.
- [ ] The note "View" links open.
- [ ] Logged out, the page says access denied. As a member of a different group, it says access denied too.

## Things to check (assumptions in the code)

All of these are at the top of `src/Controller/ContributionsGridController.php`.

1. **How members are linked to a group.** The code looks for CiviCRM relationships called "Household Member of" or "Head of Household for" between a person and the group's Household contact. If Kin links members another way, change `MEMBER_RELATIONSHIPS`, or tell Claude how it works. If neither relationship type exists, the page falls back to everyone who has ever paid into or out of the group.
2. **Only Completed transactions count.** The history list also shows Pending and Partially paid transactions, but the group total block counts Completed only. This page follows the total block.
3. **Request type is a single choice.** The filter assumes custom field 83 holds one value per transaction.
4. **No pager.** All members show on one page, which is fine for groups of up to a couple of hundred people.

## Who can see the page

- CiviCRM admins (the "administer CiviCRM" permission).
- Logged-in people who are members of the group, or who have ever paid into or out of it.
- Nobody else, including logged-out visitors.

## Notes for Ben

- Data comes from the same tables as the Contributions view: `civicrm_value_kin_contribut_9` (`household_25`, `note_65`) and `civicrm_value_group_reward_31` (`reward_type_83`). Amounts are summed as stored, because gifts are already negative.
- CiviCRM is started with `\Drupal::service('civicrm')->initialize()`. Lookups use APIv4 with permission checks off, and the transaction query uses `CRM_Core_DAO::executeQuery` with bound parameters.
- The page is never cached (`max-age 0`), because it's per-user financial data.
- The existing Contributions view has `access: none`. Worth checking whether something else protects `/member/group/*/contributions`, because the view itself doesn't.
