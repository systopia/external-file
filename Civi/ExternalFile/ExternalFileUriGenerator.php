<?php
declare(strict_types = 1);

namespace Civi\ExternalFile;

use CRM_Utils_System;

/**
 * @codeCoverageIgnore
 */
final class ExternalFileUriGenerator implements ExternalFileUriGeneratorInterface {

  public function generateDownloadUri(int $externalFileId, string $externalFilename): string {
    return CRM_Utils_System::url(
      sprintf('civicrm/external-file/download/%d/%s', $externalFileId, $externalFilename),
      '',
      TRUE,
      NULL,
      FALSE,
    );
  }

}
