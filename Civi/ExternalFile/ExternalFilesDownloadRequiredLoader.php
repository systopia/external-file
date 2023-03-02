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

use Civi\ExternalFile\Api4\Query\Comparison;
use Civi\ExternalFile\Api4\Query\CompositeCondition;
use Civi\ExternalFile\Api4\Query\ConditionInterface;

final class ExternalFilesDownloadRequiredLoader implements ExternalFilesDownloadRequiredLoaderInterface {

  private ExternalFileManagerInterface $externalFileManager;

  public function __construct(ExternalFileManagerInterface $externalFileManager) {
    $this->externalFileManager = $externalFileManager;
  }

  public function get(int $retries): iterable {
    foreach ($this->externalFileManager->getBy($this->getCondition($retries)) as $externalFile) {
      // File might have been downloaded meanwhile.
      $this->externalFileManager->refresh($externalFile);
      if (ExternalFileStatus::AVAILABLE !== $externalFile->getStatus()) {
        yield $externalFile;
      }
    }
  }

  /**
   * @see \Civi\Api4\Query\SqlFunctionEXTERNALFILE_TIMESTAMPDIFF
   */
  private function getCondition(int $retries): ConditionInterface {
    return CompositeCondition::new('OR',
      Comparison::new('status', 'IN', [
        ExternalFileStatus::NEW,
        ExternalFileStatus::RELOAD,
      ]),
      // Download shouldn't take more than an hour, i.e. status might be incorrect (PHP process killed?)
      CompositeCondition::new('AND',
        Comparison::new('status', '=', ExternalFileStatus::DOWNLOADING),
        Comparison::new('EXTERNALFILE_TIMESTAMPDIFF(HOUR, download_start_date, NOW())', '>=', 1),
      ),
      // Retry failed downloads after 2 hours.
      CompositeCondition::new('AND',
        Comparison::new('status', '=', ExternalFileStatus::DOWNLOAD_FAILED),
        Comparison::new('download_try_count', '<=', $retries),
        Comparison::new(
          'EXTERNALFILE_TIMESTAMPDIFF(HOUR, download_start_date, NOW())',
          '>=',
          2,
        ),
      )
    );
  }

}
