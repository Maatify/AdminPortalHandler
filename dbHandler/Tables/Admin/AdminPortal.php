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

namespace Maatify\Portal\Admin;

use \App\Assist\AppFunctions;
use App\Assist\Jwt\JwtAdminKey;
use Exception;
use Maatify\GoogleRecaptcha\V3\GoogleRecaptchaV3Json;
use Maatify\Json\Json;
use Maatify\Logger\Logger;
use Maatify\Portal\DbHandler\ParentClassHandler;
use Maatify\PostValidatorV2\ValidatorConstantsTypes;
use Maatify\PostValidatorV2\ValidatorConstantsValidators;

class AdminPortal extends ParentClassHandler
{
    const TABLE_NAME = Admin::TABLE_NAME;
    protected string $tableName = self::TABLE_NAME;
    protected string $logger_type = Admin::LOGGER_TYPE;
    const IDENTIFY_TABLE_ID_COL_NAME = Admin::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $tableAlias = 'user';
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    public function AdminLogin(): void
    {
        $this->logger_sub_type = 'Login';
        (new GoogleRecaptchaV3Json());
        $username = $this->postValidator->Require('username', 'username', $this->class_name . __LINE__);
        $password = $this->postValidator->Require('password', 'password', $this->class_name . __LINE__);
        if ($admin = $this->Login($username)) {
            if (AdminPassword::obj()->Check($admin[$this->identify_table_id_col_name], $password)) {
                $log = [$this->identify_table_id_col_name];
                $this->logger_keys = $log;
                unset($admin['password']);
                if (! empty($admin['status'])) {
                    AdminLoginToken::obj()->GenerateToken($admin[$this->identify_table_id_col_name], $admin['username']);
                    if (! empty($admin['confirmed']) || empty($_ENV['EMAIL_CONFIRM_REQUIRED'])) {
                        if ($_ENV['AUTH_2FA_STATUS']) {
                            if ($_ENV['AUTH_2FA_REQUIRED'] || AdminPrivilege::obj()->IsMaster($admin[$this->identify_table_id_col_name]) || $admin['is_admin'] || $admin['isAuthRequired']) {
                                try {
                                    Admin2FA::obj()->ResponseAuthMov($admin);
                                } catch (Exception $e) {
                                    Logger::RecordLog($e, 'auth_move');
                                    Json::TryAgain($this->class_name . __LINE__);
                                }
                            } else {
                                $log['details'] = 'Success Login';
                                $this->AdminLogger($log, [], 'Login');
                                JwtAdminKey::obj()->JwtTokenHash($admin[$this->identify_table_id_col_name], $admin['username']);
                                AdminPassword::obj()->ValidateTempPass($admin[$this->identify_table_id_col_name]);
                                AdminFailedLogin::obj()->Success($admin['username']);
                            }
                        } else {
                            $log['details'] = 'Success Login';
                            $this->AdminLogger($log, [], 'Login');
                            JwtAdminKey::obj()->JwtTokenHash($admin[$this->identify_table_id_col_name], $admin['username']);
                            AdminPassword::obj()->ValidateTempPass($admin[$this->identify_table_id_col_name]);
                            AdminFailedLogin::obj()->Success($admin['username']);
                        }
                        Json::Success(AdminLoginToken::obj()->HandleAdminResponse($admin), line: $this->class_name . __LINE__);
                    } else {
                        JwtAdminKey::obj()->TokenConfirmMail($admin[$this->identify_table_id_col_name], $admin['username']);
                        Json::GoToMethod('EmailConfirm', 'Please Confirm Your Email', line: $this->class_name . __LINE__);
                    }
                } else {
                    Json::SuspendedAccount();
                }
            } else {
                AdminFailedLogin::obj()->Failed($admin['username']);
                Json::Incorrect('credentials', line: $this->class_name . __LINE__);
            }
        } else {
            AdminFailedLogin::obj()->Failed($username);
            Json::Incorrect('credentials', line: $this->class_name . __LINE__);
        }
    }

    private function Login($username): array
    {
        $tb_email = AdminEmail::TABLE_NAME;
        $tb_admin_auth = Admin2FA::TABLE_NAME;

        return self::Row("`$this->tableName` 
        INNER JOIN `$tb_email` ON `$tb_email`.`$this->identify_table_id_col_name` = `$this->tableName`.`$this->identify_table_id_col_name` 
        INNER JOIN `$tb_admin_auth` ON `$tb_admin_auth`.`$this->identify_table_id_col_name` = `$this->tableName`.`$this->identify_table_id_col_name` 
        ",
            "`$this->tableName`.*, `$tb_email`.`email`, `$tb_email`.`confirmed`, `$tb_admin_auth`.`auth`, `$tb_admin_auth`.`isAuthRequired`",
            "LCASE(`$this->tableName`.`username`) = ? LIMIT 1 ",
            [strtolower($username)]);
    }


    public function UsernameIsExist(string $username): string
    {
        return self::ColThisTable("`$this->identify_table_id_col_name`",
            "LCASE(`username`) = ? LIMIT 1 ",
            [strtolower($username)]);
    }

