<?php
/**
 * @PHP       Version >= 8.0
 * @Liberary  AdminPortalHandler
 * @Project   AdminPortalHandler
 * @copyright ©2024 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-07-23 11:56 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/AdminPortalHandler  view project on GitHub
 * @Maatify   AdminPortalHandler :: AdminTelegramBotPortal
 */

namespace Maatify\Portal\Admin;

use App\Assist\AppFunctions;
use App\Assist\Encryptions\EnvEncryption;
use Maatify\Functions\GeneralFunctions;
use Maatify\Json\Json;
use Maatify\Logger\Logger;
use Maatify\Portal\DbHandler\ParentClassHandler;
use Maatify\PostValidatorV2\ValidatorConstantsTypes;
use Maatify\PostValidatorV2\ValidatorConstantsValidators;
use Maatify\TelegramBot\TelegramBotManager;

class AdminTelegramBotPortal extends ParentClassHandler
{
    public const IDENTIFY_TABLE_ID_COL_NAME = AdminTelegramBot::IDENTIFY_TABLE_ID_COL_NAME;
    public const TABLE_NAME                 = AdminTelegramBot::TABLE_NAME;
    public const TABLE_ALIAS                = AdminTelegramBot::TABLE_ALIAS;
    public const LOGGER_TYPE                = AdminTelegramBot::LOGGER_TYPE;
    public const LOGGER_SUB_TYPE            = AdminTelegramBot::LOGGER_SUB_TYPE;
    public const COLS                       = AdminTelegramBot::COLS;
    public const IMAGE_FOLDER               = self::TABLE_NAME;

    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $tableName = self::TABLE_NAME;
    protected string $tableAlias = self::TABLE_ALIAS;
    protected string $logger_type = self::LOGGER_TYPE;
    protected string $logger_sub_type = self::LOGGER_SUB_TYPE;
    protected array $cols = self::COLS;
    protected string $image_folder = self::IMAGE_FOLDER;

    // to use in list of AllPaginationThisTableFilter()
    protected array $inner_language_tables = [];

    // to use in list of source and destination rows with names
    protected string $inner_language_name_class = '';

    protected array $cols_to_add = [
//        ['chat_id', ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Require],
//        [ValidatorConstantsTypes::Username, ValidatorConstantsTypes::Username, ValidatorConstantsValidators::Require],
//        ['first_name', ValidatorConstantsTypes::Name, ValidatorConstantsValidators::Require],
//        ['last_name', ValidatorConstantsTypes::Name, ValidatorConstantsValidators::Require],
//        [ValidatorConstantsTypes::Phone, ValidatorConstantsTypes::Phone, ValidatorConstantsValidators::Require],
//        [ValidatorConstantsTypes::Status, ValidatorConstantsTypes::Status, ValidatorConstantsValidators::Require],
//        [ValidatorConstantsTypes::DateTime, ValidatorConstantsTypes::DateTime, ValidatorConstantsValidators::Require],
    ];

    protected array $cols_to_edit = [
        [ValidatorConstantsTypes::Username, ValidatorConstantsTypes::Username, ValidatorConstantsValidators::Optional],
        ['first_name', ValidatorConstantsTypes::Name, ValidatorConstantsValidators::Optional],
        ['last_name', ValidatorConstantsTypes::Name, ValidatorConstantsValidators::Optional],
        [ValidatorConstantsTypes::Status, ValidatorConstantsTypes::Bool, ValidatorConstantsValidators::Optional],
    ];

