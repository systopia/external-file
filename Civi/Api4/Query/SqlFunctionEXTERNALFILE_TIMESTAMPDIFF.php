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

use Civi\ExternalFile\Api4\Query\SqlUnit;
use CRM_ExternalFile_ExtensionUtil as E;

/**
 * TIMESTAMPDIFF is not part of CiviCRMs SqlFunction classes (yet). This class
 * allows to use EXTERNALFILE_TIMESTAMPDIFF as alias for TIMESTAMPDIFF.
 *
 * EXTERNALFILE is used as prefix to avoid conflicts if it becomes available
 * in CiviCRM.
 *
 * The namespace is enforced by CiviCRM.
 */
class SqlFunctionEXTERNALFILE_TIMESTAMPDIFF extends SqlFunction {

  protected static $category = self::CATEGORY_DATE;

  protected static $dataType = 'Integer';

  public static function getName(): string {
    return 'TIMESTAMPDIFF';
  }

  /**
   * @phpstan-return array<array<string, mixed>>
   */
  protected static function params(): array {
    return [
      [
        'max_expr' => 3,
        'min_expr' => 3,
        'optional' => FALSE,
        'label' => E::ts('diff'),
      ],
    ];
  }

  protected function initialize(): void {
    parent::initialize();
    // Don't treat the first expression as field.
    if (($this->args[0]['expr'][0] ?? NULL) instanceof SqlExpression) {
      $expr = $this->args[0]['expr'][0]->getExpr();
      if (1 === preg_match('/^[A-Za-z]+$/', $expr)) {
        unset($this->fields[0]);
        $this->args[0]['expr'][0] = new SqlUnit($expr);
      }
    }
  }

  /**
   * @return string
   *
   * @codeCoverageIgnore
   */
  public static function getTitle(): string {
    return E::ts('Difference between two timestamps');
  }

  /**
   * @return string
   *
   * @codeCoverageIgnore
   */
  public static function getDescription(): string {
    return E::ts('Calculates the difference between two timestamps. See https://mariadb.com/kb/en/timestampdiff/');
  }

}
