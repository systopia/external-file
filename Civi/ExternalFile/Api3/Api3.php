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

/**
 * @codeCoverageIgnore
 */
final class Api3 implements Api3Interface {

  /**
   * @inheritDoc
   */
  public function execute(string $entityName, string $action, array $params = []): array {
    /** @var array<string, mixed> $result */
    $result = civicrm_api3($entityName, $action, $params);

    return $result;
  }

  /**
   * @inheritDoc
   */
  public function executeInt(string $entityName, string $action, array $params = []): int {
    /** @var int $result */
    $result = civicrm_api3($entityName, $action, $params);

    return $result;
  }

}
