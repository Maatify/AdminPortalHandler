<?php
/**
 * @PHP       Version >= 8.0
 * @Liberary  AdminPortalHandler
 * @Project   AdminPortalHandler
 * @copyright ©2024 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-07-23 7:59 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/AdminPortalHandler  view project on GitHub
 * @Maatify   AdminPortalHandler :: AdminPhonePortal
 */

namespace Maatify\Portal\Admin\Phone;

use App\DB\DBS\DbPortalHandler;
use Maatify\Json\Json;
use Maatify\Portal\Admin\AdminLoginToken;
use Maatify\PostValidatorV2\ValidatorConstantsTypes;
use Maatify\PostValidatorV2\ValidatorConstantsValidators;

class AdminPhonePortal extends DbPortalHandler
{
    const TABLE_NAME                 = AdminPhone::TABLE_NAME;
    const TABLE_ALIAS                = AdminPhone::TABLE_ALIAS;
    const IDENTIFY_TABLE_ID_COL_NAME = AdminPhone::IDENTIFY_TABLE_ID_COL_NAME;
    const LOGGER_TYPE                = AdminPhone::LOGGER_TYPE;
    const LOGGER_SUB_TYPE            = AdminPhone::LOGGER_SUB_TYPE;
    const Cols           =
        [
            self::IDENTIFY_TABLE_ID_COL_NAME => 1,
            'phone'                      => 0,
        ];

    protected string $tableName = self::TABLE_NAME;
    protected string $tableAlias = self::TABLE_ALIAS;
    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $logger_type = self::LOGGER_TYPE;
    protected string $logger_sub_type = self::LOGGER_SUB_TYPE;
    private static self $instance;

    protected array $cols_to_edit = [
        [ValidatorConstantsTypes::Phone, ValidatorConstantsTypes::Phone, ValidatorConstantsValidators::Require]
    ];

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function PhoneIsExist(string $phone): bool
    {
        return $this->RowIsExistThisTable('`phone` = ? ', [$phone]);
    }

    public function ChangePhone(): void
    {
        $phone = $this->postValidator->Require(ValidatorConstantsTypes::Phone, ValidatorConstantsTypes::Phone, $this->class_name . __LINE__);
        $old_phone = AdminLoginToken::obj()->GetAdminPhone();
        if($old_phone === $phone) {
            Json::ErrorNoUpdate($this->class_name . __LINE__);
        }else{
            if($this->PhoneIsExist($phone)){
                Json::Exist(ValidatorConstantsTypes::Phone, ValidatorConstantsTypes::Phone . ' is already exist', $this->class_name . __LINE__);
            }
            $this->row_id = AdminLoginToken::obj()->GetAdminID();
            $this->logger_keys = [$this->identify_table_id_col_name => $this->row_id];
            $log = $this->logger_keys;
            $log['change'] = 'Change Phone';
            $this->AdminLogger($log, [['phone', $old_phone, $phone]], $_GET['action']);
            $this->Set(AdminLoginToken::obj()->GetAdminID(), $phone);
            Json::Success(line: $this->class_name . __LINE__);
        }
    }

    private function Set(int $admin_id, string $phone): void
    {
        $this->Edit([
            'phone' => $phone
        ], "`$this->identify_table_id_col_name` = ?", [$admin_id]);
    }

    public function UpdateByPostedId(): void
    {
        $this->ValidatePostedTableId();
        $phone = $this->postValidator->Require(ValidatorConstantsTypes::Phone, ValidatorConstantsTypes::Phone, $this->class_name . __LINE__);
        if($this->current_row['phone'] !== $phone) {
            if($this->PhoneIsExist($phone)){
                Json::Exist(ValidatorConstantsTypes::Phone, ValidatorConstantsTypes::Phone . ' is already exist', $this->class_name . __LINE__);
            }
        }
        parent::UpdateByPostedId();
    }
}