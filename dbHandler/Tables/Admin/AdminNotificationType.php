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

use Maatify\Portal\DbHandler\AddRemoveTwoColsHandler;
use Maatify\Portal\Setting\Notification\NotificationTypes;

class AdminNotificationType extends AddRemoveTwoColsHandler
{
    const TABLE_NAME = 'a_notification_type';

    public const TABLE_ALIAS                = '';
    public const LOGGER_TYPE                = Admin::LOGGER_TYPE;
    public const LOGGER_SUB_TYPE            = 'notification_type';
    public const IDENTIFY_TABLE_ID_COL_NAME = Admin::IDENTIFY_TABLE_ID_COL_NAME;
    public const COLS                       =
        [
            Admin::IDENTIFY_TABLE_ID_COL_NAME => 1,
            NotificationTypes::IDENTIFY_TABLE_ID_COL_NAME  => 1,
        ];

    protected string $tableName = self::TABLE_NAME;
    protected string $tableAlias = self::TABLE_ALIAS;
    protected string $logger_type = self::LOGGER_TYPE;
    protected string $logger_sub_type = self::LOGGER_SUB_TYPE;
    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    protected array $cols = self::COLS;
    protected string $table_source_class = Admin::class;
    protected string $table_destination_class = NotificationTypes::class;
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}