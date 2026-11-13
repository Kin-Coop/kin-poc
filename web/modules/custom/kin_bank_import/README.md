# Kin Bank Import (`kin_bank_import`)

Admin page where a user uploads a Co-operative Bank transaction CSV and gets a
cleaned copy back as a download. Cleaning rules: [SPEC.md](SPEC.md).

- **Page:** `/admin/config/services/kin-bank-import`
  (Configuration » Web services » Clean Co-operative Bank CSV)
- **Permission:** `use kin bank import` (restricted)
- **No file storage:** the upload is read from PHP's temporary upload file
  and never saved as a managed file or file entity, or written anywhere. PHP
  deletes the temp file at the end of the request. The cleaned CSV is streamed
  back in the same response.

## Layout

| Path | What |
|---|---|
| `src/Cleaner/CoopBankCsvCleaner.php` | All cleaning logic. Plain PHP, no Drupal dependencies. |
| `src/Form/BankCsvCleanForm.php` | The upload form and download response, plus the next-step buttons. |
| `config/install/kin_bank_import.settings.yml` | Labels, links and instructions for the three next-step buttons (**placeholders**). |
| `tests/src/Unit/CoopBankCsvCleanerTest.php` | Unit tests for the cleaner. |
| `tests/fixtures/` | Sample export and expected output for each stage. |

## Tests

Inside the Drupal site (DDEV):

```bash
ddev exec vendor/bin/phpunit -c web/core web/modules/custom/kin_bank_import
```

Without Drupal (from this module directory):

```bash
composer install -d tests/standalone
tests/standalone/vendor/bin/phpunit -c phpunit.standalone.xml
```

## Next-step buttons

Edit `config/install/kin_bank_import.settings.yml` before first enabling the
module. Once the module is installed, change the active config instead, e.g.
`drush config:edit kin_bank_import.settings`. A `url` is an internal path
starting with `/` or an absolute `https://` URL. Entries with a bad URL are
hidden.
