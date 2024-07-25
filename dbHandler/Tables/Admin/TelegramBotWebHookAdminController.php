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

use App\Assist\Encryptions\EnvEncryption;
use Maatify\CronTelegramBotAdmin\CronTelegramBotAdminSender;
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

    public function reply(int $chatId, string $text, int $source_message_id): void
    {
        $admin_id = AdminTelegramBotPortal::obj()->getAdminByChatId($chatId);
        if(!empty($admin_id)) {
            switch ($text){
                case '/start':
                    if(AdminTelegramBotPortal::obj()->activateByChatID($chatId)){
                        $message = 'Your account notification was enabled';
                    }else{
                        $message = 'Your notification already enabled';
                    }
                    break;

                case '/stop':
                    if(AdminTelegramBotPortal::obj()->deactivateByChatID($chatId)){
                        $message = 'Your account notification was disabled';
                    }else{
                        $message = 'Your notification already disabled';
                    }
                    break;

                case '/send':
                    $sent = CronTelegramBotAdminSender::obj()->cronSendByAdminIdAndChatId($admin_id, $chatId);
                    if($sent == 0){
                        $message = 'there is no notification for you in queue';
                    }else{
                        $message = 'all notifications for your account in queue were sent';
                    }
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
                 $keyboard = [
                     [
                         ['text' => 'Button 1', 'callback_data' => 'action1'], ['text' => 'Button 2', 'callback_data' => 'action2']
                     ]
                 ];
                    $this->sendUsingTelegramWithKeyboard($chatId, $message, $source_message_id, $keyboard);
                    return;

            }
        }else{
            $message =
                'Welcome to: ' . $_ENV['TELEGRAM_ADMIN_USERNAME']
                . PHP_EOL . PHP_EOL
                . 'I Don\'t know who are you'
                . PHP_EOL . PHP_EOL
                . 'your chat id : ' . $chatId
                . PHP_EOL
                . PHP_EOL
                . 'This bot For Alerts only and its not for replay with any other message or help'
                . PHP_EOL
                . 'هذا البوت تم تصميمه فقط لإشعارات المستخدمين ولا يقوم برد مختلف في اي وقت عن هذه الرسالة وغير مخصص للمساعدة'
                . PHP_EOL
                . 'to start receiving message from bot sent /start';
        }
        $this->sendUsingTelegram($chatId, $message, $source_message_id);
    }

    private function sendUsingTelegram(int $chat_id, string $text, int $source_message_id): void{
        try {
            $telegramBotManager = new TelegramBotManager($this->api_key);
            $telegramBotManager->Sender()->SendMessage($chat_id, $text, $source_message_id);
        }catch (\Exception $exception){
            Logger::RecordLog($exception, 'telegram_bot_webhook_reply');
        }
    }

    private function sendUsingTelegramWithKeyboard(int $chat_id, string $text, int $source_message_id, array $keyboard): void{
        try {
            $telegramBotManager = new TelegramBotManager($this->api_key);
            $telegramBotManager->Sender()->SendMessageWithKeyboardMarkup($chat_id, $text, $source_message_id, $keyboard);
        }catch (\Exception $exception){
            Logger::RecordLog($exception, 'telegram_bot_webhook_reply');
        }
    }
}