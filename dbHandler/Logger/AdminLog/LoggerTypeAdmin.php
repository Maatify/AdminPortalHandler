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
 * @Maatify   AdminPortalHandler :: LoggerTypeAdmin
 */

namespace Maatify\Portal\Logger\AdminLog;

use Maatify\Portal\Logger\LoggerHandler\LoggerTypeHandler;

final class LoggerTypeAdmin extends LoggerTypeHandler
{
    protected string $tableName = 'a_logger_types';
}