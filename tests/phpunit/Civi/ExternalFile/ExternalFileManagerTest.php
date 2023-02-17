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

use Civi\Api4\Generic\DAOGetAction;
use Civi\Api4\Generic\Result;
use Civi\ExternalFile\Api4\Api4Interface;
use Civi\ExternalFile\Api4\DAOActionFactoryInterface;
use Civi\ExternalFile\EntityFactory\ExternalFileFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\ExternalFile\ExternalFileManager
 */
final class ExternalFileManagerTest extends TestCase {

  /**
   * @var \Civi\ExternalFile\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  /**
   * @var \Civi\ExternalFile\Api4\DAOActionFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $daoActionFactoryMock;

  private ExternalFileManager $externalFileManager;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->daoActionFactoryMock = $this->createMock(DAOActionFactoryInterface::class);
    $this->externalFileManager = new ExternalFileManager(
      $this->api4Mock,
      $this->daoActionFactoryMock,
    );
  }

  public function testGet(): void {
    $externalFile = ExternalFileFactory::create();
    $this->mockGet(12, $externalFile->toArray());
    static::assertEquals($externalFile, $this->externalFileManager->get(12));
  }

  public function testGetNull(): void {
    $this->mockGet(12, NULL);
    static::assertNull($this->externalFileManager->get(12));
  }

  public function testGetByIdAndFilename(): void {
    $externalFile = ExternalFileFactory::create();
    $this->mockGet(12, $externalFile->toArray(), 2);

    static::assertEquals($externalFile, $this->externalFileManager->getByIdAndFilename(12, 'test.txt'));
    static::assertNull($this->externalFileManager->getByIdAndFilename(12, 'testX.txt'));
  }

  public function testRefresh(): void {
    $externalFile = ExternalFileFactory::create();
    $this->mockGet(12, $externalFile->toArray());

    $currentExternalFile = ExternalFileFactory::create(['status' => 'should_be_refreshed']);
    $this->externalFileManager->refresh($currentExternalFile);
    static::assertEquals($externalFile, $currentExternalFile);
  }

  public function testUpdate(): void {
    $externalFile = ExternalFileFactory::create();
    $this->api4Mock->expects(static::once())->method('updateEntity')
      ->with('ExternalFile', $externalFile->getId(), $externalFile->toArray())
      ->willReturn(new Result([['status' => 'modified_in_update'] + $externalFile->toArray()]));

    $this->externalFileManager->update($externalFile);
    static::assertSame('modified_in_update', $externalFile->getStatus());
  }

  /**
   * @phpstan-param array<string, mixed>|null $record
   */
  private function mockGet(int $id, ?array $record, int $times = 1): void {
    $getAction = NULL;
    $this->daoActionFactoryMock->expects(static::exactly($times))->method('get')
      ->with('ExternalFile')
      ->willReturnCallback(function () use (&$getAction) {
        return $getAction = new DAOGetAction('ExternalFile', 'get');
      });

    $this->api4Mock->expects(static::exactly($times))->method('executeAction')->with(
      static::callback(function (DAOGetAction $action) use (&$getAction, $id) {
        static::assertSame($getAction, $action);
        static::assertSame('ExternalFile', $action->getEntityName());
        static::assertSame([['id', '=', $id, FALSE]], $action->getWhere());

        return TRUE;
      })
    )->willReturn(NULL === $record ? new Result() : new Result([$record]));
  }

}
