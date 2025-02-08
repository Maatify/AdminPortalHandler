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
 * @Maatify   AdminPortalHandler :: PrivilegeRoles
 */

namespace Maatify\Portal\Privileges;

use Maatify\LanguagePortalHandler\DBHandler\ParentClassHandler;
use Maatify\PostValidatorV2\ValidatorConstantsTypes;
use Maatify\PostValidatorV2\ValidatorConstantsValidators;

class PrivilegeRoles extends ParentClassHandler
{
    const TABLE_NAME = 'privilege_roles';
    protected string $tableName = self::TABLE_NAME;
    const TABLE_ALIAS = '';
    protected string $tableAlias = self::TABLE_ALIAS;
    const IDENTIFY_TABLE_ID_COL_NAME = 'role_id';
    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    const LOGGER_TYPE = 'roles';
    protected string $logger_type = self::LOGGER_TYPE;
    protected string $logger_sub_type = self::LOGGER_TYPE;
    protected array $child_classe_languages = [PrivilegeRolesName::class];
    protected string $inner_language_name_class = PrivilegeRolesName::class;

    protected array $cols_to_add = [
        [ValidatorConstantsTypes::Comment, ValidatorConstantsTypes::String, ValidatorConstantsValidators::Require],
    ];

    protected array $cols_to_edit = [
        [ValidatorConstantsTypes::Comment, ValidatorConstantsTypes::String, ValidatorConstantsValidators::Require],
    ];

    protected array $cols_to_filter = [
        [self::IDENTIFY_TABLE_ID_COL_NAME, ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
        [ValidatorConstantsTypes::Comment, ValidatorConstantsTypes::String, ValidatorConstantsValidators::Optional],
    ];
    protected array $inner_language_tables = [PrivilegeRolesName::class];

    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}