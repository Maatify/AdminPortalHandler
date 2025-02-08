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
 * @Maatify   AdminPortalHandler :: Privileges
 */

namespace Maatify\Portal\Privileges;


use Maatify\LanguagePortalHandler\DBHandler\AddRemoveTwoColsHandler;

class Privileges extends AddRemoveTwoColsHandler
{
    const TABLE_NAME                 = 'privileges';
    const Table_ALIAS                = '';
    const LOGGER_TYPE                = self::TABLE_NAME;
    const LOGGER_SUB_TYPE            = '';
    const IDENTIFY_TABLE_ID_COL_NAME = PrivilegeRoles::IDENTIFY_TABLE_ID_COL_NAME;
    const TABLE_SOURCE_CLASS         = PrivilegeRoles::class;
    const TABLE_DESTINATION_CLASS    = PrivilegeMethods::class;

    protected string $tableName = self::TABLE_NAME;
    protected string $tableAlias = self::Table_ALIAS;
    protected string $logger_type = self::LOGGER_TYPE;
    protected string $logger_sub_type = self::LOGGER_SUB_TYPE;
    protected string $table_source_class = self::TABLE_SOURCE_CLASS;

    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $table_destination_class = self::TABLE_DESTINATION_CLASS;

    protected array $cols_to_add = [
        // [name, type, default value usually '' ];
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