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
 * @Maatify   AdminPortalHandler :: AdminTelegramPassPortal
 */

namespace Maatify\Portal\Admin;

use App\Assist\AppFunctions;
use App\Assist\Encryptions\EnvEncryption;
use Exception;
use Maatify\Emoji\EmojiConverter;
use Maatify\Functions\GeneralAgentFunctions;
use Maatify\Logger\Logger;
use Maatify\Portal\DbHandler\ParentClassHandler;
use Maatify\PostValidatorV2\ValidatorConstantsTypes;
use Maatify\PostValidatorV2\ValidatorConstantsValidators;
use Maatify\TelegramBot\TelegramBotManager;

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
    private string $api_key;
    private TelegramBotManager $telegramBotManager;

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
            try {
                $this->is_active_telegram = true;
                $api_key = (new EnvEncryption())->DeHashed($_ENV['TELEGRAM_API_KEY_ADMIN']);
                $this->telegramBotManager = new TelegramBotManager($api_key);
            } catch (Exception $exception) {
                Logger::RecordLog($exception, 'AdminTelegramPassPortal');
                $this->is_active_telegram = false;
            }
        }
    }

    public function clearPendingChatAuthKeyboardByLogout(int $chat_id): void
    {
        if($this->is_active_telegram) {
            $this->clearPendingChatAuthKeyboardForAllTypes($chat_id, false, '👋 Logout');
        }
    }

    public function clearPendingChatAuthKeyboard(int $chat_id, bool $is_auth_by_2fa = false): void
    {
        if($this->is_active_telegram) {
            if ($is_auth_by_2fa) {
                $message_to_add = '☑️ Login Success by Two-Factor-Authenticator';
            } else {
                $message_to_add = '🔄 Replaced by new login request';
            }
            $this->clearPendingChatAuthKeyboardForAllTypes($chat_id, $is_auth_by_2fa, $message_to_add);
        }
    }

    private function clearPendingChatAuthKeyboardForAllTypes(int $chat_id, bool $is_auth_by_2fa = false, $message_to_add = ''): void
    {
        if($this->is_active_telegram) {
            $list = $this->pendingChatList($chat_id);
            if (! empty($list)) {
                foreach ($list as $item) {
                    if (! empty($item['message'])) {
                        $message = $item['message'];
                        $message = str_replace('‼️️', '🔄', $message);
                        if ($is_auth_by_2fa) {
                            $message = str_replace('⚠️', '☑️', $message);
                        } else {
                            $message = str_replace('⚠️', '🔄', $message);
                        }
                        $message .= PHP_EOL . PHP_EOL . $message_to_add . ' at: ' . AppFunctions::CurrentDateTime();
                        $sent = $this->telegramBotManager
                            ->sender
                            ->editMessageText($chat_id, $message, reply_to_message_id: $item['message_id'], parseMode: 'HTML');
                        if (! empty($sent['result']['message_id'])) {
                            $this->Edit([
                                'is_pending' => 0,
                                'message'    => EmojiConverter::emojiToCodepoint($message),
                                'token'      => '',
                            ],
                                "`message_id` = ? AND `chat_id` = ? AND `admin_id` = ? ",
                                [$item['message_id'], $chat_id, $item['admin_id']]);
                        }
                    }
                }
            }
        }
    }

    public function sendNewAuthorization(int $admin_id, string $token): void
    {
        if($this->is_active_telegram) {
            $admin = AdminTelegramBotPortal::obj()->infoByAdmin($admin_id);
            if (! empty($admin)) {
                if (! empty($admin['admin_id']) && ! empty($admin['chat_id'])) {
                    $this->clearPendingChatAuthKeyboard($admin['chat_id']);
                    $platform = GeneralAgentFunctions::obj()->platform();
                    if (! empty($platform)) {
                        $user_agent = PHP_EOL . PHP_EOL
                                      . "platform: " . GeneralAgentFunctions::obj()->platform()
                                      . PHP_EOL
                                      . "browser: " . GeneralAgentFunctions::obj()->browser() . ' ver. (' . GeneralAgentFunctions::obj()->browserVersion() . ')'
                                      . PHP_EOL
                                      . "ip: " . AppFunctions::IP()
                                      . PHP_EOL
                                      . "time: " . AppFunctions::CurrentDateTime()
                                      . PHP_EOL;
                    } else {
                        $user_agent = PHP_EOL;
                    }

                    $text = "⚠️  "
                            . "<b>" . $admin['first_name'] . "</b>, "
                            . "We received a request to log in on " . AppFunctions::PortalName() . " with your account."
                            . PHP_EOL . PHP_EOL
                            . "To authorize this request, use the 'Confirm' button below. "
                            . $user_agent
                            . PHP_EOL
                            . "If you didn't request this, use the 'Decline' button or ignore this message.";
                    $keyboard = [
                        [
                            ['text' => 'Decline', 'callback_data' => 'disallow_auth'],
                            ['text' => 'Confirm', 'callback_data' => 'allow_auth'],
                        ],
                    ];
                    $sent = $this->telegramBotManager->sender->sendMessage($admin['chat_id'],
                        $text,
                        keyboard : $keyboard,
                        parseMode: 'HTML'
                    );
                    if (! empty($sent['result']['message_id'])) {
                        $this->recordNewMessage($admin_id, $admin['chat_id'], $sent['result']['message_id'], $sent['result']['text'], $token);
                    }
                }
            }
        }
    }

    public function recordNewMessage(int $admin_id, int $chat_id, int $message_id, string $message_text, string $token = ''): void
    {
        $this->Add([
            self::IDENTIFY_TABLE_ID_COL_NAME  => $message_id,
            Admin::IDENTIFY_TABLE_ID_COL_NAME => $admin_id,
            'chat_id'                         => $chat_id,
            'is_pending'                      => 1,
            'is_passed'                       => 0,
            'token'                           => $token,
            'message'                         => EmojiConverter::emojiToCodepoint($message_text),
            'time'                            => AppFunctions::CurrentDateTime(),
        ]);
    }

    public function validateAdminPassViaTelegramToken(int $admin_id, string $token): bool
    {
        if (! empty($token)) {
            $admin_col_name = Admin::IDENTIFY_TABLE_ID_COL_NAME;
            if ($this->RowIsExistThisTable("`$admin_col_name` = ? AND `token` = ? AND `is_passed` = ?",
                [$admin_id,
                 $token,
                 1])) {
                return true;
            }
        }

        return false;
    }

    public function sendAdminSessionStartByNewSession(int $admin_id, string $first_name, int $chat_id): void
    {
        if($this->is_active_telegram) {
            $text = '‼️️ Dear <b>' . $first_name . '</b>,'
                    . PHP_EOL . PHP_EOL
                    . 'Your session was started successfully';
            $keyboard = [
                [
                    ['text' => 'Terminate the Session', 'callback_data' => 'terminate_session'],
                ],
            ];
            $this->clearPendingChatAuthKeyboard($chat_id, true);
            $sent = $this->telegramBotManager->sender->sendMessage($chat_id, $text, 0, $keyboard, 'HTML');
            if (! empty($sent['result']['message_id'])) {
                $this->recordNewMessage($admin_id, $chat_id, $sent['result']['message_id'], $text);
            }
        }
    }

    public function allowByTelegram(int $chat_id, string $message, int $message_id): void
    {
        $token = $this->ColThisTable('token', "`chat_id` = ? AND `message_id` = ? ", [$chat_id, $message_id]);
        $this->clearPendingChatAuthKeyboardForAllTypes($chat_id, false, 'Replaced by allowed');
        if($this->is_active_telegram) {
            $keyboard = [
                [
                    ['text' => 'Terminate the Session', 'callback_data' => 'terminate_session'],
                ],
            ];
            $sent = $this->telegramBotManager->sender->editMessageText($chat_id, $message, $message_id, $keyboard, 'HTML');
            if (! empty($sent['result']['message_id'])) {
                $this->Edit([
                    'is_pending' => 1,
                    'is_passed'  => 1,
                    'token' => $token
                ], "`chat_id` = ? AND `message_id` = ? ", [$chat_id, $message_id]);
            }
        }
    }

    public function disallowByTelegram(int $chat_id, string $message, int $message_id): void
    {
        if($this->is_active_telegram) {
            $this->clearPendingChatAuthKeyboardForAllTypes($chat_id, false, '❌ Replaced by declined');
            $sent = $this->telegramBotManager->sender->editMessageText($chat_id, $message, $message_id, [], 'HTML');
            if (! empty($sent['result']['message_id'])) {
                $this->Edit([
                    'is_pending' => 0,
                    'is_passed'  => 0,
                ], "`chat_id` = ? AND `message_id` = ? AND `is_pending` = ?", [$chat_id, $message_id, 1]);;
            }
        }
    }

    public function terminateSession(int $chat_id, string $message, int $message_id): void
    {
        if($this->is_active_telegram) {
            $this->clearPendingChatAuthKeyboardForAllTypes($chat_id, false, 'Session Terminated By TelegramBot');
            $sent = $this->telegramBotManager->sender->editMessageText($chat_id, $message, $message_id, [], 'HTML');
            $admin_id = (int)$this->ColThisTable('admin_id',
                " `chat_id` = ? AND `message_id` = ? ORDER BY $this->identify_table_id_col_name DESC LIMIT 1",
                [$chat_id, $message_id]);
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

    private function pendingChatList(int $chat_id): array
    {
        $list = $this->RowsThisTable('*', "`chat_id` = ? AND `is_pending` = ?", [$chat_id, 1]);
        if (! empty($list)) {
            $list = $this->messageArrayMapTextCodeToEmoji($list);
        }

        return $list;
    }

    private function messageArrayMapTextCodeToEmoji(array $messages): array
    {
        if (! empty($messages)) {
            $messages = array_map(function ($message) {
                return $this->messageTextCodeToEmoji($message);
            }, $messages);
        }

        return $messages;
    }

    private function messageTextCodeToEmoji(array $message): array
    {
        if (! empty($message['message'])) {
            $message['message'] = htmlspecialchars_decode($message['message']);
            $message['message'] = EmojiConverter::codepointToEmoji(($message['message']));
        }

        return $message;
    }
}