    public function AddNewAdmin(): void
    {
        $email = $this->postValidator->Require('email', 'email');
        $username = $this->postValidator->Require('username', 'username');
        $name = $this->postValidator->Require('name', 'name');
        if (AdminEmail::obj()->EmailIsExist($email)) {
            Json::Exist('email');
        }
        if ($this->UsernameIsExist($username)) {
            Json::Exist('username');
        }
        $this->cols_to_add = [
            [ValidatorConstantsTypes::Username, ValidatorConstantsTypes::Username, ValidatorConstantsValidators::Require],
            [ValidatorConstantsTypes::Name, ValidatorConstantsTypes::Name, ValidatorConstantsValidators::Require],
            ['is_admin', ValidatorConstantsTypes::Bool, ValidatorConstantsValidators::Optional],
            ['status', ValidatorConstantsTypes::Bool, ValidatorConstantsValidators::Optional],
        ];
        $this->row_id = $this->SilentRecord();
        if (! empty($this->row_id)) {
            $to_add = [$this->identify_table_id_col_name => $this->row_id];
            Admin2FA::obj()->Add($to_add);
            AdminEmail::obj()->Add($to_add);
            AdminPassword::obj()->Add($to_add);
            AdminEmail::obj()->SetUser($this->row_id, $email, $name);
            $otp = AdminPassword::obj()->SetTemp($this->row_id, $name, $email);
            AdminToken::obj()->Add($to_add);
            AdminInfo::obj()->Add(
                [
                    $this->identify_table_id_col_name => $this->row_id,
                    'reg_date'                        => AppFunctions::CurrentDateTime(),
                    'reg_by'                          => AdminLoginToken::obj()->GetAdminID(),
                ]);

            $user = $this->UserForEdit();
            $user['password'] = $otp;
            Json::Success($user);
        }
    }

    public function UserForEdit(int $admin_id = 0): array
    {
        if (! empty($admin_id)) {
            $this->row_id = $admin_id;
        }
        if (
            (AdminPrivilege::obj()->IsMaster($this->row_id) && ! AdminPrivilege::obj()->IsMaster(AdminLoginToken::obj()->GetAdminID()))
            || $this->row_id == AdminLoginToken::obj()->GetAdminID()
            || in_array($this->row_id, [1, 2])
        ) {
            Json::Forbidden($this->class_name . __LINE__);
        } else {
            [$tables, $cols] = $this->UsersTbsCols();
            $user = $this->Row(
                $tables,
                $cols,
                "`$this->tableName`.`$this->identify_table_id_col_name` = ? GROUP BY `$this->tableName`.`$this->identify_table_id_col_name` ORDER BY `$this->tableName`.`$this->identify_table_id_col_name` ASC",
                [$this->row_id]
            );
            if (! empty($user)) {
                return $user;
            } else {
                Json::Incorrect($this->identify_table_id_col_name, $this->identify_table_id_col_name . ' Not Found', $this->class_name . __LINE__);
            }
        }

        return [];
    }

    public function MyInfo(): void
    {
        [$tables, $cols] = $this->UsersTbsCols();

        Json::Success($this->Row(
            $tables,
            $cols,
            "`$this->tableName`.`$this->identify_table_id_col_name` = ? ",
            [AdminLoginToken::obj()->GetAdminID()]));
    }

    private function UsersTbsCols(): array
    {
        $tb_admin_emails = AdminEmail::TABLE_NAME;
        $tb_admin_auth = Admin2FA::TABLE_NAME;

        return ["`$this->tableName` 
            INNER JOIN `$tb_admin_emails` ON `$tb_admin_emails`.`$this->identify_table_id_col_name` = `$this->tableName`.`$this->identify_table_id_col_name` 
            INNER JOIN `$tb_admin_auth` ON `$tb_admin_auth`.`$this->identify_table_id_col_name` = `$this->tableName`.`$this->identify_table_id_col_name` 
            ",
                "`$this->tableName`.*, `$tb_admin_emails`.`email`, `$tb_admin_emails`.`confirmed`,  
            IF(`$tb_admin_auth`.`auth` = '', 0, 1) as auth,
            `$tb_admin_auth`.`isAuthRequired`"];
    }

    public function AllUsers(): void
    {
        [$tables, $columns] = $this->UsersTbsCols();
        $master_id = AdminPrivilege::obj()->MasterIds();
        if (AdminLoginToken::obj()->GetAdminID() <= $master_id) {
            $where_val = [0];
        } else {
            $where_val = [$master_id];
        }
        $where_to_add = '';
        if(isset($_POST['status']) && is_numeric($_POST['status']) && in_array($_POST['status'], [0,1])){
            $where_to_add .= 'AND `status` = ? ';
            $where_val[] = $_POST['status'];
        }
        if(isset($_POST['is_admin']) && is_numeric($_POST['is_admin']) && in_array($_POST['is_admin'], [0,1])){
            $where_to_add .= 'AND `is_admin` = ? ';
            $where_val[] = $_POST['is_admin'];
        }
        Json::Success(
            $this->PaginationHandler(
                $this->MaxIDThisTable(),
                $this->PaginationRows(
                    $tables,
                    $columns,
                    "`$this->tableName`.`$this->identify_table_id_col_name` > ? $where_to_add GROUP BY `$this->tableName`.`$this->identify_table_id_col_name` ORDER BY `$this->tableName`.`$this->identify_table_id_col_name` ASC",
                    $where_val
                )
            )
        );
    }


}