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

namespace Maatify\Portal\Logger\LoggerHandler;

use \App\DB\DBS\DbLogger;

abstract class LoggerKeysHandler extends DbLogger
{
    protected array $cols = [
        'id' => 1,
        'log_id' => 1,
        'for_key' => 0,
        'key_value' => 1,
    ];
    public function RecordKeys(int $log_id, array $keys): void
    {
        foreach ($keys as $key => $value) {
            $this->RecordKey($log_id, $key, $value);
        }
    }

    public function RecordKey(int $log_id, string $for_key, $key_value): void
    {
        $this->Add([
            'log_id' => $log_id,
            'for_key' => $for_key,
            'key_value' => $key_value,
        ]);
    }
}