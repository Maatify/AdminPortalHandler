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
 * @Maatify   AdminPortalHandler :: AdminEmail
 */

namespace Maatify\Portal\Admin\Email;

use App\Assist\Encryptions\ConfirmEmailEncryption;
use Maatify\CronEmail\CronEmailAdminRecord;
use Maatify\Functions\GeneralFunctions;
use Maatify\LanguagePortalHandler\DBHandler\ParentClassHandler;
use Maatify\Portal\Admin\Admin;

class AdminEmail extends ParentClassHandler
{
    const TABLE_NAME = 'a_email';
    protected string $tableName = self::TABLE_NAME;
    const IDENTIFY_TABLE_ID_COL_NAME = Admin::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    const LOGGER_TYPE = 'Email';


    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        parent::__construct();
        $this->logger_type = Admin::LOGGER_TYPE;
        $this->logger_sub_type = self::LOGGER_TYPE;

    }

    public function EmailIsExist(string $email): string
    {
        return self::ColThisTable("`$this->identify_table_id_col_name`",
            "LCASE(`email`) = ? LIMIT 1 ",
            [strtolower($email)]
        );
    }

    public function SetUser(int $admin_id, string $email, string $name): void
    {
        if($this->Edit(['email'=>$email, 'e_confirmed'=>0], "`$this->identify_table_id_col_name` = ?", [$admin_id])){
            $otp = $this->OTP();
            $this->Edit(['token' => $this->HashedOTP($otp)], "`$this->identify_table_id_col_name` = ?", [$admin_id]);
            CronEmailAdminRecord::obj()->RecordConfirmCode($admin_id, $email, $otp, $name);
        }
    }

    protected function OTP(): string
    {
        return GeneralFunctions::GenerateOTP(6);
    }

    protected function HashedOTP(string $otp): string
    {
        $code = base64_encode($otp);
        return (new ConfirmEmailEncryption())->Hash($code);
    }
}