<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from external-file/xml/schema/CRM/ExternalFile/ExternalFile.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:ecb4467cd1921a16301207f3f7785687)
 */
use CRM_ExternalFile_ExtensionUtil as E;

/**
 * Database access object for the ExternalFile entity.
 */
class CRM_ExternalFile_DAO_ExternalFile extends CRM_Core_DAO {
  const EXT = E::LONG_NAME;
  const TABLE_ADDED = '';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_external_file';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique ExternalFile ID
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $id;

  /**
   * FK to File
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $file_id;

  /**
   * Source URI
   *
   * @var string
   *   (SQL type: varchar(255))
   *   Note that values will be retrieved from the database as a string.
   */
  public $source;

  /**
   * Filename in download URI
   *
   * @var string
   *   (SQL type: varchar(255))
   *   Note that values will be retrieved from the database as a string.
   */
  public $filename;

  /**
   * The extension that added the file
   *
   * @var string
   *   (SQL type: varchar(255))
   *   Note that values will be retrieved from the database as a string.
   */
  public $extension;

  /**
   * Optional additional data
   *
   * @var string
   *   (SQL type: text)
   *   Note that values will be retrieved from the database as a string.
   */
  public $custom_data;

  /**
   * @var string
   *   (SQL type: varchar(64))
   *   Note that values will be retrieved from the database as a string.
   */
  public $status;

  /**
   * @var string|null
   *   (SQL type: datetime)
   *   Note that values will be retrieved from the database as a string.
   */
  public $download_start_date;

