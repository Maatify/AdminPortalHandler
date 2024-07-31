<?php
/**
 * @PHP       Version >= 8.0
 * @Liberary  AdminPortalHandler
 * @Project   AdminPortalHandler
 * @copyright ©2024 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-04-18 8:55 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/AdminPortalHandler  view project on GitHub
 * @Maatify   AdminPortalHandler :: LoggerTypeHandler
 */

namespace Maatify\Portal\Logger\LoggerHandler;

use \App\DB\DBS\DbLogger;
use Maatify\Json\Json;

class LoggerTypeHandler extends DbLogger
{
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    public function RecordType(string $type): void
    {
        if(!$this->RowIsExistThisTable('`type` = ? ', [$type])){
            $this->Add(['type' => $type]);
        }
    }

    public function JsonAllTypes(): void
    {
        Json::Success($this->RowsThisTable(), line: $this->class_name . __LINE__);
    }
}