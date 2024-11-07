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
 * @Maatify   AdminPortalHandler :: AdminPasswordPortal
 */

namespace Maatify\Portal\Admin\Password;

use Maatify\Json\Json;
use Maatify\Portal\Admin\AdminFailedLogin;
use Maatify\Portal\Admin\AdminLoginToken;
use Maatify\Portal\Admin\AdminPortal;
use Maatify\Portal\Admin\TelegramBot\AlertAdminTelegramBot;

class AdminPasswordPortal extends AdminPassword
{
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function ChangePassword(): void
    {
        $password = $this->postValidator->Require('password', 'password');
        $password_old = $this->postValidator->Require('old_password', 'password');
        if ($password == $password_old) {
            Json::ErrorNoUpdate(__LINE__);
        } else {
            if ($this->Check(AdminLoginToken::obj()->GetAdminID(), $password_old)) {
                $this->row_id = AdminLoginToken::obj()->GetAdminID();
                $this->logger_keys = [$this->identify_table_id_col_name => $this->row_id];
                $log = $this->logger_keys;
                $log['change'] = 'Change Password';
                $this->AdminLogger($log, [['password', 'encrypted', 'encrypted']], $_GET['action']);
                $this->Set(AdminLoginToken::obj()->GetAdminID(), $password);

                if(!empty($user['telegram_status'])){
                    AlertAdminTelegramBot::obj()->alertMessageOfAgent(
                        $this->row_id,
                        (int)$user['telegram_chat_id'],
                        'Your Password was Changed Successfully'
                    );
                }
                AdminLoginToken::obj()->LogoutSilent();
                Json::ReLogin(__LINE__);
            } else {
                AdminFailedLogin::obj()->Failed(AdminLoginToken::obj()->GetAdminUsername());
                Json::Incorrect('credentials', line: $this->class_name . __LINE__);
            }
        }
    }



    public function SetUserNewPassword(): void
    {
        $this->row_id = $this->ValidatePostedTableId();
        $user = AdminPortal::obj()->UserForEdit($this->row_id);
        $otp = AdminPassword::obj()->SetTemp($this->row_id, $user['name'], $user['email'], $user['phone']);
        if(!empty($user['telegram_status']) && !empty($user['telegram_chat_id'])){
            AlertAdminTelegramBot::obj()->alertTempPassword($this->row_id, (int)$user['telegram_chat_id'], $otp);
        }
        $this->logger_keys = [$this->identify_table_id_col_name => $this->row_id];
        $log = $this->logger_keys;
        $log['change'] = 'Generate new default password';
        $changes[] = ['password', '{{encrypted}}', 'new default password: ' . $otp];
        $this->Logger($log, $changes, $_GET['action']);
        $user['password'] = $otp;
        Json::Success($user, line: $this->class_name . __LINE__);
    }
}