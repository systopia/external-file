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

namespace Civi\Api4;

use Civi\ExternalFile\AbstractExternalFileHeadlessTestCase;
use Civi\ExternalFile\ExternalFileStatus;

/**
 * @covers \Civi\Api4\ExternalFile
 * @covers \Civi\ExternalFile\Api4\Action\ExternalFile\CreateAction
 * @covers \Civi\ExternalFile\Api4\Action\ExternalFile\DeleteAction
 * @covers \Civi\ExternalFile\Api4\Action\ExternalFile\GetAction
 * @covers \Civi\ExternalFile\Api4\Action\ExternalFile\GetFieldsAction
 * @covers \Civi\ExternalFile\Api4\Action\ExternalFile\UpdateAction
 *
 * @group headless
 */
final class ExternalFileTest extends AbstractExternalFileHeadlessTestCase {

  public function test(): void {
    $createValues = ExternalFile::create()
      ->setValues([
        'file_id' => NULL,
        'source' => 'https://example.org/test.txt',
        'extension' => 'test',
      ])->execute()->single();
    // Remove extra values added by CiviCRM on create.
    unset($createValues['custom']);
    unset($createValues['check_permissions']);

    static::assertIsInt($createValues['id']);
    static::assertIsInt($createValues['file_id']);
    static::assertNotEmpty($createValues['file_uri']);
    static::assertNotEmpty($createValues['uri']);
    static::assertMatchesRegularExpression(
      '/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/',
      $createValues['file_upload_date'],
    );
    $expected = [
      'id' => $createValues['id'],
      'file_id' => $createValues['file_id'],
      'source' => 'https://example.org/test.txt',
      'filename' => 'test.txt',
      'status' => ExternalFileStatus::NEW,
      'download_start_date' => NULL,
      'download_try_count' => 0,
      'extension' => 'test',
      'custom_data' => NULL,
      'last_modified' => NULL,
      'file_mime_type' => 'application/octet-stream',
      'file_description' => NULL,
      'file_upload_date' => $createValues['file_upload_date'],
      'file_created_id' => NULL,
      'file_uri' => $createValues['file_uri'],
      'file_file_type_id' => NULL,
      'file_document' => NULL,
      'uri' => $createValues['uri'],
    ];
    static::assertEquals($expected, $createValues);

    static::assertEquals($expected, ExternalFile::get()->execute()->single());

    static::assertCount(1, ExternalFile::get()
      ->addWhere('filename', '=', 'test.txt')->execute()
    );

    static::assertCount(0, ExternalFile::get()
      ->addWhere('filename', '=', 'testX.txt')->execute()
    );

    ExternalFile::update()
      ->addWhere('id', '=', $expected['id'] + 1)
      ->setValues([
        'id' => $expected['id'] + 1,
        'status' => ExternalFileStatus::DOWNLOAD_FAILED,
        'file_id' => $expected['file_id'] + 1,
        'file_description' => 'test description',
      ])->execute();
    // Nothing should have changed.
    static::assertEquals($expected, ExternalFile::get()->execute()->single());

    $expected['status'] = ExternalFileStatus::DOWNLOAD_FAILED;
    $expected['file_description'] = 'test description';
    ExternalFile::update()
      ->addWhere('id', '=', $expected['id'])
      ->setValues($expected)
      ->execute()->single();
    static::assertEquals($expected, ExternalFile::get()->execute()->single());

    static::assertCount(0, ExternalFile::delete()->addWhere('id', '=', $expected['id'] + 1)->execute());
    static::assertCount(1, ExternalFile::delete()->addWhere('id', '=', $expected['id'])->execute());
    static::assertCount(0, ExternalFile::get()->execute());
  }

  public function testGet(): void {
    ExternalFile::create()
      ->setValues([
        'source' => 'https://example.org/test.txt',
        'extension' => 'test',
        'file_description' => 'file1',
      ])->execute();

    ExternalFile::create()
      ->setValues([
        'source' => 'https://example.org/test.txt',
        'extension' => 'test',
        'file_description' => 'file2',
      ])->execute();

    static::assertCount(2, ExternalFile::get()->execute());

    // Test "file_" field in where.
    static::assertCount(1, ExternalFile::get()
      ->addWhere('file_description', '=', 'file2')->execute()
    );

    // Test "file_" field in clause.
    static::assertCount(1, ExternalFile::get()
      ->addClause('AND', ['file_description', '=', 'file2'], ['extension', '=', 'test'])
      ->execute()
    );

    // Test select with "*" includes "file_" fields.
    // @phpstan-ignore-next-line
    static::assertNotEmpty(ExternalFile::get()->addSelect('*')->execute()->first()['file_description']);

    // Test "file_" field in order by.
    static::assertSame(
      ['file2', 'file1'],
      ExternalFile::get()->addOrderBy('file_description', 'DESC')
        ->execute()->column('file_description')
    );
  }

  public function testGetFields(): void {
    $fileFieldCount = File::getFields()->selectRowCount()->execute()->countMatched();

    static::assertCount($fileFieldCount + 10, ExternalFile::getFields()->execute());
  }

}
