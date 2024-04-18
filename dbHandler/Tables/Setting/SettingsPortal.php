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

namespace Maatify\Portal\Setting;

use Maatify\Json\Json;
use Maatify\Portal\DbHandler\ParentClassHandler;
use Maatify\PostValidatorV2\ValidatorConstantsTypes;
use Maatify\PostValidatorV2\ValidatorConstantsValidators;

class SettingsPortal extends ParentClassHandler
{
    public const IDENTIFY_TABLE_ID_COL_NAME = Settings::IDENTIFY_TABLE_ID_COL_NAME;
    public const TABLE_NAME                 = Settings::TABLE_NAME;
    public const TABLE_ALIAS                = Settings::TABLE_ALIAS;
    public const LOGGER_TYPE                = Settings::LOGGER_TYPE;
    public const LOGGER_SUB_TYPE            = Settings::LOGGER_SUB_TYPE;
    public const COLS                       = Settings::COLS;

    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $tableName = self::TABLE_NAME;
    protected string $tableAlias = self::TABLE_ALIAS;
    protected string $logger_type = self::LOGGER_TYPE;
    protected string $logger_sub_type = self::LOGGER_SUB_TYPE;
    protected array $cols = self::COLS;
    protected array $inner_language_tables = [];
    protected string $inner_language_name_class = '';

    protected array $cols_to_add = [
        ['type', ValidatorConstantsTypes::Col_Name, ValidatorConstantsValidators::Require],
        ['isEditable', ValidatorConstantsTypes::Bool, ValidatorConstantsValidators::Require],
        ['default_key', ValidatorConstantsTypes::Col_Name, ValidatorConstantsValidators::Require],
        ['default_value', ValidatorConstantsTypes::Col_Name, ValidatorConstantsValidators::AcceptEmpty],
        ['comment', ValidatorConstantsTypes::Col_Name, ValidatorConstantsValidators::AcceptEmpty],
    ];

    protected array $cols_to_edit = [
        ['default_value', ValidatorConstantsTypes::Col_Name, ValidatorConstantsValidators::AcceptEmpty],
    ];

    protected array $cols_to_filter = [
        ['type', ValidatorConstantsTypes::Col_Name, ValidatorConstantsValidators::Optional],
        ['isEditable', ValidatorConstantsTypes::Bool, ValidatorConstantsValidators::Optional],
        ['default_key', ValidatorConstantsTypes::Col_Name, ValidatorConstantsValidators::Optional],
    ];

    protected array $child_classes = [];
    protected array $child_classe_languages = [];
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function AllPaginationThisTableFilter(string $order_with_asc_desc = ''): void
    {
        [$tables, $cols] = $this->HandleThisTableJoins();
        $result = $this->ArrayPaginationThisTableFilter($tables,
            $cols,
//            " ORDER BY `$this->identify_table_id_col_name` ASC"
        );
        $result['data'] = array_map(
            function (array $data) {
                $data['default_value'] =
                    match ($data['type']) {
                        'bool', 'int' => (int)$data['default_value'],
                        'float', 'number' => (float)$data['default_value'],
                        default => (string)$data['default_value'],
                    };

                return $data;
            },
            $result['data']
        );
        Json::Success(
            $result,
            line: $this->class_name . __LINE__
        );
    }

    public function UpdateByPostedId(): void
    {
        $this->ValidatePostedTableId();
        if (empty($this->current_row['isEditable'])) {
            Json::NotAllowedToUse($this->identify_table_id_col_name, $this->identify_table_id_col_name . ' Not Editable', $this->class_name . __LINE__);
        } else {
            $type = match ($this->current_row['type']) {
                'int' => ValidatorConstantsTypes::Int,
                'number', 'float' => ValidatorConstantsTypes::Float,
                'date' => ValidatorConstantsTypes::Date,
                'datetime' => ValidatorConstantsTypes::DateTime,
                'bool' => ValidatorConstantsTypes::Bool,
                default => ValidatorConstantsTypes::String,
            };
            $this->cols_to_edit =
                [
                    ['default_value', $type, ValidatorConstantsValidators::AcceptEmpty],
                ];
            parent::UpdateByPostedId();
        }
    }
}