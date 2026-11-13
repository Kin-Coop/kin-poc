<?php

declare(strict_types=1);

namespace Drupal\kin_bank_import\Cleaner;

/**
 * Cleans a Co-operative Bank "List of transactions" CSV export.
 *
 * Plain PHP with no Drupal dependencies. The rules are in the module's SPEC.md:
 *
 * - Stage 1: drop everything above the column header row ("List of
 *   transactions", "Transactions List:"), the "Date and Time:" footer, and
 *   blank lines.
 * - Stage 2: an empty "Bank reference" becomes "NOREF".
 * - Stage 3: rows whose Transaction date, Bank reference, Customer reference
 *   and Amount (GBP) exactly match an earlier row anywhere in the file get
 *   "-D1", "-D2", ... appended to their Bank reference. The first occurrence
 *   is left unchanged. Runs after stage 2, so NOREF rows can become NOREF-D1.
 *
 * The column layout (including the export's empty first column and two empty
 * trailing columns) is preserved exactly.
 */
final class CoopBankCsvCleaner {

  public const COL_DATE = 'Transaction date';
  public const COL_BANK_REF = 'Bank reference';
  public const COL_CUSTOMER_REF = 'Customer reference';
  public const COL_AMOUNT = 'Amount (GBP)';

  public const NO_REFERENCE = 'NOREF';

  /**
   * Columns that must be present in the header row.
   */
  private const REQUIRED_COLUMNS = [
    self::COL_DATE,
    'Account Number',
    self::COL_BANK_REF,
    'Type of payment',
    self::COL_CUSTOMER_REF,
    self::COL_AMOUNT,
    'Additional Info',
  ];

  /**
   * First-cell prefix of the export's footer line.
   */
  private const FOOTER_PREFIX = 'Date and Time:';

  /**
   * First cells of the report title lines above the header.
   */
  private const TITLE_LINES = ['List of transactions', 'Transactions List:'];

  /**
   * Runs all three stages and returns the cleaned CSV text.
   *
   * @throws \Drupal\kin_bank_import\Cleaner\InvalidBankCsvException
   */
  public function clean(string $csv): string {
    $table = $this->stripReportLines($this->parse($csv));
    $table = $this->fillMissingReferences($table);
    $table = $this->markDuplicates($table);
    return $this->toCsv($table);
  }

  /**
   * Parses CSV text into rows of string cells.
   *
   * Accepts LF or CRLF line endings and strips a UTF-8 byte order mark.
   *
   * @return list<list<string>>
   */
  public function parse(string $csv): array {
    if (str_starts_with($csv, "\xEF\xBB\xBF")) {
      $csv = substr($csv, 3);
    }
    if (trim($csv) === '') {
      throw new InvalidBankCsvException('The file is empty.');
    }

    $stream = fopen('php://memory', 'r+');
    fwrite($stream, $csv);
    rewind($stream);
    $rows = [];
    while (($row = fgetcsv($stream, NULL, ',', '"', '')) !== FALSE) {
      // fgetcsv() returns [NULL] for a blank line.
      $rows[] = array_map(static fn($cell) => (string) $cell, $row);
    }
    fclose($stream);
    return $rows;
  }

  /**
   * Stage 1: removes report title lines, the footer and blank lines.
   *
   * @param list<list<string>> $rows
   *   Parsed rows of the whole export.
   *
   * @return array{header: list<string>, rows: list<list<string>>}
   *   The header row and the transaction rows.
   *
   * @throws \Drupal\kin_bank_import\Cleaner\InvalidBankCsvException
   */
  public function stripReportLines(array $rows): array {
    $headerIndex = NULL;
    foreach ($rows as $i => $row) {
      if ($this->isHeaderRow($row)) {
        $headerIndex = $i;
        break;
      }
    }
    if ($headerIndex === NULL) {
      throw new InvalidBankCsvException(sprintf(
        'This does not look like a Co-operative Bank transaction export: no header row with the columns %s was found.',
        implode(', ', self::REQUIRED_COLUMNS)
      ));
    }

    $header = $rows[$headerIndex];
    $width = count($header);
    $data = [];
    foreach (array_slice($rows, $headerIndex + 1, NULL, TRUE) as $i => $row) {
      if ($this->isBlankRow($row) || $this->isFooterRow($row)) {
        continue;
      }
      if ($this->isHeaderRow($row) || $this->isTitleRow($row)) {
        throw new InvalidBankCsvException(sprintf(
          'Line %d repeats the report title or column headings. Exports that span several pages are not supported yet.',
          $i + 1
        ));
      }
      if (count($row) !== $width) {
        throw new InvalidBankCsvException(sprintf(
          'Line %d has %d columns but the header has %d. The file may not be a standard export, or may span several pages.',
          $i + 1,
          count($row),
          $width
        ));
      }
      $data[] = $row;
    }

    return ['header' => $header, 'rows' => $data];
  }

