<?php
declare(strict_types = 1);

use CRM_ExternalFile_ExtensionUtil as E;

// phpcs:disable Generic.Files.LineLength.TooLong
return [
  [
    'name' => 'Job_Download_external_files',
    'entity' => 'Job',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'run_frequency' => 'Always',
        'name' => 'Download external files',
        'description' => E::ts('Downloads external files. The number of times to retry failed downloads can be changed with parameter "retries". It is ensured that this job runs only once.'),
        'api_entity' => 'ExternalFile',
        'api_action' => 'run_download_job',
        'parameters' => 'retries=5',
      ],
    ],
  ],
];
