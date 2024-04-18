<?php
/**
 * @PHP       Version >= 8.0
 * @copyright ©2024 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-04-18 8:55 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/AdminPortalHandler  view project on GitHub
 * @Maatify   DB :: AdminPortalHandler
 */

namespace Maatify\Portal\Logger\AdminLog;

use Maatify\Portal\Logger\LoggerHandler\LoggerKeysHandler;

final class LoggerKeysAdmin extends LoggerKeysHandler
{
    protected string $tableName = 'a_logger_keys';

}