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

use \App\Assist\Encryptions\CronEmailEncryption;

class CronEmailRecord extends CronEmail
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

    public function RecordMessage(int $ct_id,string $name, string $email, string $message, string $subject): void
    {
        $this->AddCron($ct_id, $name, $email, $message, $subject, 1);
    }

    public function RecordConfirmLink(int $ct_id,string $email, string $message): void
    {
        $this->AddCron($ct_id, $email, $email, $message, 'Confirm Mail', 2);
    }

    public function RecordConfirmCode(int $ct_id,string $email, string $code, $name = ''): void
    {
        if(empty($name)){
            $name = $email;
        }

        $this->AddCron($ct_id, $name, $email, (new CronEmailEncryption())->Hash($code), 'Confirm Code', 3);
    }

    public function RecordTempPassword(int $ct_id,string $email, string $code, $name = ''): void
    {
        if(empty($name)){
            $name = $email;
        }

        $this->AddCron($ct_id, $name, $email, (new CronEmailEncryption())->Hash($code), 'Your Temporary Password', 4);
    }

    public function RecordAdminMessage(int $ct_id,string $name, string $email, string $message, string $subject): void
    {
        $this->AddCron($ct_id, $name, $email, $message, $subject, 7);
    }



    public function RecordDashboardConfirmLink(int $ct_id,string $email, string $message, $name = ''): void
    {
        if(empty($name)){
            $name = $email;
        }

        $this->AddCron($ct_id, $name, $email, $message, 'Confirm Mail', 5);
    }

    public function RecordDashboardForgetLink(int $ct_id,string $email, string $message, $name = ''): void
    {
        if(empty($name)){
            $name = $email;
        }

        $this->AddCron($ct_id, $name, $email, $message, 'Reset Password', 6);
    }
}