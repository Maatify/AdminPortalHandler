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

    public function UserInfo(): void
    {
        $this->ValidatePostedTableId();
        $this->logger_sub_type = 'Info';
        $this->logger_keys = [$this->identify_table_id_col_name => $this->row_id];
        $log = $this->logger_keys;

        $this->Logger($log, [], 'ViewUserInfo');
        Json::Success($this->current_row);
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
        ];
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