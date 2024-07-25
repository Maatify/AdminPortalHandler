<?php

/**
 * @PHP       Version >= 8.0
 * @copyright ©2024 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-07-25 4:35 PM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/AdminPortalHandler  view project on GitHub
 * @Maatify   DB :: AdminPortalHandler
 */

namespace Maatify\Portal\Admin;

use App\DB\DBS\DbConnector;
use Maatify\Portal\DbHandler\AddRemoveTwoColsHandler;
use Maatify\Portal\Setting\Notification\NotificationTypes;
use Maatify\Portal\Setting\Notification\NotificationTypesPortal;

class AdminNotificationType extends DbConnector
{
    public const TABLE_NAME = 'a_notification_type';
    public const TABLE_ALIAS                = '';
    public const LOGGER_TYPE                = Admin::LOGGER_TYPE;
    public const LOGGER_SUB_TYPE            = 'notification_type';
    public const IDENTIFY_TABLE_ID_COL_NAME = Admin::IDENTIFY_TABLE_ID_COL_NAME;
    public const COLS                       =
        [
            AdminPortal::IDENTIFY_TABLE_ID_COL_NAME => 1,
            NotificationTypesPortal::IDENTIFY_TABLE_ID_COL_NAME  => 1,
        ];

    protected string $tableName = self::TABLE_NAME;
    protected string $tableAlias = self::TABLE_ALIAS;
    protected string $logger_type = self::LOGGER_TYPE;
    protected string $logger_sub_type = self::LOGGER_SUB_TYPE;
    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    protected array $cols = self::COLS;
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}