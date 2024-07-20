<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2024-07-23
 * Time: 11:56 AM
 * https://www.Maatify.dev
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
        [ValidatorConstantsTypes::Phone, ValidatorConstantsTypes::Phone, ValidatorConstantsValidators::Optional],
        [ValidatorConstantsTypes::Status, ValidatorConstantsTypes::Status, ValidatorConstantsValidators::Optional],
    ];

    protected array $cols_to_filter = [
        [self::IDENTIFY_TABLE_ID_COL_NAME, ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
        ['chat_id', ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
        [ValidatorConstantsTypes::Username, ValidatorConstantsTypes::Username, ValidatorConstantsValidators::Optional],
        ['first_name', ValidatorConstantsTypes::Name, ValidatorConstantsValidators::Optional],
        ['last_name', ValidatorConstantsTypes::Name, ValidatorConstantsValidators::Optional],
        [ValidatorConstantsTypes::Phone, ValidatorConstantsTypes::Phone, ValidatorConstantsValidators::Optional],
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

    public function RecordAdminChat(): void
    {
        $admin_id = $this->postValidator->Require('user_id', ValidatorConstantsTypes::Int, $this->class_name . __LINE__);
        $chat_id = $this->postValidator->Require('chat_id', ValidatorConstantsTypes::Int, $this->class_name . __LINE__);
        $first_name = $this->postValidator->Optional('first_name', ValidatorConstantsTypes::Name, $this->class_name . __LINE__);
        $last_name = $this->postValidator->Optional('last_name', ValidatorConstantsTypes::Name, $this->class_name . __LINE__);
        $username = $this->postValidator->Optional('username', ValidatorConstantsTypes::Username, $this->class_name . __LINE__);
        $phone = $this->postValidator->Optional(ValidatorConstantsTypes::Phone, ValidatorConstantsTypes::Phone, $this->class_name . __LINE__);
        $photo_url = $this->postValidator->Optional('photo_url', ValidatorConstantsTypes::String, $this->class_name . __LINE__);
        $auth_date = $this->postValidator->Optional('auth_date', ValidatorConstantsTypes::Int, $this->class_name . __LINE__);
        $array_to_add = [
            self::IDENTIFY_TABLE_ID_COL_NAME => $admin_id,
            'chat_id'                        => $chat_id,
            'first_name'                     => $first_name,
            'last_name'                      => $last_name,
            'username'                       => $username,
            'phone'                          => $phone,
            'photo_url'                      => $photo_url,
            'auth_date'                      => $auth_date,
            'status'                         => 1,
            'time'                           => AppFunctions::CurrentDateTime(),
        ];
        $this->AddInfo($admin_id, $array_to_add);
        Json::Success(line: $this->class_name . __LINE__);
    }

    public function RegisterByAuth(array $auth_data): bool
    {
        $admin_id = AdminLoginToken::obj()->GetAdminID();
        if (! empty($auth_data['id'])) {
            $chat_id = $auth_data['id'];
            $first_name = $auth_data['first_name'] ?? '';
            $last_name = $auth_data['last_name'] ?? '';
            $username = $auth_data['username'] ?? '';
            $phone = $auth_data['phone'] ?? '';
            $photo_url = $auth_data['photo_url'] ?? '';
            $auth_date = $auth_data['auth_date'] ?? '';

            $this->current_row = $this->RowThisTable('*', " `$this->identify_table_id_col_name` = ? AND `chat_id` = ? ", [$admin_id, $chat_id]);
            if (! empty($this->current_row)) {
                $array_to_update = array();
                if (! empty($first_name)) {
                    $array_to_update['first_name'] = $first_name;
                }
                if (! empty($last_name)) {
                    $array_to_update['last_name'] = $last_name;
                }
                if (! empty($username)) {
                    $array_to_update['username'] = $username;
                }
                if (! empty($phone)) {
                    $array_to_update['phone'] = $phone;
                }
                if (! empty($photo_url)) {
                    $array_to_update['photo_url'] = $photo_url;
                }
                if (! empty($auth_date)) {
                    $array_to_update['auth_date'] = $auth_date;
                }
                $array_to_update['status'] = 1;
                $this->editInfo($admin_id, $chat_id, $array_to_update);
            } else {
                $array_to_add = [
                    self::IDENTIFY_TABLE_ID_COL_NAME => $this->row_id,
                    'chat_id'                        => $chat_id,
                    'first_name'                     => $first_name,
                    'last_name'                      => $last_name,
                    'username'                       => $username,
                    'phone'                          => $phone,
                    'photo_url'                      => $photo_url,
                    'auth_date'                      => $auth_date,
                    'status'                         => 1,
                    'time'                           => AppFunctions::CurrentDateTime(),
                ];
                $this->AddInfo($admin_id, $array_to_add);
            }

            return true;
        }

        return false;
    }

    private function AddInfo(int $admin_id, array $array_to_add): void
    {
        $this->Add($array_to_add);
        $this->row_id = $admin_id;
        $this->logger_keys = [$this->identify_table_id_col_name => $this->row_id];
        $log = $this->logger_keys;
        $log['change'] = 'Add Telegram Information';
        $changes = array();
        foreach ($array_to_add as $key => $value) {
            if ($key === 'status') {
                $changes[] = [$key, '', GeneralFunctions::Bool2String($value)];
            } else {
                $changes[] = [$key, '', $value];
            }
        }
        $this->Logger($log, $changes, $_GET['action']);
    }

    private function editInfo(int $admin_id, int $chat_id, array $array_to_update): void
    {
        $this->Edit($array_to_update, " `$this->identify_table_id_col_name` = ? AND `chat_id` = ? ", [$admin_id, $chat_id]);
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

    public function SendAdminTelegramMessage(int $admin_id, string $message): void
    {
        if(!empty($_ENV['IS_TELEGRAM_ADMIN_ACTIVATE']) && $_ENV['TELEGRAM_API_KEY_ADMIN'])
        {
            $api_key = (new EnvEncryption())->DeHashed($_ENV['TELEGRAM_API_KEY_ADMIN']);
            $chats = $this->AdminActiveChatList($admin_id);
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

    private function AdminActiveChatList(int $admin_id): array
    {
        return $this->RowsThisTable('chat_id', "`$this->identify_table_id_col_name` = ? AND `status` = ? ", [$admin_id, 1]);
    }
}