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

use App\Assist\Jwt\JWTAssistance;
use Maatify\Json\Json;

class Admin2FAPortal extends Admin2FA
{
    private static self $instance;
    private string $code;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    public function AllowUserToNewAuth(): void
    {
        $this->row_id = $this->ValidatePostedTableId();
        AdminPortal::obj()->UserForEdit($this->row_id);
        if ($this->current_row['auth']) {
            Admin2FA::obj()->RemoveAuthCode();
            $this->logger_keys = [$this->identify_table_id_col_name => $this->row_id];
            $log = $this->logger_keys;
            $log['remove'] = 'Remove Current Auth Code';
            $changes[] = ['auth', 'set', 'unset'];
            $this->Logger($log, $changes, $_GET['action']);
            Json::Success(AdminPortal::obj()->UserForEdit($this->row_id), line: $this->class_name . __LINE__);
        } else {
            Json::ErrorNoUpdate(__LINE__);
        }
    }

    public function Auth(): void
    {
        $this->code = $this->postValidator->Require('code', 'code');
        $admin = $this->CheckCode($this->code);
        $this->logger_keys = [$this->identify_table_id_col_name => $admin[$this->identify_table_id_col_name]];
        $this->row_id = $admin[$this->identify_table_id_col_name];
        $log = [$this->identify_table_id_col_name => $admin[$this->identify_table_id_col_name], 'details' => 'Success Login with Two-Factor-Authenticator'];
        $this->AdminLogger($log, [], 'Login');
        AdminPassword::obj()->ValidateTempPass($admin[$this->identify_table_id_col_name]);
        Json::Success(AdminLoginToken::obj()->HandleAdminResponse($admin));
    }


    public function AuthRegister(): void
    {
        $this->code = $this->postValidator->Require('code', 'code');
        $admin = $this->RegisterNewCode($this->code);
        $this->logger_keys = [$this->identify_table_id_col_name => $admin[$this->identify_table_id_col_name]];
        $this->row_id = $admin[$this->identify_table_id_col_name];
        $log = [$this->identify_table_id_col_name => $admin[$this->identify_table_id_col_name], 'details' => 'Success Register of Two-Factor-Authenticator'];
        $this->AdminLogger($log, [['auth', '', 'set']], 'Register');
        $admin = AdminLoginToken::obj()->ValidateAdminToken();
        AdminPassword::obj()->ValidateTempPass($admin[$this->identify_table_id_col_name]);
        Json::Success(AdminLoginToken::obj()->HandleAdminResponse($admin));
    }

    private function CheckCode(string $code): array
    {
        if($admin = $this->ValideToken()){
            if($this->ValidateCode($code, $this->AuthDecode($admin['auth']), $admin['username'])){
                JWTAssistance::obj()->JwtTokenHash($admin[$this->identify_table_id_col_name], $admin['username']);
                return $admin;
            }
        }
        return [];
    }

    private function RegisterNewCode(string $code): array
    {
        if($admin = $this->ValideToken()){
            if(empty($admin['auth'])) {
                if (! empty($admin['secret'])) {
                    if ($this->ValidateCode($code, $admin['secret'], $admin['username'])) {
                        if ($this->NewAuthRecord($admin[$this->identify_table_id_col_name],
                            $admin['secret'])) {
                            JWTAssistance::obj()->JwtTokenHash($admin[$this->identify_table_id_col_name], $admin['username']);
                            return $admin;
                        }
                    }
                }
            }else{
                Json::Exist('code', line: $this->class_name . __LINE__);
            }
        }
        Json::ReLogin($this->class_name . __LINE__);
        return [];
    }


    private function NewAuthRecord(int $admin_id, string $authCode): bool
    {
        if ($this->AuthCanUse($authCode)) {
            if ($this->Edit(['auth' => $this->AuthEncode($authCode)], " `$this->identify_table_id_col_name` = ?", [$admin_id])) {
                return true;
            }
        }
        return false;
    }

    private function AuthCanUse($authCode): bool
    {
        $authCode = $this->AuthEncode($authCode);
        if (self::RowThisTable("`$this->identify_table_id_col_name`",
            "`auth` = ? LIMIT 1", [$authCode])) {
            return false;
        }
        else return $authCode;
    }
}