<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2024-07-23
 * Time: 7:55 AM
 * https://www.Maatify.dev
 */

namespace Maatify\Portal\Admin;

use Maatify\Portal\DbHandler\ParentClassHandler;

class AdminPhone extends ParentClassHandler
{
    const TABLE_NAME                 = 'a_phone';
    const TABLE_ALIAS                = '';
    const IDENTIFY_TABLE_ID_COL_NAME = Admin::IDENTIFY_TABLE_ID_COL_NAME;
    const LOGGER_TYPE                = Admin::LOGGER_TYPE;
    const LOGGER_SUB_TYPE            = 'phone';
    const Cols           =
        [
            self::IDENTIFY_TABLE_ID_COL_NAME => 1,
            'phone'                      => 0,
        ];

    protected string $tableName = self::TABLE_NAME;
    protected string $tableAlias = self::TABLE_ALIAS;
    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $logger_type = self::LOGGER_TYPE;
    protected string $logger_sub_type = self::LOGGER_SUB_TYPE;
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}