  /**
   * @var int|string
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $download_try_count;

  /**
   * Date used in Last-Modified header
   *
   * @var string|null
   *   (SQL type: varchar(255))
   *   Note that values will be retrieved from the database as a string.
   */
  public $last_modified;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_external_file';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? E::ts('External Files') : E::ts('External File');
  }

  /**
   * Returns foreign keys and entity references.
   *
   * @return array
   *   [CRM_Core_Reference_Interface]
   */
  public static function getReferenceColumns() {
    if (!isset(Civi::$statics[__CLASS__]['links'])) {
      Civi::$statics[__CLASS__]['links'] = static::createReferenceColumns(__CLASS__);
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'file_id', 'civicrm_file', 'id');
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'links_callback', Civi::$statics[__CLASS__]['links']);
    }
    return Civi::$statics[__CLASS__]['links'];
  }

  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  public static function &fields() {
    if (!isset(Civi::$statics[__CLASS__]['fields'])) {
      Civi::$statics[__CLASS__]['fields'] = [
        'id' => [
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('ID'),
          'description' => E::ts('Unique ExternalFile ID'),
          'required' => TRUE,
          'where' => 'civicrm_external_file.id',
          'table_name' => 'civicrm_external_file',
          'entity' => 'ExternalFile',
          'bao' => 'CRM_ExternalFile_DAO_ExternalFile',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'readonly' => TRUE,
          'add' => NULL,
        ],
        'file_id' => [
          'name' => 'file_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('File ID'),
          'description' => E::ts('FK to File'),
          'where' => 'civicrm_external_file.file_id',
          'table_name' => 'civicrm_external_file',
          'entity' => 'ExternalFile',
          'bao' => 'CRM_ExternalFile_DAO_ExternalFile',
          'localizable' => 0,
          'FKClassName' => 'CRM_Core_DAO_File',
          'add' => NULL,
        ],
        'source' => [
          'name' => 'source',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Source'),
          'description' => E::ts('Source URI'),
          'required' => TRUE,
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civicrm_external_file.source',
          'table_name' => 'civicrm_external_file',
          'entity' => 'ExternalFile',
          'bao' => 'CRM_ExternalFile_DAO_ExternalFile',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
          ],
          'add' => NULL,
        ],
        'filename' => [
          'name' => 'filename',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Filename'),
          'description' => E::ts('Filename in download URI'),
          'required' => TRUE,
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civicrm_external_file.filename',
          'table_name' => 'civicrm_external_file',
          'entity' => 'ExternalFile',
          'bao' => 'CRM_ExternalFile_DAO_ExternalFile',
          'localizable' => 0,
          'add' => NULL,
        ],
        'extension' => [
          'name' => 'extension',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Extension'),
          'description' => E::ts('The extension that added the file'),
          'required' => TRUE,
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civicrm_external_file.extension',
          'table_name' => 'civicrm_external_file',
          'entity' => 'ExternalFile',
          'bao' => 'CRM_ExternalFile_DAO_ExternalFile',
          'localizable' => 0,
          'add' => NULL,
        ],
        'custom_data' => [
          'name' => 'custom_data',
          'type' => CRM_Utils_Type::T_TEXT,
          'title' => E::ts('Custom Data'),
          'description' => E::ts('Optional additional data'),
          'required' => FALSE,
          'where' => 'civicrm_external_file.custom_data',
          'table_name' => 'civicrm_external_file',
          'entity' => 'ExternalFile',
          'bao' => 'CRM_ExternalFile_DAO_ExternalFile',
          'localizable' => 0,
          'serialize' => self::SERIALIZE_JSON,
          'add' => NULL,
        ],
        'status' => [
          'name' => 'status',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Status'),
          'required' => TRUE,
          'maxlength' => 64,
          'size' => CRM_Utils_Type::BIG,
          'where' => 'civicrm_external_file.status',
          'table_name' => 'civicrm_external_file',
          'entity' => 'ExternalFile',
          'bao' => 'CRM_ExternalFile_DAO_ExternalFile',
          'localizable' => 0,
          'html' => [
            'type' => 'Select',
          ],
          'pseudoconstant' => [
            'callback' => 'Civi\ExternalFile\ExternalFileStatus::getAll',
          ],
          'add' => NULL,
        ],
        'download_start_date' => [
          'name' => 'download_start_date',
          'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
          'title' => E::ts('Download Start Date'),
          'where' => 'civicrm_external_file.download_start_date',
          'table_name' => 'civicrm_external_file',
          'entity' => 'ExternalFile',
          'bao' => 'CRM_ExternalFile_DAO_ExternalFile',
          'localizable' => 0,
          'html' => [
            'type' => 'Select Date',
            'formatType' => 'activityDateTime',
          ],
          'add' => NULL,
        ],
        'download_try_count' => [
          'name' => 'download_try_count',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Download Try Count'),
          'required' => TRUE,
          'where' => 'civicrm_external_file.download_try_count',
          'table_name' => 'civicrm_external_file',
          'entity' => 'ExternalFile',
          'bao' => 'CRM_ExternalFile_DAO_ExternalFile',
          'localizable' => 0,
          'add' => NULL,
        ],
        'last_modified' => [
          'name' => 'last_modified',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Last Modified'),
          'description' => E::ts('Date used in Last-Modified header'),
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civicrm_external_file.last_modified',
          'table_name' => 'civicrm_external_file',
          'entity' => 'ExternalFile',
          'bao' => 'CRM_ExternalFile_DAO_ExternalFile',
          'localizable' => 0,
          'add' => NULL,
        ],
      ];
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }
    return Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Return a mapping from field-name to the corresponding key (as used in fields()).
   *
   * @return array
   *   Array(string $name => string $uniqueName).
   */
  public static function &fieldKeys() {
    if (!isset(Civi::$statics[__CLASS__]['fieldKeys'])) {
      Civi::$statics[__CLASS__]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', self::fields()));
    }
    return Civi::$statics[__CLASS__]['fieldKeys'];
  }

  /**
   * Returns the names of this table
   *
   * @return string
   */
  public static function getTableName() {
    return self::$_tableName;
  }

  /**
   * Returns if this table needs to be logged
   *
   * @return bool
   */
  public function getLog() {
    return self::$_log;
  }

  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &import($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'external_file', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of fields that can be exported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &export($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'external_file', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of indices
   *
   * @param bool $localize
   *
   * @return array
   */
  public static function indices($localize = TRUE) {
    $indices = [
      'UI_external_file_file_id' => [
        'name' => 'UI_external_file_file_id',
        'field' => [
          0 => 'file_id',
        ],
        'localizable' => FALSE,
        'unique' => TRUE,
        'sig' => 'civicrm_external_file::1::file_id',
      ],
    ];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
