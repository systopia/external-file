<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\ExternalFile;

use Civi\ExternalFile\Exception\DownloadAlreadyInProgressException;
use Civi\ExternalFile\Lock\LockFactoryInterface;

/**
 * Downloads external files that are required to be downloaded.
 */
final class DownloadFilesJob {

  private ExternalFileDownloaderInterface $externalFileDownloader;

  private ExternalFilesDownloadRequiredLoaderInterface $externalFilesLoader;

  private LockFactoryInterface $lockFactory;

  public function __construct(
    ExternalFileDownloaderInterface $externalFileDownloader,
    ExternalFilesDownloadRequiredLoaderInterface $externalFilesLoader,
    LockFactoryInterface $lockFactory
  ) {
    $this->externalFileDownloader = $externalFileDownloader;
    $this->externalFilesLoader = $externalFilesLoader;
    $this->lockFactory = $lockFactory;
  }

  /**
   * @param int $retries
   *   Include files in download failed status if the download try count does
   *   not exceed this value.
   *
   * @throws \CRM_Core_Exception
   */
  public function run(int $retries): void {
    $lock = $this->lockFactory->createLock('external-file.download');
    if (!$lock->tryLock()) {
      // Another job is already running.
      return;
    }

    foreach ($this->externalFilesLoader->get($retries) as $externalFile) {
      try {
        $this->externalFileDownloader->download($externalFile);
      }
      catch (DownloadAlreadyInProgressException $e) {
        // @ignoreException Download has been started meanwhile.
      }
    }
  }

}
