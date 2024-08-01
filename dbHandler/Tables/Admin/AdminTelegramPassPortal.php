<?php
/**
 * @PHP       Version >= 8.0
 * @Liberary  AdminPortalHandler
 * @Project   AdminPortalHandler
 * @copyright ©2024 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-07-31 3:52 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/AdminPortalHandler  view project on GitHub
 * @Maatify   AdminPortalHandler :: AdminPassViaTelegramPortal
 */

namespace Maatify\Portal\Admin;

use Maatify\Logger\Logger;
use Maatify\Portal\DbHandler\ParentClassHandler;
use Maatify\PostValidatorV2\ValidatorConstantsTypes;
use Maatify\PostValidatorV2\ValidatorConstantsValidators;

class AdminTelegramPassPortal extends ParentClassHandler
{
    public const IDENTIFY_TABLE_ID_COL_NAME = AdminTelegramPass::IDENTIFY_TABLE_ID_COL_NAME;
    public const TABLE_NAME                 = AdminTelegramPass::TABLE_NAME;
    public const TABLE_ALIAS                = AdminTelegramPass::TABLE_ALIAS;
    public const LOGGER_TYPE                = AdminTelegramPass::LOGGER_TYPE;
    public const LOGGER_SUB_TYPE            = AdminTelegramPass::LOGGER_SUB_TYPE;
    public const COLS                       = AdminTelegramPass::COLS;
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

    protected array $cols_to_filter = [
        [self::IDENTIFY_TABLE_ID_COL_NAME, ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
        ['chat_id', ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
    ];

    // to use in add if child classes no have language_id
    protected array $child_classes = [];

    // to use in add if child classes have language_id
    protected array $child_classe_languages = [];
    private static self $instance;
    /**
     * @var true
     */
    private bool $is_active_telegram = false;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        parent::__construct();
        if (! empty($_ENV['TELEGRAM_ADMIN_USERNAME'])) {
            $this->is_active_telegram = true;
        }
    }

    public function clearAdminPendingLogin(int $admin_id, int $chat_id, bool $is_auth_by_2fa = false): void
    {
        if ($this->is_active_telegram && ! empty($chat_id)) {
            $list = $this->pendingListAdminChat($admin_id, $chat_id);
            if (! empty($list)) {
                foreach ($list as $item) {
                    $this->clearAuthMessage($admin_id, $chat_id, $item[self::IDENTIFY_TABLE_ID_COL_NAME], $is_auth_by_2fa);
                }
            }
        }
    }

    private function pendingListAdminChat(int $admin_id, int $chat_id): array
    {
        return $this->RowsThisTable("`$this->identify_table_id_col_name`",
            "`" . Admin::IDENTIFY_TABLE_ID_COL_NAME . "` = ? AND `chat_id` = ? AND `is_pending` = ?",
            [$admin_id, $chat_id, 1]);
    }

    private function clearAuthMessage(int $admin_id, int $chat_id, int $message_id, bool $is_auth_by_2fa = false): void
    {
        TelegramBotWebHookAdminController::obj()->clearAuthKeyboard($chat_id, $message_id, $is_auth_by_2fa);
        $this->Edit([
            'is_pending' => 0,
        ],
            "`" . Admin::IDENTIFY_TABLE_ID_COL_NAME . "` = ? AND `$this->identify_table_id_col_name` = ? AND `chat_id` = ? ",
            [$admin_id, $message_id, $chat_id]);
    }


    public function sendNewAuthorization(int $admin_id, string $token): void
    {
        $admin = AdminTelegramBotPortal::obj()->infoByAdmin($admin_id);
        if (! empty($admin)) {
            if (! empty($admin['admin_id']) && ! empty($admin['chat_id'])) {
                $this->clearAdminPendingLogin($admin_id, $admin['chat_id']);
                $message = TelegramBotWebHookAdminController::obj()->sendAuthorization($admin['first_name'], $admin['chat_id']);
                if (! empty($message['ok'])) {
                    if (! empty($message['result']['message_id'])) {
                        $this->Add([
                            self::IDENTIFY_TABLE_ID_COL_NAME  => $message['result']['message_id'],
                            Admin::IDENTIFY_TABLE_ID_COL_NAME => $admin_id,
                            'chat_id'                         => $admin['chat_id'],
                            'is_pending'                      => 1,
                            'token'                           => $token,
                        ]);
                    }
                }
            }
        }
    }

    /*
    public function fixNotDefinedAdmin(): void
    {
        $a_table_name = Admin::TABLE_NAME;
        $all = $this->Rows(
            "`$a_table_name` 
            LEFT JOIN `$this->tableName` ON `$this->tableName`.`$this->identify_table_id_col_name` = `$a_table_name`.`$this->identify_table_id_col_name` ",
            "`$a_table_name`.`$this->identify_table_id_col_name`",
            "`$this->tableName`.`$this->identify_table_id_col_name` IS NULL"
        );
        if(!empty($all)){
            foreach($all as $row){
                $this->Add([
                    self::IDENTIFY_TABLE_ID_COL_NAME => $row[self::IDENTIFY_TABLE_ID_COL_NAME],
                ]);
            }
        }
        Json::Success();
    }
    */
    public function validateAdminPassViaTelegramToken(int $admin_id, string $token): bool
    {
        if (! empty($token)) {
            $admin_col_name = Admin::IDENTIFY_TABLE_ID_COL_NAME;
            if ($this->RowIsExistThisTable("`$admin_col_name` = ? AND `token` = ? AND `is_pending` = ? AND `is_passed` = ?",
                [$admin_id,
                 $token,
                 0,
                 1])) {
                return true;
            }
        }

        return false;
    }

    public function sendSessionStartFrom2fs(int $admin_id, string $first_name, int $telegram_chat_id, bool $is_auth_by_2fa = false): void
    {
        AdminTelegramPassPortal::obj()->clearAdminPendingLogin($admin_id, $telegram_chat_id, true);
        if (! empty($first_name) && ! empty($telegram_chat_id)) {
            AdminTelegramMessageSender::obj()->sendAdminSessionStartByNewSession($first_name, $telegram_chat_id);
        }
    }

    public function allowByTelegram(int $chat_id, int $message_id): void
    {
        $this->Edit([
            'is_pending' => 0,
            'is_passed' => 1,
        ], "`chat_id` = ? AND `message_id` = ? AND `is_pending` = ?", [$chat_id, $message_id, 1]);
    }

    public function disallowByTelegram(int $chat_id, int $message_id): void
    {
        $this->Edit([
            'is_pending' => 0,
            'is_passed' => 0,
        ], "`chat_id` = ? AND `message_id` = ? AND `is_pending` = ?", [$chat_id, $message_id, 1]);
    }

    public function terminateSession(int $chat_id, int $message_id): void
    {
        $admin_id = (int)$this->ColThisTable('admin_id',
            " `chat_id` = ? AND `message_id` = ? ORDER BY $this->identify_table_id_col_name DESC LIMIT 1", [$chat_id, $message_id]);
        $this->Edit(
            [
                'is_pending' => 0,
            ],
            "`chat_id` = ? AND `admin_id` = ? AND `message_id` = ? AND `is_pending` = ?",
            [$chat_id, $admin_id, $message_id, 1]
        );
        AdminLoginToken::obj()->terminateSessionUsingTelegram($admin_id, $chat_id);
    }
}