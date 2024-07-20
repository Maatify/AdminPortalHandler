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
use \App\Assist\Encryptions\AdminTokenEncryption;
use App\Assist\Jwt\JWTAssistance;
use Maatify\Functions\GeneralFunctions;
use Maatify\Json\Json;
use Maatify\Portal\Language\LanguagePortal;

class AdminLoginToken extends AdminToken
{
    private static self $instance;
    private string $admin_name = '';
    private string $admin_username = '';
    private string $admin_email;
    private int $admin_isAdmin;

    public static function obj(): self
    {
        if(empty(self::$instance))
        {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function LogoutSilent(): void
    {
        if (! empty($_SESSION['token']) && $token = JWTAssistance::obj()->JwtValidation($this->class_name . __LINE__)) {
            if (! empty($token->token)) {
                if ($admin = $this->ByToken($token->token, $this->class_name . __LINE__)) {
                    $this->Edit(['token'=>''], "`$this->identify_table_id_col_name` = ? ", [$admin[$this->identify_table_id_col_name]]);
                    $this->row_id = $admin[$this->identify_table_id_col_name];
                    $this->logger_type = Admin::LOGGER_TYPE;
                    $this->logger_sub_type = 'Logout';
                    $log = [$this->identify_table_id_col_name=>$admin[$this->identify_table_id_col_name]];
                    $this->logger_keys = $log;
                    $log['details'] = 'Success Logout';
                    $this->AdminLogger($log, [], 'Logout');
                }
            }
        }
        session_destroy();
    }

    public function UserLogout(): void
    {
        $this->LogoutSilent();
        Json::Success(line: $this->class_name . __LINE__);
    }

    public function ValidateAdminToken(): array
    {
        if(!empty($_GET['action']) && !in_array($_GET['action'], ['login', 'logout'])) {
            if (! empty($_SESSION['token']) && $token = JWTAssistance::obj()->JwtValidation($this->class_name . $this->class_name . __LINE__)) {
                if (! empty($token->token)) {
                    if ($admin = $this->ByToken($token->token, $this->class_name . __LINE__)) {
                        JWTAssistance::obj()->JwtTokenHash($admin[$this->identify_table_id_col_name], $admin['username']);
                        $this->row_id = $admin[$this->identify_table_id_col_name];
                        $this->admin_isAdmin = (int)$admin['is_admin'];
                        $this->admin_name = $admin['name'];
                        $this->admin_username = $admin['username'];
                        $this->admin_email = $admin['email'];

                        return $admin;
                    }
                }
            }
            AdminFailedLogin::obj()->Failed('');
            Json::ReLogin($this->class_name .  __FUNCTION__ . '::' .__LINE__);
        }
        return [];
    }

    public function ValidateSilentAdminTokenPage(): bool
    {
        if (! empty($_SESSION['token']) && $tokens = JWTAssistance::obj()->JwtValidationForSessionLogin($this->class_name . __LINE__)) {
            if (isset($tokens->token)) {
                if (AdminLoginToken::obj()->ByToken($tokens->token, $this->class_name . __LINE__)) {
                    return true;
                }
            }
        }
        die("<script>window.location = '" . AppFunctions::PortalUrl() . "login" . "';</script>");
    }

    public function ValidateSilentAdminToken(): void
    {
        $auth_pages = ['AuthRegister', 'Auth', 'ChangePassword', 'EmailConfirm', 'CheckSession'];
        if(!empty($_GET['action'])) {
            if (! empty($_SESSION['token']) && $tokens = JWTAssistance::obj()->JwtValidationForSessionLogin($this->class_name . __LINE__)) {
                if (isset($tokens->token)) {
                    if (AdminLoginToken::obj()->ByToken($tokens->token, $this->class_name . __LINE__)) {
                        $type['type'] = (isset($tokens->next) && in_array($tokens->next, $auth_pages) ? 'login' : 'main');
                        Json::Success($type);
                    }
                }
            }
        }
        Json::ReLogin($this->class_name . __LINE__);
    }

    public function ByToken(string $hashed_token, string $line): array
    {
        $tb_admin = Admin::TABLE_NAME;
        $tb_admin_email = AdminEmail::TABLE_NAME;
        $tb_admin_auth = Admin2FA::TABLE_NAME;
        $admin = $this->Row("`$this->tableName` 
        INNER JOIN `$tb_admin` ON `$tb_admin`.`$this->identify_table_id_col_name` = `$this->tableName`.`$this->identify_table_id_col_name` 
        INNER JOIN `$tb_admin_email` ON `$tb_admin_email`.`$this->identify_table_id_col_name` = `$this->tableName`.`$this->identify_table_id_col_name` 
        INNER JOIN `$tb_admin_auth` ON `$tb_admin_auth`.`$this->identify_table_id_col_name` = `$this->tableName`.`$this->identify_table_id_col_name` 
        ",
            "`$tb_admin`.*, `$tb_admin_email`.`email`, `$tb_admin_email`.`confirmed`, `$tb_admin_auth`.`auth`, `$tb_admin_auth`.`isAuthRequired`",
            "`$this->tableName`.`token` = ? AND `$this->tableName`.`token` <> ''",
            [self::TokenSecretKeyEncode($hashed_token)]);
        if($admin){
            if(empty($admin['status'])){
                Json::SuspendedAccount($line);
            }
            $this->row_id = $admin[$this->identify_table_id_col_name];
            $this->admin_isAdmin = (int)$admin['is_admin'];
            $this->admin_name = $admin['name'];
            $this->admin_username = $admin['username'];
            $this->admin_email = $admin['email'];
        }
        return $admin;
    }
    public function GenerateToken(int $admin_id, string $username): string
    {
        $token = time() . $admin_id . md5(time() . $username) . "_" . $this->MD5IP() . "_" . $this->MD5AgentUser();
        $this->SetToken($admin_id, $token);
        return $token;
    }

    private function MD5AgentUser(): string
    {
        return md5(GeneralFunctions::UserAgent());
    }

    private function MD5IP(): string
    {
        return md5(AppFunctions::IP());
    }


    private static function TokenSecretKeyEncode($code): string
    {
        $code = base64_encode($code);
        return (new AdminTokenEncryption())->Hash($code);
    }
    private static function TokenSecretKeyDecode($code): string
    {
        $code = (new AdminTokenEncryption())->DeHashed($code);
        return (string)base64_decode($code);
    }

    private function SetToken(int $admin_id, string $token): void
    {
        $this->Edit(['token' => $this->TokenSecretKeyEncode($token)],
            "`$this->identify_table_id_col_name` = ? ",
            [$admin_id]);
    }

    public function HandleAdminResponse(array $admin): array
    {
        $this->row_id = $admin[$this->identify_table_id_col_name];
        if(isset($admin['username'])) unset($admin['username']);
        if(isset($admin['status'])) unset($admin['status']);
        if(isset($admin['lang'])) unset($admin['lang']);
        if(isset($admin['confirmed'])) $admin['confirmed'] = (bool) $admin['confirmed'];
        if(isset($admin['isAuthRequired'])) unset($admin['isAuthRequired']);
        if(isset($admin['auth'])) unset($admin['auth']);
        $admin['languages'] = LanguagePortal::obj()->IdNameCode();
        return $admin;
    }

    public function GetAdminID(): int
    {
        return $this->row_id;
    }

    public function GetAdminName(): string
    {
        return $this->admin_name;
    }

    public function GetAdminUsername(): string
    {
        return $this->admin_username;
    }

    public function GetAdminEmail(): string
    {
        return $this->admin_email;
    }

    public function GetAdminIsAdmin(): int
    {
        return $this->admin_isAdmin;
    }
}