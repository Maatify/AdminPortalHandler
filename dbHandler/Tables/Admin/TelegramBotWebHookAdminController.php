<?php
/**
 * @PHP       Version >= 8.0
 * @copyright ©2024 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-07-27 5:11 PM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/AdminPortalHandler  view project on GitHub
 * @Maatify   DB :: AdminPortalHandler
 */

namespace Maatify\Portal\Admin;

use App\Assist\AppFunctions;
use App\Assist\Encryptions\EnvEncryption;
use Exception;
use Maatify\CronTelegramBotAdmin\CronTelegramBotAdminSender;
use Maatify\Functions\GeneralAgentFunctions;
use Maatify\Logger\Logger;
use Maatify\TelegramBot\TelegramBotManager;

class TelegramBotWebHookAdminController
{
    private static self $instance;

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
        $admin_id = AdminTelegramBotPortal::obj()->getAdminByChatId($chatId);
        if (! empty($admin_id)) {
            switch ($text) {
                case '/start':
                    if (AdminTelegramBotPortal::obj()->activateByChatID($chatId)) {
                        $message = 'Your account notification was enabled';
                    } else {
                        $message = 'Your notification already enabled';
                    }
                    break;

                case '/stop':
                    if (AdminTelegramBotPortal::obj()->deactivateByChatID($chatId)) {
                        $message = 'Your account notification was disabled';
                    } else {
                        $message = 'Your notification already disabled';
                    }
                    break;

                case '/send':
                    $sent = CronTelegramBotAdminSender::obj()->cronSendByAdminIdAndChatId($admin_id, $chatId);
                    if ($sent == 0) {
                        $message = 'there is no notification for you in queue';
                    } else {
                        $message = 'all notifications for your account in queue were sent';
                    }
                    break;

                case '/info':
                    $message = '‼️️' . $_ENV['TELEGRAM_ADMIN_USERNAME'] . '‼️️'
                               . PHP_EOL . PHP_EOL
                               . '  I\'m an Assistance for User Notifications and Login Only.'
                               . PHP_EOL . PHP_EOL
                               . '  I\'m Developed by Maatify.'
                               . PHP_EOL . PHP_EOL
                               . '  I\'ve been active since 2024-07-31.'
                               . PHP_EOL . PHP_EOL
                               . '  My Developer\'s website is maatify.dev.';

                    break;

                default;
                    $message =
                        'Welcome to: ' . $_ENV['TELEGRAM_ADMIN_USERNAME']
                        . PHP_EOL . PHP_EOL .
                        'your chat id : ' . $chatId
                        . PHP_EOL
                        . PHP_EOL
                        . 'This bot For Alerts only and its not for replay with any other message or help'
                        . PHP_EOL
                        . 'هذا البوت تم تصميمه فقط لإشعارات المستخدمين ولا يقوم برد مختلف في اي وقت عن هذه الرسالة وغير مخصص للمساعدة'
                        . PHP_EOL
                        . 'to start receiving message from bot sent /start';

            }
        } else {
            $message = match ($text) {
                '/info' => '‼️️' . $_ENV['TELEGRAM_ADMIN_USERNAME'] . '‼️️'
                           . PHP_EOL . PHP_EOL
                           . '  I\'m an Assistance for User Notifications and Login Only.'
                           . PHP_EOL . PHP_EOL
                           . '  I\'m Developed by Maatify.'
                           . PHP_EOL . PHP_EOL
                           . '  I\'ve been active since ' . AppFunctions::PortalGeneratedDate() . '.'
                           . PHP_EOL . PHP_EOL
                           . '  My Developer\'s website is maatify.dev.',
                default => 'Welcome to: ' . $_ENV['TELEGRAM_ADMIN_USERNAME']
                           . PHP_EOL . PHP_EOL
                           . 'I Don\'t know who you are ⁉️'
                           . PHP_EOL . PHP_EOL
                           . 'your chat id : ' . $chatId
                           . PHP_EOL
                           . PHP_EOL
                           . 'This bot For Alerts only and its not for replay with any other message or help'
                           . PHP_EOL
                           . 'هذا البوت تم تصميمه فقط لإشعارات المستخدمين ولا يقوم برد مختلف في اي وقت عن هذه الرسالة وغير مخصص للمساعدة'
                           . PHP_EOL
                           . 'to start receiving message from bot sent /start',
            };
        }
        return [$admin_id, $message];
    }

    public function reply(int $chatId, string $text, int $source_message_id): void
    {
        [$admin_id, $message] = $this->handleMessageFromCommand($chatId, $text);
        $this->sendUsingTelegram($chatId, $message, $source_message_id);
    }

    private function sendUsingTelegram(int $chat_id, string $text, int $source_message_id): void
    {
        try {
            $telegramBotManager = new TelegramBotManager($this->api_key);
            $telegramBotManager->Sender()->SendMessage($chat_id, $text, $source_message_id);
        } catch (Exception $exception) {
            Logger::RecordLog($exception, 'telegram_bot_webhook_reply');
        }
    }

    private function sendUsingTelegramWithKeyboard(int $chat_id, string $text, int $source_message_id, array $keyboard = []): void
    {
        try {
            $telegramBotManager = new TelegramBotManager($this->api_key);
            $telegramBotManager->Sender()->SendMessageWithKeyboardMarkup($chat_id, $text, $source_message_id, $keyboard);
        } catch (Exception $exception) {
            Logger::RecordLog($exception, 'telegram_bot_webhook_reply');
        }
    }

    public function clearAuthKeyboard(int $chat_id, int $message_id): void
    {
        try {
            $telegramBotManager = new TelegramBotManager($this->api_key);
            //            $telegramBotManager->Sender()->editMessageReplyMarkup($chat_id, reply_to_message_id: $message_id);
            $telegramBotManager->Sender()->editMessageText($chat_id, 'new authorization requested after this message', reply_to_message_id: $message_id);
        } catch (Exception $exception) {
            Logger::RecordLog($exception, 'clearAuthKeyboard');
        }
    }

    public function sendAuthorization(string $first_name, string $chat_id): array
    {
        try {
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
                              . PHP_EOL . PHP_EOL;
            } else {
                $user_agent = PHP_EOL;
            }

            $text = $first_name . ", we received a request to log in on  with your account."
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
            $telegramBotManager = new TelegramBotManager($this->api_key);

            return $telegramBotManager->Sender()->SendMessage($chat_id,
                $text,
                keyboard: $keyboard
            );
        } catch (Exception $exception) {
            Logger::RecordLog($exception, 'sendAuthorization');
        }

        return [];
    }

    public function sendNewMessage(int $chat_id, string $message, int $message_id = 0, $parsMode = null): array
    {
        try {
            $telegramBotManager = new TelegramBotManager($this->api_key);
            return $telegramBotManager->Sender()->sendMessage($chat_id, $message, $message_id, [], $parsMode);
        } catch (Exception $exception) {
            Logger::RecordLog($exception, 'sendNewMessage');
        }
        return [];
    }

    public function allowedAuth(int $chat_id, string $message, int $message_id): array
    {
        $keyboard = [
            [
                ['text' => 'Terminate the Session', 'callback_data' => 'terminate_session'],
            ],
        ];
        try {
            $telegramBotManager = new TelegramBotManager($this->api_key);
            $telegramBotManager->Sender()->editMessageText($chat_id, $message, $message_id, $keyboard, 'HTML');
        } catch (Exception $exception) {
            Logger::RecordLog($exception, 'allowedAuth');
        }
        return [];

    }

    public function editMessage(int $chat_id, string $message, int $message_id): array
    {
        try {
            $telegramBotManager = new TelegramBotManager($this->api_key);
            $telegramBotManager->Sender()->editMessageText($chat_id, $message, $message_id, [], 'HTML');
        } catch (Exception $exception) {
            Logger::RecordLog($exception, 'editMessage');
        }
        return [];

    }
}