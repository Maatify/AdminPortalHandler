<?php
/**
 * @PHP       Version >= 8.0
 * @copyright ©2024 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-07-25 1:46 PM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/AdminPortalHandler  view project on GitHub
 * @Maatify   DB :: AdminPortalHandler
 */
namespace Maatify\dBHandler\Tables;

use Maatify\Portal\DbHandler\ParentClassHandler;
use Maatify\PostValidatorV2\ValidatorConstantsTypes;
use Maatify\PostValidatorV2\ValidatorConstantsValidators;

class NotificationAdminTypesPortal extends ParentClassHandler
{
    public const IDENTIFY_TABLE_ID_COL_NAME = NotificationAdminTypes::IDENTIFY_TABLE_ID_COL_NAME;
    public const TABLE_NAME                 = NotificationAdminTypes::TABLE_NAME;
    public const TABLE_ALIAS                = NotificationAdminTypes::TABLE_ALIAS;
    public const LOGGER_TYPE                = NotificationAdminTypes::LOGGER_TYPE;
    public const LOGGER_SUB_TYPE            = NotificationAdminTypes::LOGGER_SUB_TYPE;
    public const COLS                       = NotificationAdminTypes::COLS;
    public const IMAGE_FOLDER               = self::TABLE_NAME;

    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $tableName = self::TABLE_NAME;
    protected string $tableAlias = self::TABLE_ALIAS;
    protected string $logger_type = self::LOGGER_TYPE;
    protected string $logger_sub_type = self::LOGGER_SUB_TYPE;
    protected array $cols = self::COLS;
    protected string $image_folder = self::IMAGE_FOLDER;

    // to use in list of AllPaginationThisTableFilter()
    protected array $inner_language_tables = [];

    // to use in list of source and destination rows with names
    protected string $inner_language_name_class = '';

    protected array $cols_to_add = [
        ['type', ValidatorConstantsTypes::Description, ValidatorConstantsValidators::Require],
        ['sms_status', ValidatorConstantsTypes::Status, ValidatorConstantsValidators::Require],
        ['telegram_status', ValidatorConstantsTypes::Status, ValidatorConstantsValidators::Require],
        ['app_status', ValidatorConstantsTypes::Status, ValidatorConstantsValidators::Require],
    ];

    protected array $cols_to_edit = [
        ['type', ValidatorConstantsTypes::Description, ValidatorConstantsValidators::Optional],
        ['sms_status', ValidatorConstantsTypes::Status, ValidatorConstantsValidators::Optional],
        ['telegram_status', ValidatorConstantsTypes::Status, ValidatorConstantsValidators::Optional],
        ['app_status', ValidatorConstantsTypes::Status, ValidatorConstantsValidators::Optional],
    ];

    protected array $cols_to_filter = [
        [self::IDENTIFY_TABLE_ID_COL_NAME, ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
        ['sms_status', ValidatorConstantsTypes::Status, ValidatorConstantsValidators::Optional],
        ['telegram_status', ValidatorConstantsTypes::Status, ValidatorConstantsValidators::Optional],
        ['app_status', ValidatorConstantsTypes::Status, ValidatorConstantsValidators::Optional],
    ];

    // to use in add if child classes no have language_id
    protected array $child_classes = [];

    // to use in add if child classes have language_id
    protected array $child_classe_languages = [];
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}