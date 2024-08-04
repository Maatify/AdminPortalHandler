<?php
/**
 * @PHP       Version >= 8.0
 * @Liberary  AdminPortalHandler
 * @Project   AdminPortalHandler
 * @copyright ©2024 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-07-27 5:11 PM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/AdminPortalHandler  view project on GitHub
 * @Maatify   AdminPortalHandler :: TelegramBotWebHookAdminController
 */

namespace Maatify\Portal\Admin\TelegramBot;

use App\Assist\AppFunctions;
use App\Assist\Encryptions\EnvEncryption;
use Exception;
use Maatify\CronTelegramBotAdmin\CronTelegramBotAdminSender;
use Maatify\Logger\Logger;
use Maatify\Portal\Admin\Admin;
use Maatify\TelegramBot\TelegramBotManager;

class TelegramBotWebHookAdminController
{
    private static self $instance;
    private int $admin_id = 0;
    private string $admin_first_name = '';
    private string $admin_last_name = '';
    private bool $admin_status = false;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private string $api_key;

    public function __construct()
    {
        $this->api_key = (new EnvEncryption())->DeHashed($_ENV['TELEGRAM_API_KEY_ADMIN']);
    }

    public function handleMessageFromCommand(int $chatId, string $text): array
    {
        if ($admin = AdminTelegramBotPortal::obj()->rowByChatId($chatId)) {
            $this->admin_id = (int)$admin[Admin::IDENTIFY_TABLE_ID_COL_NAME];
            $this->admin_first_name = (string)$admin['first_name'];
            $this->admin_last_name = (string)$admin['last_name'];
            $this->admin_status = (bool)$admin['status'];
        }
        if (! empty($this->admin_id)) {
            switch ($text) {
                case '/start':
                    $message = 'Dear ' . $this->admin_first_name . ' ' . $this->admin_last_name . ',' . PHP_EOL . PHP_EOL;
                    if (! $this->admin_status) {
                        AdminTelegramBotPortal::obj()->activateByChatID($chatId);
                        $message .= '✅ Your account notification was enabled';
                    } else {
                        $message .= '⚠️ Your notification already enabled';
                    }
                    break;

                case '/stop':
                    $message = 'Dear ' . $this->admin_first_name . ' ' . $this->admin_last_name . ',' . PHP_EOL . PHP_EOL;
                    if ($this->admin_status) {
                        AdminTelegramBotPortal::obj()->deactivateByChatID($chatId);
                        $message .= '❌ Your account notifications have been disabled.';
                    } else {
                        $message .= '⚠️ Your notifications are already disabled.';
                    }
                    break;

                case '/send':
                    $message = 'Dear ' . $this->admin_first_name . ' ' . $this->admin_last_name . ',' . PHP_EOL . PHP_EOL;
                    $sent = CronTelegramBotAdminSender::obj()->cronSendByAdminIdAndChatId($this->admin_id, $chatId);
                    if ($sent == 0) {
                        $message .= '⚠️ There are no notifications for you in the queue.';
                    } else {
                        $message .= '✅ All your notification queues have been sent.';
                    }
                    break;

                case '/info':
                    $message = $this->infoMessage();

                    break;

                default;
                    $message = $this->defaultMessage($chatId, $admin['first_name']);
            }
        } else {
            $message = match ($text) {
                '/info' => $this->infoMessage(),
                default => $this->defaultMessage($chatId),
            };
        }

        return [$this->admin_id, $message];
    }

    public function defaultMessage(int $chatId, string $first_name = ''): string
    {
        if (! empty($this->admin_first_name)) {
            if ($this->admin_status) {
                $status_message = 'To stop receiving notifications, please send /stop';
            } else {
                $status_message = 'To start receiving notifications, please send /start.';
            }

            $status_message .= PHP_EOL
                               . PHP_EOL
                               . 'To receive all pending notifications, please send /send.';

            $status_message .= PHP_EOL
                               . PHP_EOL
                               . 'To get my information, Please send /info';
        } else {
            $status_message = '';
        }

        return '‼️️' . $_ENV['TELEGRAM_ADMIN_USERNAME'] . '‼️️'
               . PHP_EOL . PHP_EOL
               . (! $this->admin_first_name ? ('  I don’t know who you are. ⁉️.') : 'Hello! ' . $this->admin_first_name . '.')
               . PHP_EOL . PHP_EOL
               . 'Your chat id : "' . $chatId . '"'
               . PHP_EOL
               . PHP_EOL
               . $status_message
               . $this->infoMessage(false);
    }

    public function infoMessage(bool $bot_name = true): string
    {
        if ($bot_name) {
            $title = '‼️️' . $_ENV['TELEGRAM_ADMIN_USERNAME'] . '‼️️';
        } else {
            $title = '';
        }

        return $title
               . PHP_EOL . PHP_EOL
               . '  I\'m an Assistant for Users who Login and Receive Notifications Only.'
               . PHP_EOL . PHP_EOL
               . '  I\'m Developed by Maatify.'
               . PHP_EOL . PHP_EOL
               . '  I\'ve been active since ' . AppFunctions::PortalGeneratedDate() . '.'
               . PHP_EOL . PHP_EOL
               . '  My Developer\'s website is maatify.dev.';
    }

    public function reply(int $chatId, string $text, int $source_message_id): array
    {
        [$admin_id, $message] = $this->handleMessageFromCommand($chatId, $text);
        return $this->sendUsingTelegram($chatId, $message, $source_message_id);
    }

    private function sendUsingTelegram(int $chat_id, string $text, int $source_message_id, array $keyboard = [], $parseMode = null): array
    {
        try {
            $telegramBotManager = new TelegramBotManager($this->api_key);
            return $telegramBotManager->sender->sendMessage($chat_id, $text, $source_message_id, $keyboard, $parseMode);
        } catch (Exception $exception) {
            Logger::RecordLog($exception, 'telegram_bot_webhook_reply');
            return [];
        }
    }

    public function sendNewMessage(int $chat_id, string $message, int $message_id = 0, $parsMode = null): array
    {
        try {
            $telegramBotManager = new TelegramBotManager($this->api_key);

            return $telegramBotManager->sender->sendMessage($chat_id, $message, $message_id, [], $parsMode);
        } catch (Exception $exception) {
            Logger::RecordLog($exception, 'sendNewMessage');
        }

        return [];
    }

    public function editMessage(int $chat_id, string $message, int $message_id): array
    {
        try {
            $telegramBotManager = new TelegramBotManager($this->api_key);
            $telegramBotManager->sender->editMessageText($chat_id, $message, $message_id, [], 'HTML');
        } catch (Exception $exception) {
            Logger::RecordLog($exception, 'editMessage');
        }

        return [];
    }
}