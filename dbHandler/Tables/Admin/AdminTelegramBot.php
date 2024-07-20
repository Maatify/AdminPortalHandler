<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2024-07-23
 * Time: 11:52 AM
 * https://www.Maatify.dev
 */

namespace Maatify\Portal\Admin;

use App\DB\DBS\DbConnector;

class AdminTelegramBot extends DbConnector
{
    const TABLE_NAME                 = 'a_telegram';
    const TABLE_ALIAS                = 'telegram';
    const IDENTIFY_TABLE_ID_COL_NAME = Admin::IDENTIFY_TABLE_ID_COL_NAME;
    const LOGGER_TYPE                = Admin::LOGGER_TYPE;
    const LOGGER_SUB_TYPE            = 'telegram';
    const COLS                       =
        [
            self::IDENTIFY_TABLE_ID_COL_NAME => 1,
            'chat_id'                        => 1,
            'username'                       => 0,
            'first_name'                     => 0,
            'last_name'                      => 0,
            'photo_url'                      => 0,
            'status'                         => 1,
            'auth_date'                      => 0,
            'time'                           => 0,
        ];

    protected string $tableName = self::TABLE_NAME;
    protected string $tableAlias = self::TABLE_ALIAS;
    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
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