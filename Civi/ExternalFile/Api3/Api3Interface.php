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

namespace Civi\ExternalFile\Api3;

interface Api3Interface {

  /**
   * To be used for actions returning an array.
   *
   * @phpstan-param array<string, mixed> $params
   *
   * @phpstan-return array<string, mixed>
   *
   * @throws \CRM_Core_Exception
   */
  public function execute(string $entityName, string $action, array $params = []): array;

  /**
   * To be used for actions returning an integer (e.g. getcount).
   *
   * @phpstan-param array<string, mixed> $params
   *
   * @return int
   *
   * @throws \CRM_Core_Exception
   */
  public function executeInt(string $entityName, string $action, array $params = []): int;

}
