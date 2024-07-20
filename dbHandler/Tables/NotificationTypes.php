<?php
/**
 * @PHP       Version >= 8.0
 * @copyright ©2024 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-07-25 1:46 PM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/AdminPortalHandler  view project on GitHub
 * @Maatify   DB :: AdminPortalHandler
 */

namespace Maatify\dBHandler\Tables;

use App\DB\DBS\DbConnector;

class NotificationTypes extends DbConnector
{
    public const IDENTIFY_TABLE_ID_COL_NAME = 'notification_id';
    public const TABLE_NAME                 = 'notification_type';
    public const TABLE_ALIAS                = '';
    public const LOGGER_TYPE                = self::TABLE_NAME;
    public const LOGGER_SUB_TYPE            = '';
    public const COLS                       =
        [
            self::IDENTIFY_TABLE_ID_COL_NAME => 1,
            'type'                           => 0,
            'sms_status'                     => 1,
            'telegram_status'                => 1,
            'email_status'                   => 1,
            'app_status'                     => 1,
        ];

    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $tableName = self::TABLE_NAME;
    protected string $tableAlias = self::TABLE_ALIAS;
    protected string $logger_type = self::LOGGER_TYPE;
    protected string $logger_sub_type = self::LOGGER_SUB_TYPE;
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