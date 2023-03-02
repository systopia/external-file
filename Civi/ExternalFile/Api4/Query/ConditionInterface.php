<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

/**
 * @phpstan-type ComparisonT array{string, string, 2?: scalar|array<scalar>}
 * Actually this should be: array{string, array<int, ComparisonT|CompositeConditionT>}, so that is not possible
 * @phpstan-type CompositeConditionT array{string, array<int, array>}
 */
interface ConditionInterface {

  public function getOperator(): string;

  /**
   *
   * @phpstan-return ComparisonT|CompositeConditionT
   */
  public function toArray(): array;

}
