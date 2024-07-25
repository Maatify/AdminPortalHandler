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

use Maatify\Json\Json;
use Maatify\Portal\DbHandler\ParentClassHandler;
use Maatify\PostValidatorV2\ValidatorConstantsTypes;
use Maatify\PostValidatorV2\ValidatorConstantsValidators;

class NotificationTypesPortal extends ParentClassHandler
{
    public const IDENTIFY_TABLE_ID_COL_NAME = NotificationTypes::IDENTIFY_TABLE_ID_COL_NAME;
    public const TABLE_NAME                 = NotificationTypes::TABLE_NAME;
    public const TABLE_ALIAS                = NotificationTypes::TABLE_ALIAS;
    public const LOGGER_TYPE                = NotificationTypes::LOGGER_TYPE;
    public const LOGGER_SUB_TYPE            = NotificationTypes::LOGGER_SUB_TYPE;
    public const COLS                       = NotificationTypes::COLS;
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
        ['type', ValidatorConstantsTypes::Col_Name, ValidatorConstantsValidators::Require],
        ['sms_status', ValidatorConstantsTypes::Status, ValidatorConstantsValidators::Optional],
        ['telegram_status', ValidatorConstantsTypes::Status, ValidatorConstantsValidators::Optional],
        ['app_status', ValidatorConstantsTypes::Status, ValidatorConstantsValidators::Optional],
    ];

    protected array $cols_to_edit = [
        ['type', ValidatorConstantsTypes::Col_Name, ValidatorConstantsValidators::Optional],
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

    public function Record(): void
    {
        $type = $this->postValidator->Require('type', ValidatorConstantsTypes::Col_Name, $this->class_name . __LINE__);
        $type-$this->jsonValidateTypeExist($type);
        parent::Record();
    }

    public function UpdateByPostedId(): void
    {
        $this->ValidatePostedTableId();
        $type = $this->postValidator->Optional('type', ValidatorConstantsTypes::Col_Name, $this->class_name . __LINE__);
        if(!empty($type) && $type !== $this->current_row['type']) {
            $type-$this->jsonValidateTypeExist($type);
        }
        parent::UpdateByPostedId();
    }

    private function jsonValidateTypeExist(string $type): void
    {
       if($this->RowIsExistThisTable('`type` = ? ', [$type])){
           Json::Exist('type', 'type already exists', $this->class_name . __LINE__);
       }
    }
}