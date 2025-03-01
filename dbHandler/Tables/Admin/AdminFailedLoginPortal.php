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
 * @Maatify   AdminPortalHandler :: AdminFailedLoginPortal
 */

namespace Maatify\Portal\Admin;

use JetBrains\PhpStorm\NoReturn;
use Maatify\Json\Json;

class AdminFailedLoginPortal extends AdminFailedLogin
{
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private string $username;
    private string $ip;
    public function __construct()
    {
        parent::__construct();
        $this->username = $this->postValidator->Optional('username', 'username');
        $this->ip = $this->postValidator->Optional('ip', 'ip');
    }

    #[NoReturn] public function Get(): void
    {
        Json::Success($this->Log($this->username, $this->ip));
    }

    #[NoReturn] public function Remove(): void
    {
        $this->SuccessByAdmin($this->ip, $this->username);
        Json::Success($this->Log($this->username, $this->ip));
    }
}