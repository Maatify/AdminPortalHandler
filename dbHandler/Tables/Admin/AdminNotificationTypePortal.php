<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2024-07-25
 * Time: 6:50 PM
 * https://www.Maatify.dev
 */

namespace Maatify\Portal\Admin;

use Maatify\Portal\DbHandler\AddRemoveTwoColsHandler;
use Maatify\Portal\Setting\Notification\NotificationTypes;
use Maatify\Portal\Setting\Notification\NotificationTypesPortal;

class AdminNotificationTypePortal extends AddRemoveTwoColsHandler
{
    public const IDENTIFY_TABLE_ID_COL_NAME = AdminNotificationType::IDENTIFY_TABLE_ID_COL_NAME;
    public const TABLE_NAME                 = AdminNotificationType::TABLE_NAME;
    public const TABLE_ALIAS                = AdminNotificationType::TABLE_ALIAS;
    public const LOGGER_TYPE                = AdminNotificationType::LOGGER_TYPE;
    public const LOGGER_SUB_TYPE            = AdminNotificationType::LOGGER_SUB_TYPE;
    public const COLS                       = AdminNotificationType::COLS;
    public const IMAGE_FOLDER               = self::TABLE_NAME;

    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $tableName = self::TABLE_NAME;
    protected string $tableAlias = self::TABLE_ALIAS;
    protected string $logger_type = self::LOGGER_TYPE;
    protected string $logger_sub_type = self::LOGGER_SUB_TYPE;
    protected array $cols = self::COLS;
    protected string $table_source_class = AdminPortal::class;
    protected string $table_destination_class = NotificationTypesPortal::class;
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}