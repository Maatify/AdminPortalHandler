<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2024-07-06
 * Time: 11:09 PM
 * https://www.Maatify.dev
 */

namespace Maatify\Portal\Admin;

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