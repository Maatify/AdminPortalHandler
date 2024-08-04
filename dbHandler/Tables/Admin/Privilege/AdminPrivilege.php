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
 * @Maatify   AdminPortalHandler :: AdminPrivilege
 */

namespace Maatify\Portal\Admin\Privilege;

use App\Assist\AppFunctions;
use Maatify\Functions\GeneralFunctions;
use Maatify\Json\Json;
use Maatify\Portal\Admin\Admin;
use Maatify\Portal\Admin\AdminLoginToken;
use Maatify\Portal\DbHandler\AddRemoveTwoColsHandler;
use Maatify\Portal\Privileges\PrivilegeMethods;
use Maatify\Portal\Privileges\PrivilegeRoles;
use Maatify\Portal\Privileges\Privileges;

abstract class AdminPrivilege extends AddRemoveTwoColsHandler
{
    const TABLE_NAME = 'a_roles';
    const IDENTIFY_TABLE_ID_COL_NAME = Admin::IDENTIFY_TABLE_ID_COL_NAME;
    const LOGGER_TYPE                = Admin::LOGGER_TYPE;
    const LOGGER_SUB_TYPE            = 'roles';

    protected string $tableName = self::TABLE_NAME;
    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $logger_type = self::LOGGER_TYPE;
    protected string $logger_sub_type = self::LOGGER_SUB_TYPE;
    protected string $table_source_class = Admin::class;
    protected string $table_destination_class = PrivilegeRoles::class;

    public function MasterIds(): int
    {
        return AppFunctions::admin_master_id;
    }

    public function IsMaster(int $admin_id): bool
    {
        if($admin_id <= self::MasterIds()){
            return true;
        }
        return false;
    }

    public function AllAllowedPagesAndMethods(int $admin_id): array
    {
        $tb_privilege_roles = PrivilegeRoles::TABLE_NAME;
        $col_privilege_roles = PrivilegeRoles::IDENTIFY_TABLE_ID_COL_NAME;

        $tb_privilege = Privileges::TABLE_NAME;

        $tb_privilege_methods = PrivilegeMethods::TABLE_NAME;
        $col_privilege_methods = PrivilegeMethods::IDENTIFY_TABLE_ID_COL_NAME;

        $tb_admin_role = self::TABLE_NAME;
        $privileges = $this->Rows(
            "`$tb_privilege_roles` 
           INNER JOIN `$tb_admin_role` ON `$tb_privilege_roles`.`$col_privilege_roles` = `$tb_admin_role`.`$col_privilege_roles`
           INNER JOIN `$tb_privilege` ON `$tb_privilege_roles`.`$col_privilege_roles` = `$tb_privilege`.`$col_privilege_roles`
           INNER JOIN `$tb_privilege_methods` ON `$tb_privilege_methods`.`$col_privilege_methods` = `$tb_privilege`.`$col_privilege_methods`",
            "`$tb_privilege_methods`.`method`, `$tb_privilege_methods`.`page`",
            "`$tb_admin_role`.`$this->identify_table_id_col_name` = ? ",
            [$admin_id]
        );
        $final_array = array();
        foreach ($privileges as $privilege) {
            if (!isset($final_array[$privilege['page']])) {
                $final_array[$privilege['page']] = [];
            }
            $final_array[$privilege['page']][] = $privilege['method'];
        }
        return $final_array;
    }


    /** deprecated */
    public function AllAllowedMethods(int $admin_id): array
    {
        $tb_privilege_roles = PrivilegeRoles::obj()->TableName();
        $tb_privilege = Privileges::obj()->TableName();
        $tb_privilege_methods = PrivilegeMethods::obj()->TableName();
        $tb_admin_role = self::TABLE_NAME;
        $privileges = $this->Rows(
            "`$tb_privilege_roles` 
           INNER JOIN `$tb_admin_role` ON `$tb_privilege_roles`.`id` = `$tb_admin_role`.`role_id`
           INNER JOIN `$tb_privilege` ON `$tb_privilege_roles`.`id` = `$tb_privilege`.`role_id` AND `$tb_privilege`.`granted` = '1'
           INNER JOIN `$tb_privilege_methods` ON `$tb_privilege_methods`.`id` = `$tb_privilege`.`method_id`",
            "`$tb_privilege_methods`.`method`",
            "`$tb_admin_role`.`admin_id` = ? ",
            [$admin_id]
        );
        $final_array = array();
        foreach ($privileges as $privilege) {
            $final_array[] = $privilege['method'];
        }
        return $final_array;
    }

