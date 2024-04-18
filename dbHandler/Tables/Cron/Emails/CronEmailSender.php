<?php
/**
 * @PHP       Version >= 8.0
 * @copyright ©2024 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-04-18 8:55 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/AdminPortalHandler  view project on GitHub
 * @Maatify   DB :: AdminPortalHandler
 */

namespace Maatify\Portal\Cron\Emails;

use \App\Assist\AppFunctions;
use \App\Assist\Encryptions\CronEmailEncryption;
use Maatify\Mailer\Mailer;
use Maatify\Portal\Queue\EmailQueue;

class CronEmailSender extends CronEmail
{
    private static self $instance;

    public static function obj(): self
    {
        if(empty(self::$instance))
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function SentMarker(int $id): void
    {
        $this->Edit([
            'is_sent'     => 1,
            'sent_time'   => AppFunctions::CurrentDateTime(),
        ], '`id` = ? ', [$id]);
    }

    private function NotSent(): array
    {
        return $this->RowsThisTable('*', '`is_sent` = ? ', [0]);
    }

    public function CronSend(): void
    {
        EmailQueue::obj()->Email();
        if($all = $this->NotSent()){
            foreach ($all as $item){
                $mailer = new Mailer($item['email'], $item['name']);
                $message = $item['message'];
                switch ($item['type']){
                    case 1;
                        $type = 'Message';
                        break;
                    case 2;
                        $type = 'ConfirmCustomerLink';
                        break;
                    case 3;
                        $type = 'ConfirmCode';
                        $message = (new CronEmailEncryption())->DeHashed($item['message']);
                        break;
                    case 4;
                        $type = 'TempPassword';
                        $message = (new CronEmailEncryption())->DeHashed($item['message']);
                        break;
                    case 7;
                        $type= 'AdminMessage';
                        break;
                    default;
                        $type = 'Message';
                }
                if($mailer->$type($message, $item['subject'])){
                    $this->SentMarker($item['id']);
                }
            }
        }
    }
}