<?php
declare(strict_types = 1);

namespace Civi\ExternalFile;

use CRM_Utils_System;

/**
 * @codeCoverageIgnore
 */
final class ExternalFileUriGenerator implements ExternalFileUriGeneratorInterface {

  public function generateDownloadUri(int $externalFileId, string $externalFilename): string {
    // Remove the "?" that is added to the URI even though there's no query.
    $uri = rtrim(
      CRM_Utils_System::url(
        sprintf('civicrm/external-file/download/%d/%s', $externalFileId, rawurlencode($externalFilename)),
        '',
        TRUE,
        NULL,
        FALSE,
      ),
      '?'
    );

    $config = \CRM_Core_Config::singleton();

    // Return language independent URI, so it's the same for all users.
    return $config->userSystem->languageNegotiationURL($uri, FALSE, TRUE);
  }

}
