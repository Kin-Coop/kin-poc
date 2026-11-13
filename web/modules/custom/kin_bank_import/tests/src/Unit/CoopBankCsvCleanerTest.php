<?php

declare(strict_types=1);

namespace Drupal\Tests\kin_bank_import\Unit;

use Drupal\kin_bank_import\Cleaner\CoopBankCsvCleaner;
use Drupal\kin_bank_import\Cleaner\InvalidBankCsvException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Tests the Co-operative Bank CSV cleaner against SPEC.md.
 *
 * Extends the plain PHPUnit TestCase (not Drupal's UnitTestCase) because the
 * cleaner has no Drupal dependencies; it runs under Drupal's PHPUnit setup or
 * standalone via phpunit.standalone.xml.
 */
#[CoversClass(CoopBankCsvCleaner::class)]
#[Group('kin_bank_import')]
final class CoopBankCsvCleanerTest extends TestCase {

  private const HEADER = ',Transaction date,Account Number,Bank reference,Type of payment,Customer reference,Amount (GBP),Additional Info,,';
  private const TITLE = "List of transactions,,,,,,,,,\nTransactions List:,,,,,,,,,\n";
  private const FOOTER = "Date and Time: ,,03/11/2026 11:37 AM,,,,,,Page 1 of, 1\n";

  private CoopBankCsvCleaner $cleaner;

  protected function setUp(): void {
    $this->cleaner = new CoopBankCsvCleaner();
  }

  private static function fixture(string $name): string {
    return file_get_contents(__DIR__ . '/../../fixtures/' . $name);
  }

  /**
   * Wraps data lines in a realistic export (title lines, header, footer).
   */
  private static function export(string ...$lines): string {
    return self::TITLE . self::HEADER . "\n" . implode("\n", $lines) . "\n" . self::FOOTER;
  }

  public function testSampleStage1(): void {
    $table = $this->cleaner->stripReportLines($this->cleaner->parse(self::fixture('sample-input.csv')));
    $this->assertSame(self::fixture('sample-expected-stage1.csv'), $this->cleaner->toCsv($table));
  }

  public function testSampleStage2(): void {
    $table = $this->cleaner->stripReportLines($this->cleaner->parse(self::fixture('sample-input.csv')));
    $table = $this->cleaner->fillMissingReferences($table);
    $this->assertSame(self::fixture('sample-expected-stage2.csv'), $this->cleaner->toCsv($table));
  }

  public function testSampleFullClean(): void {
    $this->assertSame(
      self::fixture('sample-expected.csv'),
      $this->cleaner->clean(self::fixture('sample-input.csv'))
    );
  }

  public function testCleaningIsIdempotentOnAlreadyStrippedInput(): void {
    // Stage 1 output (no title/footer) still cleans to the same result.
    $this->assertSame(
      self::fixture('sample-expected.csv'),
      $this->cleaner->clean(self::fixture('sample-expected-stage1.csv'))
    );
  }

  public function testCrlfLineEndingsBomAndTrailingBlankLines(): void {
    $input = "\xEF\xBB\xBF" . str_replace("\n", "\r\n", self::fixture('sample-input.csv')) . "\r\n\r\n";
    $this->assertSame(self::fixture('sample-expected.csv'), $this->cleaner->clean($input));
  }

  public function testNorefRowsAreDeduplicatedToo(): void {
    $out = $this->cleaner->clean(self::export(
      ',01/11/2026,1,,Faster Payment,Jo,5.00,111,,',
      ',01/11/2026,1,,Faster Payment,Jo,5.00,222,,',
    ));
    $this->assertStringContainsString(',01/11/2026,1,NOREF,Faster Payment,Jo,5.00,111,,', $out);
    $this->assertStringContainsString(',01/11/2026,1,NOREF-D1,Faster Payment,Jo,5.00,222,,', $out);
  }

  public function testNonAdjacentDuplicatesAreCountedPerGroup(): void {
    $out = $this->cleaner->clean(self::export(
      ',01/11/2026,1,A,FP,Jo,5.00,1,,',
      ',01/11/2026,1,B,FP,Al,7.00,2,,',
      ',01/11/2026,1,A,FP,Jo,5.00,3,,',
      ',01/11/2026,1,B,FP,Al,7.00,4,,',
      ',01/11/2026,1,A,FP,Jo,5.00,5,,',
    ));
    $refs = array_map(
      static fn(string $line) => explode(',', $line)[3],
      array_slice(explode("\n", trim($out)), 1)
    );
    $this->assertSame(['A', 'B', 'A-D1', 'B-D1', 'A-D2'], $refs);
  }

  public function testOnlyTheFourKeyColumnsDecideDuplicates(): void {
    // Account Number, Type of payment and Additional Info differ: still dupes.
    $out = $this->cleaner->clean(self::export(
      ',01/11/2026,1,A,Faster Payment,Jo,5.00,1,,',
      ',01/11/2026,2,A,Standing Order,Jo,5.00,2,,',
    ));
    $this->assertStringContainsString(',01/11/2026,2,A-D1,Standing Order,Jo,5.00,2,,', $out);
  }

  /**
   * Any difference in a key column means the rows are not duplicates.
   */
  public function testDifferentKeyValuesAreNotDuplicates(): void {
    $out = $this->cleaner->clean(self::export(
      ',01/11/2026,1,A,FP,Jo,5.00,1,,',
      ',02/11/2026,1,A,FP,Jo,5.00,1,,',
      ',01/11/2026,1,B,FP,Jo,5.00,1,,',
      ',01/11/2026,1,A,FP,Al,5.00,1,,',
      ',01/11/2026,1,A,FP,Jo,5.0,1,,',
    ));
    $this->assertStringNotContainsString('-D', $out);
  }

  public function testQuotedFieldsWithCommasRoundTrip(): void {
    $out = $this->cleaner->clean(self::export(
      ',01/11/2026,1,,FP,"Smith, J",5.00,1,,',
      ',01/11/2026,1,,FP,"Smith, J",5.00,1,,',
    ));
    $this->assertStringContainsString(',01/11/2026,1,NOREF,FP,"Smith, J",5.00,1,,', $out);
    $this->assertStringContainsString(',01/11/2026,1,NOREF-D1,FP,"Smith, J",5.00,1,,', $out);
  }

  public function testExportWithNoTransactionsReturnsHeaderOnly(): void {
    $this->assertSame(self::HEADER . "\n", $this->cleaner->clean(self::TITLE . self::HEADER . "\n" . self::FOOTER));
  }

  public function testEmptyFileIsRejected(): void {
    $this->expectException(InvalidBankCsvException::class);
    $this->expectExceptionMessage('empty');
    $this->cleaner->clean(" \n\n");
  }

  public function testFileWithoutHeaderIsRejected(): void {
    $this->expectException(InvalidBankCsvException::class);
    $this->expectExceptionMessage('no header row');
    $this->cleaner->clean("Date,Description,Amount\n01/11/2026,Coffee,-2.50\n");
  }

  public function testRowWithWrongColumnCountIsRejected(): void {
    $this->expectException(InvalidBankCsvException::class);
    $this->expectExceptionMessage('Line 5 has 3 columns but the header has 10');
    $this->cleaner->clean(self::export(',01/11/2026,1,A,FP,Jo,5.00,1,,', 'a,b,c'));
  }

  public function testMultiPageExportIsRejected(): void {
    $this->expectException(InvalidBankCsvException::class);
    $this->expectExceptionMessage('several pages');
    $this->cleaner->clean(self::export(',01/11/2026,1,A,FP,Jo,5.00,1,,') . self::export(',02/11/2026,1,B,FP,Jo,5.00,1,,'));
  }

}
