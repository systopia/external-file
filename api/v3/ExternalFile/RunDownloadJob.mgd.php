<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed

use CRM_ExternalFile_ExtensionUtil as E;

return [
  [
    'name' => 'Cron:ExternalFile.run_download_job',
    'entity' => 'Job',
    'params' => [
      'version' => 3,
      'name' => E::ts('Download external files'),
      'description' => E::ts(<<<EOT
Downloads external files. The number of times to retry failed downloads can be
changed with parameter "retries". It is ensured that this job runs only once.
EOT
      ),
      'run_frequency' => 'Always',
      'api_entity' => 'ExternalFile',
      'api_action' => 'run_download_job',
      'parameters' => 'retries=5',
    ],
  ],
];
