<?php
// This file declares a new entity type. For more details, see "hook_civicrm_entityTypes" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
return [
  [
    'name' => 'ExternalFile',
    'class' => 'CRM_ExternalFile_DAO_ExternalFile',
    'table' => 'civicrm_external_file',
  ],
];
