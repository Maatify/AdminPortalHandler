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
 * @Maatify   AdminPortalHandler :: AdminPortalUpdates
 */

namespace Maatify\Portal\Admin;

use App\DB\Tables\PortalCacheRedis;
use Maatify\Json\Json;
use Maatify\LanguagePortalHandler\Language\DbLanguage;
use Maatify\LanguagePortalHandler\Language\LanguagePortal;
use Maatify\Portal\Admin\Email\AdminEmail;
use Maatify\Portal\Admin\Phone\AdminPhone;
use Maatify\Portal\Admin\TelegramBot\AdminTelegramBot;
use Maatify\Portal\Admin\TwoFactorAuthenticator\AdminTwoFactorAuthenticator;
use Maatify\PostValidatorV2\ValidatorConstantsTypes;
use Maatify\PostValidatorV2\ValidatorConstantsValidators;

class AdminPortalUpdates extends AdminPortal
{
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

/*    public function UserInfo(): void
    {
        $this->ValidatePostedTableId();
        $this->logger_sub_type = 'Info';
        $this->logger_keys = [$this->identify_table_id_col_name => $this->row_id];
        $log = $this->logger_keys;

        $this->Logger($log, [], 'ViewUserInfo');
        Json::Success($this->current_row);
    }*/

    public function UserInfo(): void
    {
        $this->ValidatePostedTableId();

        $tb_admin_emails = AdminEmail::TABLE_NAME;
        $tb_admin_auth = AdminTwoFactorAuthenticator::TABLE_NAME;
        [$p_t, $p_c] = AdminPhone::obj()->InnerJoinThisTableWithUniqueCols($this->tableName, ['phone' => 0]);
        [$t_t, $t_c] = AdminTelegramBot::obj()->LeftJoinThisTableWithTableAlias($this->tableName);
        [$language_t, $language_c] = DbLanguage::obj()->InnerJoinThisTableWithUniqueColsWithTableAlias($this->tableName, ['short_name' => 0]);

        $admin = $this->Row(
            "`$this->tableName`
            INNER JOIN `$tb_admin_emails` ON `$tb_admin_emails`.`$this->identify_table_id_col_name` = `$this->tableName`.`$this->identify_table_id_col_name`
            INNER JOIN `$tb_admin_auth` ON `$tb_admin_auth`.`$this->identify_table_id_col_name` = `$this->tableName`.`$this->identify_table_id_col_name`
            $p_t
            $t_t
            $language_t ",
            "`$this->tableName`.*, `$tb_admin_emails`.`email`, `$tb_admin_emails`.`e_confirmed`,
            IF(`$tb_admin_auth`.`auth` = '', 0, 1) as auth,
            `$tb_admin_auth`.`isAuthRequired`, $p_c, $t_c, $language_c",

            "`$this->tableName`.`$this->identify_table_id_col_name` = ? ",
            [$this->row_id]

        );

        $this->logger_sub_type = 'Info';
        $this->logger_keys = [$this->identify_table_id_col_name => $this->row_id];
        $log = $this->logger_keys;

        $this->Logger($log, [], 'ViewUserInfo');
        Json::Success($admin);
    }

    public function Update(): void
    {
        $this->logger_sub_type = 'Info';
        $this->cols_to_edit = [
            [
                ValidatorConstantsTypes::Username,
                ValidatorConstantsTypes::Username,
                ValidatorConstantsValidators::Optional
            ],
            [
                ValidatorConstantsTypes::Name,
                ValidatorConstantsTypes::Name,
                ValidatorConstantsValidators::Optional
            ],
            [
                $this->language_col_name,
                ValidatorConstantsTypes::Int,
                ValidatorConstantsValidators::Optional
            ],
        ];
        $this->ValidatePostedTableId();
        if(!empty($_POST[$this->language_col_name]) && $_POST[$this->language_col_name] != $this->current_row[$this->language_col_name]) {
            LanguagePortal::obj()->ValidatePostedTableId();
        }
        $this->UpdateByPostedIdSilent();
        PortalCacheRedis::obj()->UsersListDelete();
        Json::Success(line: $this->class_name . __LINE__);
    }

    public function SwitchAsAdmin(): void
    {
        $this->ValidatePostedTableId();
        $this->UserForEdit();
        $this->logger_sub_type = 'Info';
        $this->SwitchByKey('is_admin');
    }

    public function SwitchUserStatus(): void
    {
        $this->ValidatePostedTableId();
        $this->UserForEdit();
        $this->logger_sub_type = 'Info';
        $this->SwitchByKey('status');
    }


}