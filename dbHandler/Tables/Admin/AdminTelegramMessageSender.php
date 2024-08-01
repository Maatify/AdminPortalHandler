<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2024-08-03
 * Time: 3:47 PM
 * https://www.Maatify.dev
 */

namespace Maatify\Portal\Admin;

use App\Assist\AppFunctions;
use App\Assist\Encryptions\EnvEncryption;
use Exception;
use Maatify\Logger\Logger;
use Maatify\TelegramBot\TelegramBotManager;

class AdminTelegramMessageSender extends AdminTelegramMessage
{
    private static self $instance;
    public TelegramBotManager $telegram;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();
        $api_key = (new EnvEncryption())->DeHashed($_ENV['TELEGRAM_API_KEY_ADMIN']);
        $this->telegram = (new TelegramBotManager($api_key));
    }

    public function clearPreviousAuth(int $chat_id, string $message_to_add = ''): void
    {
        $list = AdminTelegramMessageSender::obj()->notDeletedAuthListOfChatId($chat_id);
        if(!empty($list)){
            foreach ($list as $item) {
                $item['message'] = str_replace('⚠️', '🔄', $item['message']);
                $item['message'] = str_replace('‼', '🔄', $item['message']);
                $item['message'] = str_replace('‼️️', '🔄', $item['message']);
                $item['message'] .= PHP_EOL . PHP_EOL . $message_to_add . ' at: ' . AppFunctions::CurrentDateTime();
                $sent = $this->telegram->sender->editMessageText($chat_id, $item['message'], reply_to_message_id: $item['message_id'], parseMode: 'HTML');
                if(!empty($sent['result']['message_id'])){
                    $this->markMessageDeleted($chat_id, $item['message_id']);
                }
            }
        }
    }

    public function sendAdminSessionStartByNewSession(string $first_name, int $chat_id): array
    {
        $text = '‼️️ Dear <b>' . $first_name . '</b>,'
                . PHP_EOL . PHP_EOL
                . 'Your session was started successfully';
        $keyboard = [
            [
                ['text' => 'Terminate the Session', 'callback_data' => 'terminate_session'],
            ],
        ];
        $this->clearPreviousAuth($chat_id, 'Replaced by new successfully session');
        return $this->send($chat_id, $text, 0, $keyboard, true);
    }

    public function allowFromTelegram($chat_id, $message_id, string $message): array
    {
        $keyboard = [
            [
                ['text' => 'Terminate the Session', 'callback_data' => 'terminate_session'],
            ],
        ];
        try {
            AdminTelegramMessageSender::obj()->clearPreviousAuth($chat_id, 'Replaced by new successfully session');
            $sent = $this->telegram->sender->editMessageText($chat_id, $message, $message_id, $keyboard, 'HTML');
            if(!empty($sent['result']['message_id'])){
                AdminTelegramPassPortal::obj()->allowByTelegram($chat_id, $message_id);
                $this->markMessageDeleted($chat_id, $message_id);
                $this->recordNewMessage($chat_id, $message_id, $message, !empty($keyboard), true);
            }
        } catch (Exception $exception) {
            Logger::RecordLog($exception, 'allowedAuth');
        }
        return [];
    }



    public function disallowedAuth(int $chat_id, int $message_id, string $message): array
    {
        try {
            AdminTelegramMessageSender::obj()->clearPreviousAuth($chat_id, 'Replaced by declined');
            $sent = $this->telegram->sender->editMessageText($chat_id, $message, $message_id, [], 'HTML');
            if(!empty($sent['result']['message_id'])){
                AdminTelegramPassPortal::obj()->disallowByTelegram($chat_id, $message_id);
                $this->markMessageDeleted($chat_id, $message_id);
                $this->recordNewMessage($chat_id, $message_id, $message, !empty($keyboard), true);
            }
        } catch (Exception $exception) {
            Logger::RecordLog($exception, 'disallowedAuth');
        }
        return [];
    }

    public function terminateSession(?int $chat_id, ?int $message_id, string $message): array
    {
        try {
            AdminTelegramMessageSender::obj()->clearPreviousAuth($chat_id, 'Replaced by Terminate Session');
            $sent = $this->telegram->sender->editMessageText($chat_id, $message, $message_id, [], 'HTML');
            if(!empty($sent['result']['message_id'])){
                AdminTelegramPassPortal::obj()->terminateSession($chat_id, $message_id);
                $this->markMessageDeleted($chat_id, $message_id);
            }
        } catch (Exception $exception) {
            Logger::RecordLog($exception, 'terminateSession');
        }
        return [];
    }

    public function send(int $chat_id, string $message, int $reply_to_message_id = 0, array $keyboard = [], bool $is_auth = false): array
    {
        try {
            $sent = $this->telegram->sender->sendMessage($chat_id, $message, $reply_to_message_id, $keyboard, 'HTML');
            if (! empty($sent['result']['message_id'])) {
                if(empty($reply_to_message_id)) {
                    $this->recordNewMessage($chat_id, $sent['result']['message_id'], $message, ! empty($keyboard), $is_auth);
                }

                if(empty($keyboard)){
                    $this->markMessageDeleted($chat_id, $reply_to_message_id);
                }
            }
            return $sent;
        } catch (Exception $exception) {
            Logger::RecordLog($exception, 'send_admin_message');
            return [];
        }
    }


}