    /** deprecated */
    public function AllAllowedPages(int $admin_id): array
    {
        $tb_privilege_roles = PrivilegeRoles::obj()->TableName();
        $tb_privilege = Privileges::obj()->TableName();
        $tb_privilege_methods = PrivilegeMethods::obj()->TableName();
        $tb_admin_role = self::TABLE_NAME;
        $privileges = $this->Rows(
            "`$tb_privilege_roles` 
           INNER JOIN `$tb_admin_role` ON `$tb_privilege_roles`.`id` = `$tb_admin_role`.`role_id`
           INNER JOIN `$tb_privilege` ON `$tb_privilege_roles`.`id` = `$tb_privilege`.`role_id` AND `$tb_privilege`.`granted` = '1'
           INNER JOIN `$tb_privilege_methods` ON `$tb_privilege_methods`.`id` = `$tb_privilege`.`method_id`",
            "`$tb_privilege_methods`.`page`",
            "`$tb_admin_role`.`admin_id` = ? GROUP BY `$tb_privilege_methods`.`page`",
            [$admin_id]
        );
        $final_array = array();
        foreach ($privileges as $privilege) {
            $final_array[] = $privilege['page'];
        }
        return $final_array;
    }

    /** deprecated */
    private function CheckMyPrivilege(string $privilege_name, int $admin_id, int $is_admin): bool
    {
        $tb_privilege_roles = PrivilegeRoles::TABLE_NAME;
        $tb_privilege = Privileges::TABLE_NAME;
        $tb_privilege_methods = PrivilegeMethods::TABLE_NAME;
        $tb_admin_role = self::TABLE_NAME;
        if(self::IsMaster($admin_id)){
            return true;
        }
        elseif(!empty($is_admin)) {
            return true;
        }
        elseif($this->RowISExist("`$tb_privilege_roles` 
                               INNER JOIN `$tb_admin_role` ON `$tb_privilege_roles`.`role_id` = `$tb_admin_role`.`role_id`
                               INNER JOIN `$tb_privilege` ON `$tb_privilege_roles`.`role_id` = `$tb_privilege`.`role_id` AND `$tb_privilege`.`granted` = '1'
                               INNER JOIN `$tb_privilege_methods` ON `$tb_privilege_methods`.`method_id` = `$tb_privilege`.`method_id`",
            "`$tb_admin_role`.`admin_id` = ? AND `$tb_privilege_methods`.`page` = ? AND `$tb_privilege_methods`.`method` = ? ", [$admin_id, GeneralFunctions::CurrentPage(), $privilege_name])){
            return true;
        }
        return false;
    }

    /** deprecated */
    public function IsAllowedMethod(int $admin_id, int $is_admin): void
    {
        if(empty($_GET['action'])){
            Json::Missing('action', line: __LINE__);
        }
        if(!$this->IsAllowedMethodBool($admin_id, $is_admin)){
            Json::Forbidden();
        }
    }

    /** deprecated */
    public function IsAdminAllowedMethod(): void
    {
        if(empty($_GET['action'])){
            Json::Missing('action', line: __LINE__);
        }
        if(!$this->IsAllowedMethodBool(AdminLoginToken::obj()->GetAdminID(), AdminLoginToken::obj()->GetAdminIsAdmin())){
            Json::Forbidden();
        }
    }


    /** deprecated */
    public function IsAllowedMethodBooForMe(string $privilege_name): bool
    {
        if(!str_contains(strtolower($privilege_name), 'initialize') && !in_array($privilege_name, ['UserTitle','CustomerTitle'])) {
            if (! self::CheckMyPrivilege($privilege_name,
                AdminLoginToken::obj()->GetAdminID(),
                AdminLoginToken::obj()->GetAdminIsAdmin())) {
                return false;
            }
        }
        return true;
    }

    /** deprecated */
    public function IsAllowedMethodBool(int $admin_id, int $is_admin): bool
    {
        if(empty($_GET['action'])){
            return false;
        }else{
            if(!str_contains(strtolower($_GET['action']), 'initialize') &&
               !str_contains(strtolower(GeneralFunctions::CurrentPage()), 'initialize')) {
                if (! self::CheckMyPrivilege($_GET['action'], $admin_id, $is_admin)) {
                    return false;
                }
            }
        }
        return true;
    }

    /** deprecated */
    public function IsAllowedCustomerDownload(int $admin_id, int $is_admin): void
    {
        if(empty($_GET['action'])){
            Json::Missing('action', line: __LINE__);
        }
        $privilege_name = $_GET['action'];
        if(!self::CheckMyPrivilege($privilege_name, $admin_id, $is_admin)) {
            Json::Forbidden();
        }
    }

}