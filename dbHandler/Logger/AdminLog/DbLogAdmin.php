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
 * @Maatify   AdminPortalHandler :: DbLogAdmin
 */

namespace Maatify\Portal\Logger\AdminLog;

use Maatify\Portal\Logger\LoggerHandler\DbLogHandler;

class DbLogAdmin extends DbLogHandler
{
    protected string $tableName = 'a_logger';
    private static self $instance;
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
        $this->logg_keys = new LoggerKeysAdmin();
        $this->log_type = new LoggerTypeAdmin();
        $this->log_sub_type = new LoggerSubTypeAdmin();
        $this->log_changes = new LoggerChangesAdmin();
    }
}