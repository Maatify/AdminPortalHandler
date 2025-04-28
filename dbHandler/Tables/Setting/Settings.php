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
 * @Maatify   AdminPortalHandler :: Settings
 */

namespace Maatify\Portal\Setting;

use \App\DB\DBS\DbPortalHandler;
use JetBrains\PhpStorm\NoReturn;
use Maatify\Functions\GeneralFunctions;
use Maatify\Json\Json;

class Settings extends DbPortalHandler
{
    public const IDENTIFY_TABLE_ID_COL_NAME = 'setting_id';
    public const TABLE_NAME                 = 'settings';
    public const TABLE_ALIAS                = '';
    public const LOGGER_TYPE                = self::TABLE_NAME;
    public const LOGGER_SUB_TYPE            = '';
    public const COLS                       =
        [
            self::IDENTIFY_TABLE_ID_COL_NAME => 1,
            'type'                           => 0,
            'isEditable'                     => 1,
            'default_key'                    => 0,
            'default_value'                  => 0,
            'comment'                        => 0,
        ];

    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $tableName = self::TABLE_NAME;
    protected string $tableAlias = self::TABLE_ALIAS;
    protected string $logger_type = self::LOGGER_TYPE;
    protected string $logger_sub_type = self::LOGGER_SUB_TYPE;
    protected array $cols = self::COLS;
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    #[NoReturn] public function JsonAll(): void
    {
        Json::Success($this->RowsThisTable());
    }

    #[NoReturn] public function Update(): void
    {
        $this->ValidatePostedTableId();
        if (empty($this->current_row['isEditable'])) {
            Json::NotAllowedToUse($this->identify_table_id_col_name, $this->identify_table_id_col_name . ' Not Editable', $this->class_name . __LINE__);
        } else {
            $default_value = match ($this->current_row['type']) {
                'number' => (int)$this->postValidator->RequireAcceptEmpty('default_value', 'int'),
                'date' => (int)$this->postValidator->RequireAcceptEmpty('default_value', 'date'),
                'datetime' => (int)$this->postValidator->RequireAcceptEmpty('default_value', 'datetime'),
                'bool' => (bool)$this->postValidator->RequireAcceptEmpty('default_value', 'bool'),
                default => (int)$this->postValidator->RequireAcceptEmpty('default_value', 'string'),
            };
            if ($default_value == $this->current_row['default_value']) {
                Json::ErrorNoUpdate($this->class_name . __LINE__);
            } else {
                $this->Edit(['default_value' => $default_value], '`id` = ? ', [$this->row_id]);
                $from = $this->current_row['type'] == 'bool' ? GeneralFunctions::Bool2String($this->current_row['default_value']) : $this->current_row['default_value'];
                $to = $this->current_row['type'] == 'bool' ? GeneralFunctions::Bool2String($default_value) : $default_value;
                $logger[self::IDENTIFY_TABLE_ID_COL_NAME] = $this->row_id;
                $this->logger_keys[self::IDENTIFY_TABLE_ID_COL_NAME] = $this->row_id;
                $changes[] = [
                    $this->current_row['default_key'],
                    $from,
                    $to,
                ];
                $logger[$this->current_row['default_key']] = ['from' => $from, 'to' => $to];
                $this->Logger($logger,
                    $changes,
                    'Update'
                );

                Json::Success();
            }
        }
    }

    private string $default_currency = '';
    public function DefaultCurrency(): string
    {
        if(empty($this->default_currency)){
            $this->default_currency = $this->ReturnColValue('default_currency');
        }
        return $this->default_currency;
    }

    protected function ReturnColValue(string $default_key): string
    {
        return $this->ColThisTable('default_value', ' `default_key` = ? ', [$default_key]);
    }

    public function ReturnColValueByID(int|string $setting_id): string
    {
        return $this->ColThisTable('default_value', ' `setting_id` = ? ', [$setting_id]);
    }

    private string $telegram_admin_authorization = '';
    public function TelegramAdminAuthorization(): string
    {
        if(empty($this->telegram_admin_authorization)){
            $this->telegram_admin_authorization = $this->ReturnColValue('telegram_admin_authorization');
        }
        return $this->telegram_admin_authorization;
    }

    private string $telegram_customer_authorization = '';
    public function TelegramCustomerAuthorization(): string
    {
        if(empty($this->telegram_customer_authorization)){
            $this->telegram_customer_authorization = $this->ReturnColValue('telegram_customer_authorization');
        }
        return $this->telegram_customer_authorization;
    }

    public function getKeysAsArray(array $keys_to_select):array
    {
        // Define the keys you are interested in
//        $keys_to_select = ['maintenance_mode', 'game_categories_min', 'game_categories_max'];

        // Create placeholders for the prepared statement
        $placeholders = implode(',', array_fill(0, count($keys_to_select), '?'));

        $result = $this->RowsThisTable('`default_key`, `default_value`', " `default_key` IN ($placeholders)", $keys_to_select);

        // Initialize the result array
        $final_result = [];

        if($result) {
            foreach ($result as $row) {
                // Fetch the results and build the associative array
                $final_result[$row['default_key']] = $row['default_value'];
            }
        }

        return $final_result;
    }

}