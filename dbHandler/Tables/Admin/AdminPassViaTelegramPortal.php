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

use Maatify\Portal\DbHandler\ParentClassHandler;
use Maatify\PostValidatorV2\ValidatorConstantsTypes;
use Maatify\PostValidatorV2\ValidatorConstantsValidators;

class AdminPassViaTelegramPortal extends ParentClassHandler
{
    public const IDENTIFY_TABLE_ID_COL_NAME = AdminPassViaTelegram::IDENTIFY_TABLE_ID_COL_NAME;
    public const TABLE_NAME                 = AdminPassViaTelegram::TABLE_NAME;
    public const TABLE_ALIAS                = AdminPassViaTelegram::TABLE_ALIAS;
    public const LOGGER_TYPE                = AdminPassViaTelegram::LOGGER_TYPE;
    public const LOGGER_SUB_TYPE            = AdminPassViaTelegram::LOGGER_SUB_TYPE;
    public const COLS                       = AdminPassViaTelegram::COLS;
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

    public function clearAdminPendingLogin(int $admin_id, int $chat_id): void
    {
        if ($this->is_active_telegram && !empty($chat_id)) {
            $list = $this->pendingListAdminChat($admin_id, $chat_id);
            if (! empty($list)) {
                foreach ($list as $item) {
                    $this->clearAuthMessage($admin_id, $chat_id, $item[self::IDENTIFY_TABLE_ID_COL_NAME]);
                }
            }
        }
    }

    private function pendingListAdminChat(int $admin_id, int $chat_id): array
    {
        return $this->RowsThisTable("`$this->identify_table_id_col_name`",
            "`" . Admin::IDENTIFY_TABLE_ID_COL_NAME . "` = ? AND `chat_id` = ? ",
            [$admin_id, $chat_id]);
    }

    private function clearAuthMessage(int $admin_id, int $chat_id, int $message_id): void
    {
        TelegramBotWebHookAdminController::obj()->clearAuthKeyboard($chat_id, $message_id);
        $this->Edit([
            'is_pending' => 0,
        ],
            "`" . Admin::IDENTIFY_TABLE_ID_COL_NAME . "` = ? AND `$this->identify_table_id_col_name` = ? AND `chat_id` = ? ",
            [$admin_id, $message_id, $chat_id]);
    }



    public function sendNewAuthorization(int $admin_id, string $token): void
    {
        $admin = AdminTelegramBotPortal::obj()->infoByAdmin($admin_id);
        if (!empty($admin)) {
            if(!empty($admin['admin_id']) && !empty($admin['chat_id'])) {
                $this->clearAdminPendingLogin($admin_id, $admin['chat_id']);
                $message = TelegramBotWebHookAdminController::obj()->sendAuthorization($admin['first_name'], $admin['chat_id']);
                if(!empty($message['ok'])){
                    if(!empty($message['result']['message_id'])){
                        $this->Add([
                            self::IDENTIFY_TABLE_ID_COL_NAME => $message['result']['message_id'],
                            Admin::IDENTIFY_TABLE_ID_COL_NAME => $admin_id,
                            'chat_id' => $admin['chat_id'],
                            'is_pending' => 1,
                            'token' => $token,
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
}