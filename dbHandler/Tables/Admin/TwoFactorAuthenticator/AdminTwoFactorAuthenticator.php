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
 * @Maatify   AdminPortalHandler :: AdminTwoFactorAuthenticator
 */

namespace Maatify\Portal\Admin\TwoFactorAuthenticator;

use App\Assist\Encryptions\AdminAuthEncryption;
use App\Assist\Jwt\JWTAssistance;
use Exception;
use Maatify\GoogleAuth\GoogleAuth;
use Maatify\Json\Json;
use Maatify\Logger\Logger;
use Maatify\Portal\Admin\Admin;
use Maatify\Portal\Admin\AdminFailedLogin;
use Maatify\Portal\Admin\AdminLoginToken;
use Maatify\Portal\DbHandler\ParentClassHandler;

class AdminTwoFactorAuthenticator extends ParentClassHandler
{
    const TABLE_NAME = 'a_2fa';
    protected string $tableName = self::TABLE_NAME;
    const IDENTIFY_TABLE_ID_COL_NAME = Admin::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $logger_type = Admin::LOGGER_TYPE;
    protected string $logger_sub_type = 'TwoFactorAuthenticator';
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function ShowAuthCode(array $admin): void
    {
        Json::Success([$this->AuthDecode($admin['auth'])]);
    }

    public function ResponseAuthMov(array $admin): void
    {
        if (! empty($admin['auth'])) {
            JWTAssistance::obj()->TokenAuth($admin[$this->identify_table_id_col_name],
                $admin['username'],
                ['next' => 'Auth']);
            Json::GoToMethod('Auth',
                'Please Confirm Your Google Authenticator',
                line: $this->class_name . __FUNCTION__ . '::' . __LINE__);
        } else {
            try {
                $g_2fa_code = GoogleAuth::obj()->GenerateSecret();
                JWTAssistance::obj()->TokenAuth($admin[$this->identify_table_id_col_name],
                    $admin['username'],
                    ['secret' => $g_2fa_code, 'next' => 'AuthRegister']);
                Json::GoToMethod('AuthRegister',
                    'Please Set Your Google Authenticator',
                    [
                        'g_auth_code'   => $g_2fa_code,
                        'g_auth_base64' => base64_encode(file_get_contents(GoogleAuth::obj()
                            ->GetImg(trim($admin['username']),
                                trim($g_2fa_code),
                                trim($_ENV['SITE_PORTAL_NAME'])))),
                    ],
                    $this->class_name . __FUNCTION__ . '::' . __LINE__);
            } catch (Exception $exception) {
                Logger::RecordLog($exception, 'auth_generator');
                Json::TryAgain($this->class_name . __FUNCTION__ . '::' . __LINE__);
            }
        }
    }

    public function ValidateAdminCode(int $admin_id): bool
    {
        $code = $this->postValidator->Require('code', 'code');
        $tb_admin = Admin::obj()->TableName();
        $admin = $this->Row("`$this->tableName` INNER JOIN `$tb_admin` ON `$tb_admin`.`$this->identify_table_id_col_name` = `$this->tableName`.`$this->identify_table_id_col_name` ",
            "`$tb_admin`.`username`, `$this->tableName`.`auth`",
            "`$this->tableName`.`$this->identify_table_id_col_name` = ? ",
            [$admin_id]);
        if (empty($admin['auth'])) {
            Json::NotAllowedToUse('code', 'account not allowed to use 2fa authentication');
        }

        return $this->ValidateCode($code, $this->AuthDecode($admin['auth']), $admin['username']);
    }

    protected function ValideToken(): array
    {
        $auth_pages = ['AuthRegister', 'Auth', 'AuthPassedViaTelegram'];
        if (! empty($_GET['action']) && in_array($_GET['action'], $auth_pages)) {
            if (! empty($_SESSION['token']) && $tokens = JWTAssistance::obj()->JwtValidation($this->class_name . __FUNCTION__ . '::' . __LINE__)) {
                if (isset($tokens->token, $tokens->next)) {
                    if (in_array($tokens->next, $auth_pages)) {
                        if ($admin = AdminLoginToken::obj()->ByToken($tokens->token, $this->class_name . __FUNCTION__ . '::' . __LINE__)) {
                            if (! empty($tokens->secret)) {
                                $admin['secret'] = $tokens->secret;
                            }
                            return $admin;
                        }
                    }
                }
            }
        }
        AdminFailedLogin::obj()->Failed('');
        Json::ReLogin($this->class_name . __FUNCTION__ . '::' . __LINE__);

        return [];
    }

    protected function ValidateCode(string $code, string $auth_code, string $username): bool
    {
        if (GoogleAuth::obj()->checkCode($auth_code, $code)) {
            AdminFailedLogin::obj()->Success($username);

            return true;
        } else {
            AdminFailedLogin::obj()->Failed($username);
            Json::Incorrect('code', line: $this->class_name . __FUNCTION__ . '::' . __LINE__);

            return false;
        }
    }

    public function RemoveAuthCode(): bool
    {
        return $this->Edit(['auth' => ''], "`$this->identify_table_id_col_name` = ? ", [$this->row_id]);
    }

    /**
     * ========================================= GoogleAuthenticator =========================================
     **/
    protected function AuthEncode($auth): string
    {
        $auth = base64_encode($auth);

        return (new AdminAuthEncryption())->Hash($auth);
    }

    protected function AuthDecode($auth): string
    {
        $auth = (new AdminAuthEncryption())->DeHashed($auth);

        return (string)base64_decode($auth);
    }
}