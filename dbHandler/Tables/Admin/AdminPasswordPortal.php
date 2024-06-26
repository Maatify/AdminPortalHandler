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

use Maatify\Json\Json;

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
        $otp = AdminPassword::obj()->SetTemp($this->row_id, $user['name'], $user['email']);
        $this->logger_keys = [$this->identify_table_id_col_name => $this->row_id];
        $log = $this->logger_keys;
        $log['change'] = 'Generate new default password';
        $changes[] = ['password', '{{encrypted}}', 'new default password: ' . $otp];
        $this->Logger($log, $changes, $_GET['action']);
        $user['password'] = $otp;
        Json::Success($user, line: $this->class_name . __LINE__);
    }
}