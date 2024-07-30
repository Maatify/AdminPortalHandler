<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2024-07-30
 * Time: 2:31 PM
 * https://www.Maatify.dev
 */

namespace Maatify\Portal\Admin;

use Maatify\Portal\DbHandler\ParentClassHandler;

class AdminPassViaTelegram extends ParentClassHandler
{
    const TABLE_NAME                 = 'a_pass_telegram';
    const TABLE_ALIAS                = '';
    const IDENTIFY_TABLE_ID_COL_NAME = 'message_id';
    const LOGGER_TYPE                = Admin::LOGGER_TYPE;
    const LOGGER_SUB_TYPE            = 'AuthViaTelegram';
    const COLS                       = [
        self::IDENTIFY_TABLE_ID_COL_NAME  => 1,
        Admin::IDENTIFY_TABLE_ID_COL_NAME => 1,
        'chat_id'                         => 1,
        'is_pending'                      => 1,
        'is_passed'                       => 1,
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