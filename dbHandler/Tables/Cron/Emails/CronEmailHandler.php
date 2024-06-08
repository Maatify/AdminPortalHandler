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
use \App\DB\DBS\DbConnector;

abstract class CronEmailHandler extends DbConnector
{
    protected string $tableName = 'cron_email';

    protected array $cols = [
        'id'          => 1,
        'type'        => 1,
        'ct_id'       => 1,
        'name'        => 0,
        'email'       => 0,
        'message'     => 0,
        'subject'     => 0,
        'record_time' => 0,
        'is_sent'     => 1,
        'sent_time'   => 0,
    ];

    protected function AddCron(int $ct_id,string $name, string $email, string $message, string $subject, int $type = 1): void
    {
        $this->Add([
            'ct_id'       => $ct_id,
            'type'        => $type,
            'name'        => $name,
            'email'       => $email,
            'message'     => $message,
            'subject'     => $subject,
            'record_time' => AppFunctions::CurrentDateTime(),
            'is_sent'     => 0,
            'sent_time'   => AppFunctions::DefaultDateTime(),
        ]);
    }
}