<?php
/**
 * @PHP       Version >= 8.0
 * @copyright ©2024 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-07-26 10:57 PM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/AdminPortalHandler  view project on GitHub
 * @Maatify   DB :: AdminPortalHandler
 */

namespace Maatify\Portal\Admin;

use App\Assist\AppFunctions;
use Maatify\CronTelegramBotAdmin\CronTelegramBotAdminRecord;
use Maatify\Functions\GeneralAgentFunctions;

class AlertAdminTelegramBot extends CronTelegramBotAdminRecord
{
    private static self $instance;
    private CronTelegramBotAdminRecord $telegram_bot;

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
        $this->telegram_bot = CronTelegramBotAdminRecord::obj();
    }

    public function alertLogin(int $admin_id,int $chat_id): void
    {
        $this->telegram_bot->RecordMessage(
            $admin_id,
            $chat_id,
            'You have Successfully Login ' . $this->AddAlertDetails()
        );
    }

    public function alertFailedLogin(int $admin_id,int $chat_id): void
    {
        $this->telegram_bot->RecordMessage(
            $admin_id,
            $chat_id,
            'You have Failed Login ' . $this->AddAlertDetails()
        );
    }

    public function alertMessageOfAgent(int $admin_id,int $chat_id, string $message): void
    {
        $this->telegram_bot->RecordMessage(
            $admin_id,
            $chat_id,
            $message . ' ' . $this->AddAlertDetails()
        );
    }

    public function alertMessageNoAgent(int $admin_id,int $chat_id, string $message): void
    {
        $this->telegram_bot->RecordMessage(
            $admin_id,
            $chat_id,
            $message
        );
    }

    public function alertTempPassword(int $admin_id,int $chat_id, string $password): void
    {
        $this->telegram_bot->RecordTempPassword($admin_id,$chat_id,$password);
    }


    private function AddAlertDetails(): string
    {
        $platform = GeneralAgentFunctions::obj()->platform();
        if(!empty($platform)) {
            return PHP_EOL. PHP_EOL
                   . "platform: " . GeneralAgentFunctions::obj()->platform()
                   . PHP_EOL
                   . "browser: " . GeneralAgentFunctions::obj()->browser() . ' ver. (' . GeneralAgentFunctions::obj()->browserVersion() . ')'
                   . PHP_EOL
                   . "ip: " . AppFunctions::IP()
                   . PHP_EOL
                   . "time: " . AppFunctions::CurrentDateTime();
        }else{
            return '';
        }
    }
}