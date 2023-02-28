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

use Civi\ExternalFile\Api4\Api4Interface;
use Civi\ExternalFile\Api4\DAOActionFactoryInterface;
use Civi\ExternalFile\Entity\ExternalFileEntity;
use Webmozart\Assert\Assert;

final class ExternalFileManager implements ExternalFileManagerInterface {

  private Api4Interface $api4;

  private DAOActionFactoryInterface $daoActionFactory;

  public function __construct(Api4Interface $api4, DAOActionFactoryInterface $daoActionFactory) {
    $this->api4 = $api4;
    $this->daoActionFactory = $daoActionFactory;
  }

  public function get(int $id): ?ExternalFileEntity {
    $action = $this->daoActionFactory->get('ExternalFile')
      ->setCheckPermissions(FALSE)
      ->addWhere('id', '=', $id);
    $result = $this->api4->executeAction($action);
    $values = $result->first();

    // @phpstan-ignore-next-line
    return NULL === $values ? NULL : ExternalFileEntity::fromArray($values);
  }

  public function getByIdAndFilename(int $id, string $filename): ?ExternalFileEntity {
    $externalFile = $this->get($id);

    if (NULL === $externalFile || $externalFile->getFilename() !== $filename) {
      return NULL;
    }

    return $externalFile;
  }

  public function refresh(ExternalFileEntity $externalFile): void {
    $freshExternalFile = $this->get($externalFile->getId());
    Assert::notNull($freshExternalFile, sprintf('ExternalFile with ID "%d" not found.', $externalFile->getId()));
    $externalFile->setValues($freshExternalFile->toArray());
  }

  public function update(ExternalFileEntity $externalFile): void {
    $result = $this->api4->updateEntity(
      'ExternalFile',
      $externalFile->getId(),
      $externalFile->toArray(),
      ['checkPermissions' => FALSE],
    );
    // @phpstan-ignore-next-line
    $externalFile->setValues($result->single());
  }

}
