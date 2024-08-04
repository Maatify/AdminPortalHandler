<?php
/**
 * @PHP       Version >= 8.0
 * @Liberary  AdminPortalHandler
 * @Project   AdminPortalHandler
 * @copyright ©2024 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-07-06 11:09 PM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/AdminPortalHandler  view project on GitHub
 * @Maatify   AdminPortalHandler :: AdminPrivilegeHandler
 */

namespace Maatify\Portal\Admin\Privilege;

class AdminPrivilegeHandler extends AdminPrivilege
{
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function storePrivileges(int $admin_id, bool $is_admin): void
    {
        $_SESSION['is_master'] = false;
        if ($admin_id <= $this->MasterIds()) {
            $_SESSION['is_master'] = true;
        } else {
            if ($is_admin) {
                $_SESSION['is_admin'] = true;
            } else {
                $_SESSION['privileges'] = $this->AllAllowedPagesAndMethods($admin_id);
            }
        }
    }

    public function hasAccessTo(string $page, string $method): bool
    {
        if (isset($_SESSION['is_master']) && $_SESSION['is_master']) {
            return true;
        }

        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
            return true;
        }

        if (isset($_SESSION['privileges'][$page])) {
            return in_array($method, $_SESSION['privileges'][$page]);
        }

        return false;
    }
}