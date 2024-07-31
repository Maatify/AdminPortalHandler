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
    const TABLE_NAME  = 'language';

    protected string $tableName = self::TABLE_NAME;
    protected string $image_folder = 'language';
    const IDENTIFY_TABLE_ID_COL_NAME = 'language_id';
    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    const LOGGER_TYPE = 'language';
    protected string $logger_type = self::LOGGER_TYPE;
    protected string $logger_sub_type = '';

    protected array $cols = [
        self::IDENTIFY_TABLE_ID_COL_NAME => 1,
        'name' => 0,
        'short_name' => 0,
        'code' => 0,
        'locale' => 0,
        'image' => 0,
        'directory' => 0,
        'sort' => 1,
        'status' => 1,
    ];
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
        if(!$id = (int)$this->ColThisTable('language_id', '`short_name` = ? ', [strtolower($short_code)])){
            $id = 1;
        }
        return $id;
    }
}