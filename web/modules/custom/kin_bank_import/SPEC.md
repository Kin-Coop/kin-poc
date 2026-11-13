# Co-operative Bank CSV cleaning rules

Source: the annotated sample supplied on 2026-11-12, kept verbatim as
`tests/fixtures/spec-original-upload.csv`. The fixtures `sample-input.csv`,
`sample-expected-stage1.csv`, `sample-expected-stage2.csv` and
`sample-expected.csv` are cut from it.

## Input

Export from Co-operative Bank online banking ("List of transactions"):

```
List of transactions,,,,,,,,,
Transactions List:,,,,,,,,,
,Transaction date,Account Number,Bank reference,Type of payment,Customer reference,Amount (GBP),Additional Info,,
,03/11/2026,0892996735513800,,Faster Payment,Fake Name 1,2.97,60837168472244,,
...
Date and Time: ,,03/11/2026 11:37 AM,,,,,,Page 1 of, 1
```

## Stage 1: remove report lines

Remove "List of transactions", "Transactions List:" and the final
"Date and Time:" line.

Implementation: lines are recognised **by content**. Everything above the
column header row is dropped. "Date and Time:" footer lines and blank lines
are dropped. If no header row with all seven named columns is found, the file
is rejected with an error. Any other unexpected line (wrong column count, or
repeated title/header lines from a multi-page export) also rejects the file.

## Stage 2: fill missing references

An empty "Bank reference" becomes `NOREF`.

## Stage 3: mark duplicates

Rows with identical **Transaction date, Bank reference, Customer reference and
Amount (GBP)** are duplicates. The first occurrence is unchanged. Each later
one gets `-D1`, `-D2`, ... appended to its Bank reference.

Decisions confirmed on 2026-11-12:

- Stage 3 runs after stage 2, so duplicate no-reference rows become
  `NOREF`, `NOREF-D1`, `NOREF-D2`.
- Duplicates are matched **anywhere in the file**, not just on consecutive
  rows. Counting is per group of matching rows.
- The output keeps the export's exact column layout, including the empty
  first column and two empty trailing columns.

## Implementation defaults (not yet confirmed)

- Values are compared as exact strings: `6.0` and `6.00` are different.
- A Bank reference containing only spaces counts as empty (becomes `NOREF`).
- Input may be LF or CRLF, with or without a UTF-8 BOM. Output is UTF-8,
  no BOM, LF line endings.
- Output fields are quoted only if they contain a comma, quote or line break.
