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

use \App\Assist\DefaultPassword;
use \App\Assist\Encryptions\AdminPasswordEncryption;
use Maatify\CronEmail\CronEmailRecord;
use Maatify\CronSms\CronSmsRecord;
use Maatify\Json\Json;
use Maatify\Portal\DbHandler\ParentClassHandler;

class AdminPassword extends ParentClassHandler
{
    const TABLE_NAME = 'a_pass';
    protected string $tableName = self::TABLE_NAME;
    const IDENTIFY_TABLE_ID_COL_NAME = Admin::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $logger_type = Admin::LOGGER_TYPE;
    protected string $logger_sub_type = 'Password';

    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    public function Set(int $admin_id, string $password): bool
    {
        return $this->Edit(['password'=>$this->HashPassword($password), 'is_temp'=>0], "`$this->identify_table_id_col_name` = ?", [$admin_id]);
    }

    public function SetTemp(int $admin_id, string $name, string $email, string $phone): string
    {
        $otp = DefaultPassword::GenerateAdminDefaultPassword();
        $this->Edit(['password'=>$this->HashPassword($otp), 'is_temp'=>1], "`$this->identify_table_id_col_name` = ?", [$admin_id]);
        if(!empty($email)) {
            //            Mailer::obj()->TempPassword($name, $email, $otp);
            CronEmailRecord::obj()->RecordTempPassword(0, $email, $otp, $name);
            if(!empty($_ENV['IS_SMS_ACTIVATE'])){
                CronSmsRecord::obj()->RecordPassword(0, $phone, $otp);
            }
        }
        return $otp;
    }

    public function SetAllMaster(string $password): bool
    {
        return $this->Edit(['password'=>$this->HashPassword($password)], "`$this->identify_table_id_col_name` <= ?", [AdminPrivilegeHandler::obj()->MasterIds()]);
    }

    public function Check(int $admin_id, string $password): string
    {
        if($a_pass = $this->ColThisTable('`password`', "`$this->identify_table_id_col_name` = ?", [$admin_id])){
            return $this->CheckPassword($password, $a_pass);
        }
        return '';
    }

    private function PasswordSecretKeyEncode($code): string
    {
        $code = base64_encode($code);
        return (new AdminPasswordEncryption())->Hash($code);
    }
    private function PasswordSecretKeyDecode($code): string
    {
        $code = (new AdminPasswordEncryption())->DeHashed($code);
        return (string)base64_decode($code);
    }
    public function HashPassword($password): string
    {
        return self::PasswordSecretKeyEncode(password_hash($password, PASSWORD_DEFAULT));
    }
    private function CheckPassword($password, $hashedPassword): bool
    {
        return password_verify($password, self::PasswordSecretKeyDecode($hashedPassword));
    }

    public function ValidateTempPass(int $admin_id): void
    {
        if($col = $this->ColThisTable('is_temp', "`$this->identify_table_id_col_name` = ? AND `is_temp` = ?", [$admin_id, 1])){
            Json::GoToMethod('ChangePassword', line: $this->class_name . __LINE__);
        }
    }
}