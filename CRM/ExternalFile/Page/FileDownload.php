<?php
declare(strict_types = 1);

use Civi\ExternalFile\Controller\FileDownloadController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CRM_ExternalFile_Page_FileDownload extends CRM_Core_Page {

  public function run(): void {
    /** @var \Civi\ExternalFile\Controller\FileDownloadController $controller */
    $controller = Civi::service(FileDownloadController::class);
    $externalFileId = $this->urlPath[3] ?? NULL;
    $filename = $this->urlPath[4] ?? NULL;

    if (is_numeric($externalFileId) && NULL !== $filename) {
      $response = $controller->download((int) $externalFileId, $filename, Request::createFromGlobals());
    }
    else {
      $response = new Response(Response::$statusTexts[Response::HTTP_NOT_FOUND], Response::HTTP_NOT_FOUND);
    }

    $response->send();
    CRM_Utils_System::civiExit();
  }

}