  /**
   * Stage 2: puts NOREF in every empty Bank reference.
   *
   * @param array{header: list<string>, rows: list<list<string>>} $table
   *
   * @return array{header: list<string>, rows: list<list<string>>}
   */
  public function fillMissingReferences(array $table): array {
    $ref = $this->columnIndex($table['header'], self::COL_BANK_REF);
    foreach ($table['rows'] as &$row) {
      if (trim($row[$ref]) === '') {
        $row[$ref] = self::NO_REFERENCE;
      }
    }
    unset($row);
    return $table;
  }

  /**
   * Stage 3: suffixes repeat occurrences of identical transactions.
   *
   * @param array{header: list<string>, rows: list<list<string>>} $table
   *
   * @return array{header: list<string>, rows: list<list<string>>}
   */
  public function markDuplicates(array $table): array {
    $header = $table['header'];
    $date = $this->columnIndex($header, self::COL_DATE);
    $ref = $this->columnIndex($header, self::COL_BANK_REF);
    $customer = $this->columnIndex($header, self::COL_CUSTOMER_REF);
    $amount = $this->columnIndex($header, self::COL_AMOUNT);

    $seen = [];
    foreach ($table['rows'] as &$row) {
      // Key on the values before any suffix is added.
      $key = json_encode([$row[$date], $row[$ref], $row[$customer], $row[$amount]]);
      if (isset($seen[$key])) {
        $row[$ref] .= '-D' . $seen[$key];
        $seen[$key]++;
      }
      else {
        $seen[$key] = 1;
      }
    }
    unset($row);
    return $table;
  }

  /**
   * Writes the header and rows back out as CSV text with LF line endings.
   *
   * Fields are quoted only when they contain a comma, quote or line break, so
   * unremarkable rows come out byte-for-byte as they went in.
   *
   * @param array{header: list<string>, rows: list<list<string>>} $table
   */
  public function toCsv(array $table): string {
    $out = '';
    foreach ([$table['header'], ...$table['rows']] as $row) {
      $out .= implode(',', array_map($this->encodeField(...), $row)) . "\n";
    }
    return $out;
  }

  private function encodeField(string $value): string {
    if (strpbrk($value, ",\"\r\n") === FALSE) {
      return $value;
    }
    return '"' . str_replace('"', '""', $value) . '"';
  }

  /**
   * @param list<string> $row
   */
  private function isHeaderRow(array $row): bool {
    $cells = array_map('trim', $row);
    foreach (self::REQUIRED_COLUMNS as $column) {
      if (!in_array($column, $cells, TRUE)) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * @param list<string> $row
   */
  private function isBlankRow(array $row): bool {
    return trim(implode('', $row)) === '';
  }

  /**
   * @param list<string> $row
   */
  private function isFooterRow(array $row): bool {
    return str_starts_with(trim($row[0] ?? ''), self::FOOTER_PREFIX);
  }

  /**
   * @param list<string> $row
   */
  private function isTitleRow(array $row): bool {
    return in_array(trim($row[0] ?? ''), self::TITLE_LINES, TRUE);
  }

  /**
   * @param list<string> $header
   */
  private function columnIndex(array $header, string $column): int {
    $index = array_search($column, array_map('trim', $header), TRUE);
    if ($index === FALSE) {
      throw new InvalidBankCsvException(sprintf('Missing column "%s".', $column));
    }
    return $index;
  }

}
