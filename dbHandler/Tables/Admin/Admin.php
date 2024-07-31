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
 * @Maatify   AdminPortalHandler :: Admin
 */

namespace Maatify\Portal\Admin;

use Maatify\Portal\DbHandler\ParentClassHandler;

class Admin extends ParentClassHandler
{
    const TABLE_NAME = 'admin';
    protected string $tableName = self::TABLE_NAME;
    const IDENTIFY_TABLE_ID_COL_NAME = 'admin_id';
    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    const LOGGER_TYPE = self::TABLE_NAME;
    protected string $logger_type = self::LOGGER_TYPE;
    protected string $logger_sub_type = self::LOGGER_TYPE;
    protected string $tableAlias = 'user';
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected array $cols = [
        'admin_id' => 1,
        'username' => 0,
        'name'     => 0,
        'isAdmin'  => 1,
        'isActive' => 1,
    ];

    public function ExistID(int $admin_id): bool
    {
        return $this->ExistIDThisTable($admin_id);
    }

    public function PostedAdminID(): int
    {
        $this->row_id = $this->ValidatePostedTableId();

        return $this->row_id;
    }
}