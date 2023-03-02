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

interface ExternalFilesDownloadRequiredLoaderInterface {

  /**
   * Returns all ExternalFile entities for which a download is required.
   *
   * @param int $retries
   *   Include files in download failed status if the download try count does
   *   not exceed this value. The implementation might use a delay as additional
   *   condition.
   *
   * @phpstan-return iterable<\Civi\ExternalFile\Entity\ExternalFileEntity>
   *
   * @throws \CRM_Core_Exception
   */
  public function get(int $retries): iterable;

}
