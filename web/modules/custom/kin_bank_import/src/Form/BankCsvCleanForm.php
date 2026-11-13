<?php

declare(strict_types=1);

namespace Drupal\kin_bank_import\Form;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\kin_bank_import\Cleaner\CoopBankCsvCleaner;
use Drupal\kin_bank_import\Cleaner\InvalidBankCsvException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Upload a Co-operative Bank CSV and download a cleaned copy.
 *
 * The upload is read straight from PHP's temporary upload file and never
 * saved: no managed file, no file entity, nothing in public:// or private://.
 * PHP deletes its temporary upload file at the end of the request. The cleaned
 * output is held only in a temporary form-state value, which is never cached.
 */
final class BankCsvCleanForm extends FormBase {

  private const FIELD = 'csv_file';
  private const MAX_BYTES = 2 * 1024 * 1024;
  private const CLEANED = 'kin_bank_import_cleaned';
  private const FILENAME = 'kin_bank_import_filename';

  public function getFormId(): string {
    return 'kin_bank_import_clean_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#attributes']['enctype'] = 'multipart/form-data';

    $form['intro'] = [
      '#markup' => '<p>' . $this->t('Upload the transaction CSV exported from Co-operative Bank online banking. A cleaned copy downloads straight away. The uploaded file is not kept on the server.') . '</p>',
    ];

    $form[self::FIELD] = [
      '#type' => 'file',
      '#title' => $this->t('Co-operative Bank CSV export'),
      '#description' => $this->t('A .csv file of up to @size MB.', ['@size' => self::MAX_BYTES / 1024 / 1024]),
      '#attributes' => ['accept' => '.csv,text/csv'],
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Clean and download'),
      '#button_type' => 'primary',
    ];

    $form['next_steps'] = $this->buildNextSteps();

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $file = $this->getUploadedFile();

    if ($file === NULL || $file->getError() === UPLOAD_ERR_NO_FILE) {
      $form_state->setErrorByName(self::FIELD, $this->t('Choose a CSV file to upload.'));
      return;
    }
    if (!$file->isValid()) {
      $form_state->setErrorByName(self::FIELD, $this->t('The upload failed: @reason', ['@reason' => $file->getErrorMessage()]));
      return;
    }
    if (strtolower($file->getClientOriginalExtension()) !== 'csv') {
      $form_state->setErrorByName(self::FIELD, $this->t('Upload a .csv file.'));
      return;
    }
    if ($file->getSize() > self::MAX_BYTES) {
      $form_state->setErrorByName(self::FIELD, $this->t('The file is larger than @size MB.', ['@size' => self::MAX_BYTES / 1024 / 1024]));
      return;
    }

    $contents = file_get_contents($file->getPathname());
    if ($contents === FALSE) {
      $form_state->setErrorByName(self::FIELD, $this->t('The uploaded file could not be read.'));
      return;
    }

    try {
      $cleaned = (new CoopBankCsvCleaner())->clean($contents);
    }
    catch (InvalidBankCsvException $e) {
      $form_state->setErrorByName(self::FIELD, $this->t('The file could not be cleaned: @reason', ['@reason' => $e->getMessage()]));
      return;
    }

    $form_state->setTemporaryValue(self::CLEANED, $cleaned);
    $form_state->setTemporaryValue(self::FILENAME, $this->downloadName($file->getClientOriginalName()));
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $filename = $form_state->getTemporaryValue(self::FILENAME);
    $response = new Response($form_state->getTemporaryValue(self::CLEANED));
    $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
    $response->headers->set('Content-Disposition', $response->headers->makeDisposition('attachment', $filename, 'cleaned.csv'));
    $response->headers->set('Cache-Control', 'no-store, private');
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $form_state->setResponse($response);
  }

  /**
   * Returns the uploaded file from the current request, if any.
   */
  private function getUploadedFile(): ?UploadedFile {
    // Drupal names file inputs files[<element name>].
    $files = $this->getRequest()->files->get('files', []);
    $file = $files[self::FIELD] ?? NULL;
    return $file instanceof UploadedFile ? $file : NULL;
  }

  /**
   * Builds the download filename: "<original name>-cleaned.csv".
   */
  private function downloadName(string $original): string {
    $base = pathinfo($original, PATHINFO_FILENAME);
    $base = preg_replace('/[^A-Za-z0-9._-]+/', '_', $base) ?: 'bank-export';
    return $base . '-cleaned.csv';
  }

  /**
   * Builds the three "next steps" link buttons from configuration.
   */
  private function buildNextSteps(): array {
    $config = $this->config('kin_bank_import.settings');

    $items = [];
    foreach ($config->get('next_steps') ?? [] as $step) {
      $url = $this->toUrl((string) ($step['url'] ?? ''));
      if ($url === NULL || empty($step['label'])) {
        continue;
      }
      $items[] = [
        'link' => Link::fromTextAndUrl($step['label'], $url->setOption('attributes', [
          'class' => ['button'],
        ]))->toRenderable(),
        'instructions' => [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $this->t('@text', ['@text' => $step['instructions'] ?? '']),
          '#attributes' => ['class' => ['description']],
        ],
      ];
    }

    $build = [
      '#type' => 'details',
      '#title' => $this->t('Next steps'),
      '#open' => TRUE,
      'steps' => [
        '#theme' => 'item_list',
        '#list_type' => 'ol',
        '#items' => $items,
      ],
    ];
    CacheableMetadata::createFromObject($config)->applyTo($build);
    return $build;
  }

  /**
   * Turns a configured link into a Url: "/internal/path" or an absolute URL.
   */
  private function toUrl(string $link): ?Url {
    try {
      if (str_starts_with($link, '/')) {
        return Url::fromUserInput($link);
      }
      if (preg_match('#^https?://#i', $link)) {
        return Url::fromUri($link);
      }
    }
    catch (\InvalidArgumentException) {
      // Fall through: a bad link hides the button rather than breaking the page.
    }
    return NULL;
  }

}
