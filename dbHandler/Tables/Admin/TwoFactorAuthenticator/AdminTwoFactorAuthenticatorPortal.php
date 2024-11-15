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
 * @Maatify   AdminPortalHandler :: AdminTwoFactorAuthenticatorPortal
 */

namespace Maatify\Portal\Admin\TwoFactorAuthenticator;

use App\Assist\Jwt\JWTAssistance;
use Maatify\Json\Json;
use Maatify\Portal\Admin\AdminLoginToken;
use Maatify\Portal\Admin\AdminPortal;
use Maatify\Portal\Admin\Password\AdminPassword;
use Maatify\Portal\Admin\TelegramBot\AdminTelegramPassPortal;
use Maatify\Portal\Admin\TelegramBot\AlertAdminTelegramBot;

Class AdminTwoFactorAuthenticatorPortal extends AdminTwoFactorAuthenticator
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
        $admin = AdminPortal::obj()->UserForEdit($this->row_id);
        if ($this->current_row['auth']) {
            $this->RemoveAuthCode();
            if(!empty($_ENV['IS_TELEGRAM_ACTIVATE']) && !empty($admin['telegram_status'])) {
                AlertAdminTelegramBot::obj()->alertMessageOfAgent(
                    $admin[$this->identify_table_id_col_name],
                    (int) $admin['telegram_chat_id'],
                    'Your Two-Factor-Authenticator Code was removed from your account, please login to register new code'
                );
            }
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
        if(!empty($_ENV['IS_TELEGRAM_ACTIVATE']) && !empty($admin['telegram_status']) && !empty($admin['telegram_chat_id']) && !empty($admin['telegram_status_auth'])) {
            AdminTelegramPassPortal::obj()->sendAdminSessionStartByNewSession($admin[$this->identify_table_id_col_name], $admin['telegram_first_name'], $admin['telegram_chat_id']);
        }
        AdminPassword::obj()->ValidateTempPass($admin[$this->identify_table_id_col_name]);
        Json::Success(AdminLoginToken::obj()->HandleAdminResponse($admin));
    }

    public function AuthPassedViaTelegram(): void
    {
        if(!empty($_ENV['IS_TELEGRAM_ACTIVATE'])) {
            if ($admin = $this->ValideToken()) {
                $token = JWTAssistance::obj()->JwtValidationForSessionLogin($this->class_name . __LINE__);
                if (AdminTelegramPassPortal::obj()->validateAdminPassViaTelegramToken($admin[$this->identify_table_id_col_name], $token->token)) {
                    $this->logger_keys = [$this->identify_table_id_col_name => $admin[$this->identify_table_id_col_name]];
                    $this->row_id = $admin[$this->identify_table_id_col_name];
                    $log = [$this->identify_table_id_col_name => $admin[$this->identify_table_id_col_name], 'details' => 'Success Login with Telegram Authenticator'];
                    $this->AdminLogger($log, [], 'Login');
                    if (! empty($admin['telegram_status'])) {
                        AlertAdminTelegramBot::obj()->alertMessageOfAgent(
                            $admin[$this->identify_table_id_col_name],
                            (int) $admin['telegram_chat_id'],
                            'You Have Success Login with Telegram-Authenticator'
                        );
                    }
                    JWTAssistance::obj()->JwtTokenHash($admin[$this->identify_table_id_col_name], $admin['username']);
                    AdminPassword::obj()->ValidateTempPass($admin[$this->identify_table_id_col_name]);
                    Json::Success(AdminLoginToken::obj()->HandleAdminResponse($admin));
                }
            }
            Json::Incorrect('telegram_auth');
        }else{
            Json::NotAllowedToUse('telegram_auth', 'there is no telegram bot active ', $this->class_name . __LINE__);
        }
    }

    public function setMyAuthSecret(): void
    {
        $secret = $this->postValidator->Require('secret', 'digital_upper_letters');
        $this->SetAuth(AdminLoginToken::obj()->GetAdminID(),
            $secret);
    }

    public function SetAuth(int $admin_id, string $secret): void
    {
        $this->NewAuthRecord($admin_id,
            $secret);
        Json::Success();
    }

    public function checkMyAuthCode(): void
    {
        if(AdminTwoFactorAuthenticatorPortal::obj()->ValidateCurrentAdminCode()){
            Json::Success(line: $this->class_name . __LINE__);
        }

        Json::Incorrect('code', 'code not valide ', $this->class_name . __LINE__);
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
        if(!empty($_ENV['IS_TELEGRAM_ACTIVATE']) && !empty($admin['telegram_status'])) {
            AlertAdminTelegramBot::obj()->alertMessageOfAgent(
                $admin[$this->identify_table_id_col_name],
                (int) $admin['telegram_chat_id'],
                'You Have Success Register of Two-Factor-Authenticator'
            );
        }
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

    public function stgClearMyAuth(): void
    {
        $admin_id = AdminLoginToken::obj()->GetAdminID();
        if(empty($admin_id)){
            Json::ReLogin($this->class_name . __LINE__);
        }else{
            $this->Edit(['auth' => ''], " `$this->identify_table_id_col_name` = ?", [$admin_id]);
            Json::Success(line: $this->class_name . __LINE__);
        }
    }
}