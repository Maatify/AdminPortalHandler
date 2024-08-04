<?php
/**
 * @PHP       Version >= 8.0
 * @Liberary  AdminPortalHandler
 * @Project   AdminPortalHandler
 * @copyright ©2024 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-07-23 7:55 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/AdminPortalHandler  view project on GitHub
 * @Maatify   AdminPortalHandler :: AdminPhone
 */

namespace Maatify\Portal\Admin\Phone;

use App\DB\DBS\DbConnector;
use Maatify\Portal\Admin\Admin;

class AdminPhone extends DbConnector
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