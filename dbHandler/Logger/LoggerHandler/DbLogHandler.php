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

namespace Maatify\Portal\Logger\LoggerHandler;

use \App\Assist\AppFunctions;
use \App\DB\DBS\DbLogger;
use Maatify\Json\Json;
use Maatify\Portal\Admin\AdminLoginToken;

abstract class DbLogHandler extends DbLogger
{
    private string $where;
    private array $where_val = [];
    private string $type = '';
    private string $sub_type;
//    protected LoggerKeys|LoggerKeysAdmin|LoggerKeysCustomer $logg_keys;
    protected LoggerKeysHandler $logg_keys;
    protected LoggerTypeHandler $log_type;
    protected LoggerSubTypeHandler $log_sub_type;

    protected LoggerChangesHandler $log_changes;


    public function RecordLog(string $logger_type, string $logger_sub_type, int $for_id, string $action, string|array $description, array $changes, array $keys = []): void
    {
        $this->row_id = $this->Add([
            'type'        => $logger_type,
            'sub_type'    => $logger_sub_type,
            'action'      => $action,
            'for_id'      => $for_id,
            'description' => is_array($description) ? json_encode($description, true) : $description,
            'admin_id'    => AdminLoginToken::obj()->GetAdminID(),
            'time'        => AppFunctions::CurrentDateTime(),
            'ip'          => AppFunctions::IP(),
            'data'        => is_array($description) ? json_encode($description, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_UNESCAPED_SLASHES) : $description,
        ], ['description', 'data']);
        if (! empty($keys)) {
            $this->logg_keys->RecordKeys($this->row_id, $keys);
        }
        $this->log_changes->RecordLog($this->row_id, $changes);
        $this->log_type->RecordType($logger_type);
        $this->log_sub_type->RecordType($logger_type, $logger_sub_type);
    }

    private function Prepare(): void
    {
        $id = (int)$this->postValidator->Optional('id', 'int');
        $admin_id = (int)$this->postValidator->Optional('user_id', 'int');
        $ip = $this->postValidator->Optional('ip', 'ip');
        $this->type = $this->postValidator->Optional('type', 'string');
        $this->sub_type = $this->postValidator->Optional('sub_type', 'string');

        $this->where = "`$this->tableName`.`id` > ?";
        $this->where_val[] = 0;

        if (! empty($id)) {
            $this->where .= " AND `$this->tableName`.`for_id` = ? ";
            $this->where_val[] = $id;
        }

        if (! empty($admin_id)) {
            $this->where .= " AND `$this->tableName`.`admin_id` = ? ";
            $this->where_val[] = $admin_id;
        }

        if (! empty($ip)) {
            $this->where .= " AND `$this->tableName`.`ip` = ? ";
            $this->where_val[] = $ip;
        }
    }

    public function ViewLogs(): void
    {
        $this->Prepare();
        $this->ViewLog();
    }

    public function ViewLogsByType(string $type = '', string $sub_type = '', array $where_key_vals = []): void
    {
        $lk = $this->logg_keys->TableName();
        $this->Prepare();
        if (! empty($type)) {
            $this->type = $type;
        }
        if (! empty($sub_type)) {
            $this->sub_type = $sub_type;
        }

        if (! empty($where_key_vals)) {
            $this->where .= " AND (";

            foreach ($where_key_vals as $key => $val) {
                $this->where .= "(`$lk`.`for_key` = ? AND `$lk`.`key_value` = ?) AND ";
                $this->where_val[] = $key;
                $this->where_val[] = $val;
            }

            $this->where = rtrim($this->where, "AND ");
            $this->where .= ") ";
        }

        if ($keys = $this->postValidator->Optional('filter', 'json')) {
            $keys = json_decode($this->HtmlDecode($keys), true);

            if (! empty($keys)) {
                $this->where .= " AND (";

                foreach ($keys as $key => $val) {
                    $this->where .= "(`$lk`.`for_key` = ? AND `$lk`.`key_value` = ?) OR ";
                    $this->where_val[] = $key;
                    $this->where_val[] = (int)$val;
                }

                $this->where = rtrim($this->where, "OR ");
                $this->where .= ") ";
            }
        }
        $this->ViewLog();
    }


    private function ViewLog(): void
    {
        if (! empty($this->type)) {
            $this->where .= " AND `$this->tableName`.`type` = ? ";
            $this->where_val[] = $this->type;
        }
        if (! empty($this->sub_type)) {
            $this->where .= " AND `$this->tableName`.`sub_type` = ? ";
            $this->where_val[] = $this->sub_type;
        }

        $lk = $this->logg_keys->TableName();
        $tables = "`$this->tableName` LEFT JOIN `$lk` ON `$lk`.`log_id` = `$this->tableName`.`id`";
        //        Logger::RecordLog();

        Json::Success(
            $this->PaginationHandler(
                $this->CountTableRows($tables, "`$this->tableName`.`id`", $this->where, $this->where_val),
                $this->PaginationRows(
                    $tables,
                    "`$this->tableName`.*, 
                    IFNULL(group_concat(`$lk`.`for_key`), '') as field_keys, 
                    IFNULL(group_concat(`$lk`.`key_value`), '') as field_values",
                    $this->where . " GROUP BY `$this->tableName`.`id` ORDER BY `$this->tableName`.`id` DESC ",
                    $this->where_val
                )
            )
        );
    }
}