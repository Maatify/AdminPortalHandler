<?php
/**
 * @PHP       Version >= 8.0
 * @Liberary  AdminPortalHandler
 * @Project   AdminPortalHandler
 * @copyright ©2024 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-04-18 8:55 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/AdminPortalHandler  view project on GitHub
 * @Maatify   AdminPortalHandler :: DbLanguage
 */

namespace Maatify\Portal\Language;

use \App\DB\DBS\DbPortalHandler;

class DbLanguage extends DbPortalHandler
{

    public const TABLE_NAME                 = 'language';
    public const TABLE_ALIAS                = self::TABLE_NAME;
    public const IDENTIFY_TABLE_ID_COL_NAME = 'language_id';
    public const LOGGER_TYPE                = self::TABLE_NAME;
    public const LOGGER_SUB_TYPE            = '';
    public const COLS                       = [
        self::IDENTIFY_TABLE_ID_COL_NAME => 1,
        'name'                           => 0,
        'short_name'                     => 0,
        'code'                           => 0,
        'locale'                         => 0,
        'image'                          => 0,
        'directory'                      => 0,
        'sort'                           => 1,
        'status'                         => 1,
    ];
    const        IMAGE_FOLDER               = self::TABLE_NAME;

    protected string $tableName = self::TABLE_NAME;
    protected string $tableAlias = self::TABLE_ALIAS;
    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $logger_type = self::LOGGER_TYPE;
    protected string $logger_sub_type = self::LOGGER_SUB_TYPE;
    protected array $cols = self::COLS;
    protected string $image_folder = self::IMAGE_FOLDER;
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function JOINTableAdd($table_name): string
    {
        return " INNER JOIN `$this->tableName` ON `$this->tableName`.`$this->identify_table_id_col_name` = `$table_name`.`$this->identify_table_id_col_name` ";
    }

    public function JoinShortName(): string
    {
        return "`$this->tableName`.`short_name` as language";
    }

    public function JoinShortNameWithoutAs(): string
    {
        return "`$this->tableName`.`short_name`";
    }

    public function GetCurrentLanguageId(string $short_code): int
    {
        if (! $id = (int)$this->ColThisTable('language_id', '`short_name` = ? ', [strtolower($short_code)])) {
            $id = 1;
        }

        return $id;
    }

    public function RowByID(): array
    {
        return $this->RowThisTable('*', "`$this->identify_table_id_col_name` = ? ", [$this->language_id]);
    }

    public function ShortNameByID(int $language_id): string
    {
        if ($short_name = $this->ColThisTable('short_name', "`$this->identify_table_id_col_name` = ? ", [strtolower($language_id)])) {
            return $short_name;
        }

        return '';
    }
}