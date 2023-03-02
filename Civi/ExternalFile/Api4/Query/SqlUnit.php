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

namespace Civi\ExternalFile\Api4\Query;

use Civi\Api4\Query\Api4SelectQuery;
use Civi\Api4\Query\SqlExpression;

/**
 * Used for the unit part of TIMESTAMPDIFF.
 *
 * @see \Civi\Api4\Query\SqlFunctionEXTERNALFILE_TIMESTAMPDIFF
 */
final class SqlUnit extends SqlExpression {

  protected function initialize(): void {
  }

  /**
   * @inheritDoc
   */
  public function render(Api4SelectQuery $query): string {
    return $this->expr;
  }

  /**
   * @inheritDoc
   */
  public static function getTitle(): string {
    return 'Unit';
  }

}