    protected array $cols_to_filter = [
        [self::IDENTIFY_TABLE_ID_COL_NAME, ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
        ['chat_id', ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
        [ValidatorConstantsTypes::Username, ValidatorConstantsTypes::Username, ValidatorConstantsValidators::Optional],
        [ValidatorConstantsTypes::Status, ValidatorConstantsTypes::Status, ValidatorConstantsValidators::Optional],
    ];

    // to use in add if child classes no have language_id
    protected array $child_classes = [];

    // to use in add if child classes have language_id
    protected array $child_classe_languages = [];
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function recordChatByPostedAdmin(): void
    {
        $admin_id = $this->postValidator->Require('user_id', ValidatorConstantsTypes::Int, $this->class_name . __LINE__);
        $chat_id = $this->postValidator->Require('chat_id', ValidatorConstantsTypes::Int, $this->class_name . __LINE__);
        $first_name = $this->postValidator->Optional('first_name', ValidatorConstantsTypes::Name, $this->class_name . __LINE__);
        $last_name = $this->postValidator->Optional('last_name', ValidatorConstantsTypes::Name, $this->class_name . __LINE__);
        $username = $this->postValidator->Optional('username', ValidatorConstantsTypes::Username, $this->class_name . __LINE__);
        $photo_url = $this->postValidator->Optional('photo_url', ValidatorConstantsTypes::String, $this->class_name . __LINE__);
        $auth_date = $this->postValidator->Optional('auth_date', ValidatorConstantsTypes::Int, $this->class_name . __LINE__);
        $this->handleUpdate($admin_id, $chat_id, $first_name, $last_name, $username, $photo_url, $auth_date, 1);
        Json::Success(line: $this->class_name . __LINE__);
    }

    public function SwitchMyStatus(): void
    {
        $_POST[self::IDENTIFY_TABLE_ID_COL_NAME] = AdminLoginToken::obj()->GetAdminID();
        parent::SwitchStatus();
    }

    public function registerByAuth(array $auth_data): bool
    {
        $admin_id = AdminLoginToken::obj()->GetAdminID();
        if (! empty($auth_data['id'])) {
            $chat_id = $auth_data['id'];
            $first_name = $auth_data['first_name'] ?? '';
            $last_name = $auth_data['last_name'] ?? '';
            $username = $auth_data['username'] ?? '';
            $photo_url = $auth_data['photo_url'] ?? '';
            $auth_date = $auth_data['auth_date'] ?? '';
            $this->handleUpdate($admin_id, $chat_id, $first_name, $last_name, $username, $photo_url, $auth_date);
            return true;
        }

        return false;
    }

    public function getAdminByChatId(int $chatId): int
    {
        return (int)$this->ColThisTable("`$this->identify_table_id_col_name`", '`chat_id` = ? ', [$chatId]);
    }

    public function checkChatIdUsedByAnotherAdmin(int $chatId, int $adminId): bool
    {
        return $this->RowIsExistThisTable(" `chat_id` = ? AND `$this->identify_table_id_col_name` <> ? ", [$chatId, $adminId]);
    }

    public function activateByChatID(int $chatId): bool
    {
        if($admin = $this->rowByChatId($chatId)) {
            if(!$admin['status']) {
                $this->Edit(
                    ['status' => 1], '`chat_id` = ? ', [$chatId]
                );
                $this->row_id = $admin[$this->identify_table_id_col_name];
                $changes[] = ['status', GeneralFunctions::Bool2String(0), GeneralFunctions::Bool2String(1),];
                $this->logger_keys = [$this->identify_table_id_col_name => $this->row_id];
                $log = $this->logger_keys;
                $log['change'] = 'Activate Telegram Chat Status By Telegram bot chat';
                $this->Logger($log, $changes, 'Update Telegram Chat Information');
                return true;
            }
        }else{
            AlertAdminTelegramBot::obj()->alertMessageOfAgent(
                $admin[$this->identify_table_id_col_name],
                $chatId,
                'Your notification already enabled'
            );
        }
        return false;
    }

    public function deactivateByChatID(int $chatId): bool
    {
        if($admin = $this->rowByChatId($chatId)) {
            if($admin['status']) {
                $this->Edit(
                    ['status' => 0], '`chat_id` = ? ', [$chatId]
                );
                $this->row_id = $admin[$this->identify_table_id_col_name];
                $changes[] = ['status', GeneralFunctions::Bool2String(1), GeneralFunctions::Bool2String(0),];
                $this->logger_keys = [$this->identify_table_id_col_name => $this->row_id];
                $log = $this->logger_keys;
                $log['change'] = 'De-Activate Telegram Chat Status By Telegram bot chat';
                $this->Logger($log, $changes, 'Update Telegram Chat Information');
                return true;
            }
        }else{
            AlertAdminTelegramBot::obj()->alertMessageOfAgent(
                $admin[$this->identify_table_id_col_name],
                $chatId,
                'Your notification already disabled'
            );
        }
        return false;
    }

    private function handleUpdate(int $admin_id, string $chat_id, string $first_name, string $last_name, string $username, string $photo_url, string $auth_date, int $status = 0): void
    {
        $this->current_row = $this->RowThisTable('*', " `$this->identify_table_id_col_name` = ? ", [$admin_id]);
        if (! empty($this->current_row)) {
            $array_to_update = array();
            $array_to_update['chat_id'] = $chat_id;
            if (! empty($first_name)) {
                $array_to_update['first_name'] = $first_name;
            }
            if (! empty($last_name)) {
                $array_to_update['last_name'] = $last_name;
            }
            if (! empty($username)) {
                $array_to_update['username'] = $username;
            }
            if (! empty($photo_url)) {
                $array_to_update['photo_url'] = $photo_url;
            }
            if (! empty($auth_date)) {
                $array_to_update['auth_date'] = $auth_date;
            }
            $array_to_update['status'] = $status;
            $array_to_update['time'] = AppFunctions::CurrentDateTime();
            $this->editInfo($admin_id, $array_to_update);
        }
    }

    private function editInfo(int $admin_id, array $array_to_update): void
    {
        $this->Edit($array_to_update, " `$this->identify_table_id_col_name` = ? ", [$admin_id]);
        $this->row_id = $admin_id;
        $this->logger_keys = [$this->identify_table_id_col_name => $this->row_id];
        $log = $this->logger_keys;
        $log['change'] = 'Update Telegram Chat Information';
        $changes = array();
        foreach ($array_to_update as $key => $value) {
            if ($key === 'status') {
                $changes[] = [$key, GeneralFunctions::Bool2String($this->current_row[$key]), GeneralFunctions::Bool2String($value)];
            } else {
                $changes[] = [$key, $this->current_row[$key], $value];
            }
        }
        $this->Logger($log, $changes, 'Update Telegram Chat Information');
    }

    public function sendAdminTelegramMessage(int $admin_id, string $message): void
    {
        if(!empty($_ENV['IS_TELEGRAM_ADMIN_ACTIVATE']) && $_ENV['TELEGRAM_API_KEY_ADMIN'])
        {
            $api_key = (new EnvEncryption())->DeHashed($_ENV['TELEGRAM_API_KEY_ADMIN']);
            $chats = $this->adminActiveChat($admin_id);
            if (! empty($chats)) {
                try {
                    $sender = TelegramBotManager::obj($api_key)
                        ->Sender();
                    foreach ($chats as $chat) {
                        $sender->SendMessage($chat['chat_id'], $message);
                    }
                }
                catch (\Exception $exception) {
                    Logger::RecordLog($exception, 'admin_telegram_message');
                }
            }
        }
    }

    private function adminActiveChat(int $admin_id): array
    {
        return $this->RowThisTable('chat_id', "`$this->identify_table_id_col_name` = ? AND `status` = ? ", [$admin_id, 1]);
    }

    public function rowByChatId(int $chatId): array
    {
        return $this->RowThisTable('*', "`chat_id` = ?", [$chatId]);

    }

    public function infoByAdmin(int $admin_id): array
    {
        return $this->RowThisTable('*', "`$this->identify_table_id_col_name` = ?", [$admin_id]);
    }
}