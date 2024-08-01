<?php
/**
 * @PHP       Version >= 8.0
 * @Liberary  AdminPortalHandler
 * @Project   AdminPortalHandler
 * @copyright ©2024 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-08-02 8:05 PM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/AdminPortalHandler  view project on GitHub
 * @Maatify   AdminPortalHandler :: AdminTelegramMessagesHaveKeyboard
 */

namespace Maatify\Portal\Admin;

use App\Assist\AppFunctions;
use App\DB\DBS\DbConnector;
use Maatify\Emoji\EmojiConverter;

class AdminTelegramMessage extends DbConnector
{
    const TABLE_NAME                 = 'a_telegram_message';
    const TABLE_ALIAS                = '';
    const IDENTIFY_TABLE_ID_COL_NAME = 'record_id';
    const LOGGER_TYPE                = Admin::LOGGER_TYPE;
    const LOGGER_SUB_TYPE            = 'telegramMessagesKeyboards';
    const COLS                       =
        [
            self::IDENTIFY_TABLE_ID_COL_NAME => 1,
            'message_id'                     => 1,
            'message'                        => 0,
            'is_auth'                        => 1,
            'is_keyboard'                    => 1,
            'is_deleted'                     => 1,
            'time'                           => 0,
        ];
    private static self $instance;

    protected string $tableName = self::TABLE_NAME;
    protected string $tableAlias = self::TABLE_ALIAS;
    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $logger_type = self::LOGGER_TYPE;
    protected string $logger_sub_type = self::LOGGER_SUB_TYPE;
    protected array $cols = self::COLS;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function recordNewMessage(int $chat_id, int $message_id, string $message, bool $is_keyboard = false, bool $is_auth = false): void
    {
        $this->Add(
            [
                'chat_id'     => $chat_id,
                'message_id'  => $message_id,
                'message'     => EmojiConverter::emojiToCodepoint($message),
                'is_auth'     => (int)$is_auth,
                'is_keyboard' => (int)$is_keyboard,
                'is_deleted'  => 0,
                'time'        => AppFunctions::CurrentDateTime(),
            ]
        );
    }

    public function markMessageDeleted(int $chat_id, int $message_id): void
    {
        $this->Edit(
            ['is_deleted' => 1],
            "`chat_id` = ? AND `message_id` = ? ",
            [$chat_id, $message_id]
        );
    }

    public function markAllChatKeyboardDeleted(int $chat_id): void
    {
        if ($all = $this->RowsThisTable(
            'message_id',
            "`chat_id` = ? AND `is_keyboard` = ? AND `is_deleted` = ?",
            [$chat_id, 1, 0])
        ) {
            foreach ($all as $row) {
                $this->Edit(
                    ['is_deleted' => 1],
                    "`chat_id` = ? AND `message_id` = ? AND `is_keyboard` = ? AND `is_deleted` = ?",
                    [$chat_id, $row['message_id'], 1, 0]
                );
            }
        }
    }

    public function getMessageHaveKeyboard(int $chat_id, int $message_id): string
    {
        $message = $this->ColThisTable('message',
            "`chat_id` = ? AND `message_id` = ? AND `is_keyboard` = ? AND `is_deleted` = ?",
            [$chat_id, $message_id, 1, 0]);
        if (! empty($message)) {
            $message = htmlspecialchars_decode($message);
            $message = EmojiConverter::codepointToEmoji($message);
        }

        return $message;
    }

    protected function notDeletedAuthListOfChatId(int $chat_id): array
    {
        $list = $this->RowsThisTable("*",
            "`chat_id` = ? AND `is_auth` = ? AND `is_keyboard` = ? AND `is_deleted` = ?",
            [$chat_id, 1, 1, 0]);

        return $this->arrayMapMessages($list);
    }

    private function arrayMapMessages(array $messages): array
    {
        if (! empty($messages)) {
            $messages = array_map(function ($row) {
                return $this->messageEmojiDecoder($row);
            }, $messages);
        }

        return $messages;
    }

    private function messageEmojiDecoder(array $message): array
    {
        if (! empty($message['message'])) {
            $message['message'] = htmlspecialchars_decode($message['message']);
            $message['message'] = EmojiConverter::codepointToEmoji($message['message']);
        }

        return $message;
    }
}