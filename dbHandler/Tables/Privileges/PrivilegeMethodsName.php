<?php
/**
 * @PHP       Version >= 8.0
 * @copyright ©2024 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-04-18 8:55 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/AdminPortalHandler  view project on GitHub
 * @Maatify   DB :: AdminPortalHandler
 */

namespace Maatify\Portal\Privileges;

use Maatify\Portal\DbHandler\SubClassLanguageHandler;
use Maatify\Portal\Language\DbLanguage;
use Maatify\PostValidatorV2\ValidatorConstantsTypes;

class PrivilegeMethodsName extends SubClassLanguageHandler
{
    const TABLE_NAME = 'privilege_methods_name';
    protected string $tableName = self::TABLE_NAME;
    const TABLE_ALIAS = 'method';
    protected string $tableAlias = self::TABLE_ALIAS;
    const IDENTIFY_TABLE_ID_COL_NAME = PrivilegeMethods::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    const LOGGER_TYPE = PrivilegeMethods::LOGGER_TYPE;
    protected string $logger_type = self::LOGGER_TYPE;
    protected string $logger_sub_type = 'name';

    protected array $cols_to_add = [[ValidatorConstantsTypes::Name, ValidatorConstantsTypes::Name, '']];
    protected string $parent_class = PrivilegeMethods::class;
    protected array $cols = [
        self::IDENTIFY_TABLE_ID_COL_NAME => 1,
        DbLanguage::IDENTIFY_TABLE_ID_COL_NAME => 1,
        'name' => 0,
    ];
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}