<?php
declare(strict_types = 1);

use Civi\ExternalFile\DownloadFilesJob;
use CRM_ExternalFile_ExtensionUtil as E;

const EXTERNAL_FILE_DEFAULT_DOWNLOAD_RETRIES = 5;

/**
 * ExternalFile.run_download_job API spec
 *
 * @phpstan-param array<string, mixed> $spec
 */
function _civicrm_api3_external_file_run_download_job_spec(array &$spec): void {
  $spec['retries'] = [
    'title' => E::ts('Retries'),
    'description' => E::ts('Retry failed downloads that many times'),
    'type' => CRM_Utils_Type::T_INT,
    'api.default' => EXTERNAL_FILE_DEFAULT_DOWNLOAD_RETRIES,
  ];
}

/**
 * ExternalFile.run_download_job API
 *
 * @phpstan-param array{retries?: int|string} $params
 *
 * @phpstan-return array<string, mixed>
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws \CRM_Core_Exception
 */
function civicrm_api3_external_file_run_download_job($params): array {
  /** @var \Civi\ExternalFile\DownloadFilesJob $downloadFilesJob */
  $downloadFilesJob = \Civi::service(DownloadFilesJob::class);
  $downloadFilesJob->run((int) ($params['retries'] ?? EXTERNAL_FILE_DEFAULT_DOWNLOAD_RETRIES));

  return civicrm_api3_create_success([], $params, 'ExternalFile', 'run_download_job');
}
