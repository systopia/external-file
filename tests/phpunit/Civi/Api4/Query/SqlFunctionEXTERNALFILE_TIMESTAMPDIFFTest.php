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

namespace Civi\Api4\Query;

use Civi\Api4\Generic\DAOGetAction;
use Civi\ExternalFile\AbstractExternalFileHeadlessTestCase;

/**
 * @covers \Civi\Api4\Query\SqlFunctionEXTERNALFILE_TIMESTAMPDIFF
 *
 * @group headless
 */
final class SqlFunctionEXTERNALFILE_TIMESTAMPDIFFTest extends AbstractExternalFileHeadlessTestCase {

  public function test(): void {
    $query = new Api4SelectQuery(new DAOGetAction('ExternalFile', 'get'));

    $expression1 = new SqlFunctionEXTERNALFILE_TIMESTAMPDIFF(
      'EXTERNALFILE_TIMESTAMPDIFF(MINUTE, download_start_date, "1970-01-01 00:00:00")'
    );
    static::assertSame(
      'TIMESTAMPDIFF(MINUTE, `a`.`download_start_date`, "1970-01-01 00:00:00")',
      $expression1->render($query)
    );

    $expression1 = new SqlFunctionEXTERNALFILE_TIMESTAMPDIFF(
      'EXTERNALFILE_TIMESTAMPDIFF(hour, "1970-01-01 00:00:00", download_start_date)'
    );
    static::assertSame(
      'TIMESTAMPDIFF(hour, "1970-01-01 00:00:00", `a`.`download_start_date`)',
      $expression1->render($query)
    );
  }

}
