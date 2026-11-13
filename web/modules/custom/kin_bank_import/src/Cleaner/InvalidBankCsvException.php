<?php

declare(strict_types=1);

namespace Drupal\kin_bank_import\Cleaner;

/**
 * Thrown when an uploaded file does not look like a Co-operative Bank export.
 *
 * The message is safe to show to the person who uploaded the file.
 */
final class InvalidBankCsvException extends \RuntimeException {
}
