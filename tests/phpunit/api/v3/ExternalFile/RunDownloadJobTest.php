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

use Civi\ExternalFile\AbstractExternalFileHeadlessTestCase;
use Civi\Test\Api3TestTrait;

/**
 * ExternalFile.run_download_job API Test Case
 *
 * @covers \civicrm_api3_external_file_run_download_job()
 * @covers \_civicrm_api3_external_file_run_download_job_spec()
 *
 * @group headless
 */
class api_v3_ExternalFile_RunDownloadJob_Test extends AbstractExternalFileHeadlessTestCase {
  use Api3TestTrait;

  public function test(): void {
    $result = civicrm_api3('ExternalFile', 'run_download_job', ['retries' => '2']);
    // @phpstan-ignore-next-line
    $this->assertAPISuccess($result);
  }

  public function testSpec(): void {
    $result = civicrm_api3('ExternalFile', 'getfields', ['action' => 'run_download_job']);
    // @phpstan-ignore-next-line
    static::assertSame(5, $result['values']['retries']['api.default']);
  }

}
