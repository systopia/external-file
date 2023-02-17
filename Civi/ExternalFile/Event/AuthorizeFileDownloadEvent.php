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

namespace Civi\ExternalFile\Event;

use Civi\ExternalFile\Entity\AttachmentEntity;
use Civi\ExternalFile\Entity\ExternalFileEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @codeCoverageIgnore
 */
final class AuthorizeFileDownloadEvent extends Event {

  private AttachmentEntity $attachment;

  private ExternalFileEntity $externalFile;

  private Request $request;

  private bool $authorized = FALSE;

  public function __construct(AttachmentEntity $attachment, ExternalFileEntity $externalFile, Request $request) {
    $this->attachment = $attachment;
    $this->externalFile = $externalFile;
    $this->request = $request;
  }

  public function getAttachment(): AttachmentEntity {
    return $this->attachment;
  }

  public function getExternalFile(): ExternalFileEntity {
    return $this->externalFile;
  }

  public function getRequest(): Request {
    return $this->request;
  }

  public function isAuthorized(): bool {
    return $this->authorized;
  }

  public function setAuthorized(bool $authorized): self {
    $this->authorized = $authorized;

    return $this;
  }

}
