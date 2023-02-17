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

// Prevent error "Class \API_Exception not found".
// (Seems to happen because of class aliases not detected in scanned files.)
require_once __DIR__ . '/vendor/civicrm/civicrm-core/CRM/Core/Exception.php';
require_once __DIR__ . '/vendor/civicrm/civicrm-core/api/Exception.php';

// Methods in DB_DataObject are not known, otherwise
require_once __DIR__ . '/vendor/civicrm/civicrm-packages/DB/DataObject.php';
