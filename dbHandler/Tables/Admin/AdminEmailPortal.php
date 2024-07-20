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

use App\Assist\AppFunctions;
use \App\Assist\Encryptions\ConfirmEmailEncryption;
use App\Assist\Jwt\JWTAssistance;
use Maatify\CronEmail\CronEmailRecord;
use Maatify\CronTelegramBotAdmin\CronTelegramBotAdminRecord;
use Maatify\Json\Json;
use Maatify\PostValidatorV2\ValidatorConstantsTypes;
use Maatify\PostValidatorV2\ValidatorConstantsValidators;

class AdminEmailPortal extends AdminEmail
{
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private array $log;

    public function __construct()
    {
        parent::__construct();
        $this->row_id = AdminLoginToken::obj()->GetAdminID();
        $this->log = [$this->identify_table_id_col_name=>AdminLoginToken::obj()->GetAdminID()];
        $this->logger_keys = [$this->identify_table_id_col_name => AdminLoginToken::obj()->GetAdminID()];
    }

    public function UpdateEmail(): void
    {
        $this->row_id = $this->ValidatePostedTableId();
        $this->logger_keys = [$this->identify_table_id_col_name => $this->row_id];
        $this->cols_to_edit = [
            [
                ValidatorConstantsTypes::Email,
                ValidatorConstantsTypes::Email,
                ValidatorConstantsValidators::Require
            ],
        ];
        $this->UpdateByPostedId();

    }

    public function ChangeEmail(): void
    {
        $password = $this->postValidator->Require('password', 'password');
        $email = $this->postValidator->Require('email', 'email');
        if (AdminPassword::obj()->Check(AdminLoginToken::obj()->GetAdminID(), $password)) {
            if (AdminLoginToken::obj()->GetAdminEmail() == $email) {
                Json::ErrorNoUpdate($this->class_name . __LINE__);
            } else {
                if($this->EmailIsExist($email)){
                    Json::Exist('email', line: $this->class_name . __LINE__);
                }else{
                    $this->Set(AdminLoginToken::obj()->GetAdminID(), $email, AdminLoginToken::obj()->GetAdminName(), AdminLoginToken::obj()->GetAdminUsername());
                    if(!empty(AdminLoginToken::obj()->GetTelegramStatus())) {
                        CronTelegramBotAdminRecord::obj()->RecordMessage(
                            AdminLoginToken::obj()->GetAdminID(),
                            AdminLoginToken::obj()->GetTelegramChatID(),
                            'Your Email Was Changed Successfully'
                            . PHP_EOL
                            . "new email: " . $email
                            . PHP_EOL
                            . "ip: " . AppFunctions::IP()
                            . PHP_EOL
                            . "time: " . AppFunctions::CurrentDateTime()
                        );
                    }
                    $this->log['email'] = ['from'=> AdminLoginToken::obj()->GetAdminEmail(), 'to'=>$email];
                    $this->AdminLogger($this->log, [['email', AdminLoginToken::obj()->GetAdminEmail(), $email]], 'Update');
                    $this->Success( $this->class_name . __LINE__);
                }
            }
        } else {
            AdminFailedLogin::obj()->Failed(AdminLoginToken::obj()->GetAdminUsername());
            Json::Incorrect('credentials', line: $this->class_name . __LINE__);
        }
    }

    public function EmailConfirm(): void
    {
        $code = $this->postValidator->Require('code', 'code');
        $this->Confirm($code);
        if(!empty(AdminLoginToken::obj()->GetTelegramStatus())) {
            CronTelegramBotAdminRecord::obj()->RecordMessage(
                AdminLoginToken::obj()->GetAdminID(),
                AdminLoginToken::obj()->GetTelegramChatID(),
                'Your Email Was Confirmed Successfully'
                . PHP_EOL
                . "time: " . AppFunctions::CurrentDateTime()
            );
        }
        $this->log['email'] = ['from' => 'Unverified', 'to' => 'Verified'];
        $this->AdminLogger($this->log, [], 'Verify');
        $this->Success( $this->class_name . __LINE__);
    }

    public function EmailConfirmResend(): void
    {
        $this->ConfirmToken();
        $this->log['details'] = 'Email Confirm Resend';
        $this->AdminLogger($this->log, [], 'Resend');
        $this->Success( $this->class_name . __LINE__);
    }

    private function Success(string $line): void
    {
        Json::Success(AdminLoginToken::obj()->HandleAdminResponse(AdminLoginToken::obj()->ValidateAdminToken()), $line);
    }


    /**
    ========================================= Confirm Code Hash =========================================
     **/
    private function Confirm(string $code): void
    {
        $admin_id = AdminLoginToken::obj()->GetAdminID();
        $username = AdminLoginToken::obj()->GetAdminUsername();
        if($row = $this->RowThisTable('*', "`$this->identify_table_id_col_name` = ? ", [$admin_id])){
            if($row['confirmed']){
                Json::EmailAlreadyVerified($this->class_name . __LINE__);
            }else{
                if(!empty($row['token']) && $code == $this->ConfirmCodeDecode($row['token'])){
                    $this->Edit(['confirmed'=> 1, 'token' => ''],"`$this->identify_table_id_col_name` = ?", [$admin_id]);
                    AdminFailedLogin::obj()->Success($username);
                }else{
                    AdminFailedLogin::obj()->Failed($username);
                    Json::Incorrect('code', line: $this->class_name . __LINE__);
                }
            }
        }
    }

    private function ConfirmCodeDecode(string $code): string
    {
        $code = (new ConfirmEmailEncryption())->DeHashed($code);
        return (string) base64_decode($code);
    }

    private function ConfirmToken(): void
    {
        $admin_id = AdminLoginToken::obj()->GetAdminID();
        $name = AdminLoginToken::obj()->GetAdminName();
        $username = AdminLoginToken::obj()->GetAdminUsername();
        $email = AdminLoginToken::obj()->GetAdminEmail();
        if($admin = $this->RowThisTable('*', "`$this->identify_table_id_col_name` = ?", [$admin_id])) {
            if(empty($admin['confirmed'])) {
                $this->RenewTokenAndSendEmail($admin_id, $username, $name, $email);
            }else{
                Json::EmailAlreadyVerified($this->class_name . __LINE__);
            }
        }
    }

    private function Set(int $admin_id, string $email, string $name, string $username): void
    {
        if($this->Edit(['email'=>$email, 'confirmed'=>0], "`$this->identify_table_id_col_name` = ?", [$admin_id])){
            $this->RenewTokenAndSendEmail($admin_id, $username, $name, $email);
        }
    }

    private function RenewTokenAndSendEmail(int $admin_id, string $username, string $name, string $email): void
    {
        $otp = $this->OTP();
        $this->Edit(['token' => $this->HashedOTP($otp)], "`$this->identify_table_id_col_name` = ?", [$admin_id]);
        JWTAssistance::obj()->TokenConfirmMail($admin_id, $username);
        CronEmailRecord::obj()->RecordConfirmCode(0, $email, $otp, $name);
    }
}