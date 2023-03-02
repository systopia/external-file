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

// phpcs:disable Drupal.Commenting.DocComment.ContentAfterOpen
/** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */

use Civi\Core\CiviEventDispatcherInterface;
use Civi\ExternalFile\Api3\Api3;
use Civi\ExternalFile\Api3\Api3Interface;
use Civi\ExternalFile\Api4\Action\ExternalFile\CreateAction;
use Civi\ExternalFile\Api4\Action\ExternalFile\DeleteAction;
use Civi\ExternalFile\Api4\Action\ExternalFile\GetAction;
use Civi\ExternalFile\Api4\Action\ExternalFile\GetFieldsAction;
use Civi\ExternalFile\Api4\Action\ExternalFile\UpdateAction;
use Civi\ExternalFile\Api4\Api4;
use Civi\ExternalFile\Api4\Api4Interface;
use Civi\ExternalFile\Api4\DAOActionFactory;
use Civi\ExternalFile\Api4\DAOActionFactoryInterface;
use Civi\ExternalFile\AttachmentManager;
use Civi\ExternalFile\AttachmentManagerInterface;
use Civi\ExternalFile\Controller\FileDownloadController;
use Civi\ExternalFile\DownloadFilesJob;
use Civi\ExternalFile\ExternalFileDownloader;
use Civi\ExternalFile\ExternalFileDownloaderInterface;
use Civi\ExternalFile\ExternalFileManager;
use Civi\ExternalFile\ExternalFileManagerInterface;
use Civi\ExternalFile\ExternalFilesDownloadRequiredLoader;
use Civi\ExternalFile\ExternalFilesDownloadRequiredLoaderInterface;
use Civi\ExternalFile\ExternalFileUriGenerator;
use Civi\ExternalFile\ExternalFileUriGeneratorInterface;
use Civi\ExternalFile\Lock\FlockFactory;
use Civi\ExternalFile\Lock\LockFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\MimeTypeGuesserInterface;
use Symfony\Component\Mime\MimeTypes;

if (!$container->has(CiviEventDispatcherInterface::class)) {
  $container->setAlias(CiviEventDispatcherInterface::class, 'dispatcher.boot');
}

if (!$container->has(LoggerInterface::class)) {
  $container->setAlias(LoggerInterface::class, 'psr_log');
}

if (!$container->has(MimeTypeGuesserInterface::class)) {
  $container->register(MimeTypeGuesserInterface::class, MimeTypes::class)
    ->setFactory([MimeTypes::class, 'getDefault']);
}

if (!$container->has(CRM_Utils_HttpClient::class)) {
  $container->register(CRM_Utils_HttpClient::class, CRM_Utils_HttpClient::class)
    ->setFactory([CRM_Utils_HttpClient::class, 'singleton']);
}

$container->autowire(Api3Interface::class, Api3::class);
$container->autowire(Api4Interface::class, Api4::class);
$container->autowire(DAOActionFactoryInterface::class, DAOActionFactory::class);
$container->autowire(LockFactoryInterface::class, FlockFactory::class);

$container->autowire(DownloadFilesJob::class)
  ->setPublic(TRUE);
$container->autowire(FileDownloadController::class)
  ->setPublic(TRUE);

$container->autowire(AttachmentManagerInterface::class, AttachmentManager::class);
$container->autowire(ExternalFileManagerInterface::class, ExternalFileManager::class);
$container->autowire(ExternalFileDownloaderInterface::class, ExternalFileDownloader::class);
$container->autowire(ExternalFileUriGeneratorInterface::class, ExternalFileUriGenerator::class);
$container->autowire(ExternalFilesDownloadRequiredLoaderInterface::class, ExternalFilesDownloadRequiredLoader::class);

$container->autowire(CreateAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);

$container->autowire(DeleteAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);

$container->autowire(GetAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);

$container->autowire(GetFieldsAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);

$container->autowire(UpdateAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
