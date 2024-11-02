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
 * @Maatify   AdminPortalHandler :: PrivilegeMethods
 */

namespace Maatify\Portal\Privileges;

use Maatify\Json\Json;
use Maatify\Portal\DbHandler\ParentClassHandler;
use Maatify\PostValidatorV2\ValidatorConstantsTypes;
use Maatify\PostValidatorV2\ValidatorConstantsValidators;

class PrivilegeMethods extends ParentClassHandler
{
    const TABLE_NAME = 'privilege_methods';
    protected string $tableName = self::TABLE_NAME;
    const TABLE_ALIAS = '';
    protected string $tableAlias = self::TABLE_ALIAS;
    const IDENTIFY_TABLE_ID_COL_NAME = 'method_id';
    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    const LOGGER_TYPE = 'method';
    protected string $logger_type = self::LOGGER_TYPE;
    protected string $logger_sub_type = self::LOGGER_TYPE;
    protected array $child_classe_languages = [PrivilegeMethodsName::class];
    protected array $cols_to_add = [
        [ValidatorConstantsTypes::Comment, ValidatorConstantsTypes::String, ValidatorConstantsValidators::Require],
        ['method', ValidatorConstantsTypes::String, ValidatorConstantsValidators::Require],
        ['sort', ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
        ['page', ValidatorConstantsTypes::String, ValidatorConstantsValidators::Require],
    ];

    protected array $cols_to_edit = [
        [ValidatorConstantsTypes::Comment, ValidatorConstantsTypes::String, ValidatorConstantsValidators::Optional],
        ['method', ValidatorConstantsTypes::String, ValidatorConstantsValidators::Optional],
        ['sort', ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
        ['page', ValidatorConstantsTypes::String, ValidatorConstantsValidators::Optional],
    ];

    protected array $cols_to_filter = [
        [self::IDENTIFY_TABLE_ID_COL_NAME, ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
        [ValidatorConstantsTypes::Comment, ValidatorConstantsTypes::String, ValidatorConstantsValidators::Optional],
        ['method', ValidatorConstantsTypes::String, ValidatorConstantsValidators::Optional],
        ['page', ValidatorConstantsTypes::String, ValidatorConstantsValidators::Optional],
    ];

    protected array $inner_language_tables = [PrivilegeMethodsName::class];
    protected string $inner_language_name_class = PrivilegeMethodsName::class;
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
        $page = $this->postValidator->Require('page', ValidatorConstantsTypes::Col_Name, $this->class_name . __LINE__);
        $method = $this->postValidator->Require('method', ValidatorConstantsTypes::Col_Name, $this->class_name . __LINE__);

        if($this->RowIsExistThisTable('`page` = ? AND `method` = ? ',
        [
            $page,
            $method
        ])){
            Json::Exist('method', 'method Already Exist', $this->class_name . __LINE__);
        }else{
            parent::Record();
        }
    }

    public function UpdateByPostedId(): void
    {
        $this->ValidatePostedTableId();
        $page = $this->postValidator->Optional('page', ValidatorConstantsTypes::Col_Name, $this->class_name . __LINE__);
        $method = $this->postValidator->Optional('method', ValidatorConstantsTypes::Col_Name, $this->class_name . __LINE__);
        if(isset($_POST['page']) && $page != $this->current_row['page']){
            $new_page = $page;
        }else{
            $page = $this->current_row['page'];
        }
        if(isset($_POST['method']) && $method != $this->current_row['method']){
            $new_method = $method;
        }else{
            $method = $this->current_row['method'];
        }
        if(!empty($new_page) || !empty($new_method)){
            if($this->RowIsExistThisTable('`page` = ? AND `method` = ? ', [$page, $method])){
                Json::Exist('method', 'method Already Exist', $this->class_name . __LINE__);
            }
        }
        parent::UpdateByPostedId();
    }
}