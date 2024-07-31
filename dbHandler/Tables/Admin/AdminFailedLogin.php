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
 * @Maatify   AdminPortalHandler :: AdminFailedLogin
 */

namespace Maatify\Portal\Admin;

use Maatify\FailedLoginHandler\FailedLoginHandler;

class AdminFailedLogin extends FailedLoginHandler
{
    const TABLE_NAME = 'a_f_login';
    protected string $tableName = self::TABLE_NAME;

    protected int $tries = 50;

    protected string $col_name = 'username';
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }


}