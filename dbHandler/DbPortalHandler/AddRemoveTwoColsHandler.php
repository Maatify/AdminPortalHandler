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

namespace Maatify\Portal\DbHandler;

// Relation between Two table in this Table
use App\DB\DBS\DbPortalHandler;

abstract class AddRemoveTwoColsHandler extends DbPortalHandler
{
    protected string $logger_type = '';
    protected string $logger_sub_type = '';
    protected string $col_source_name;
    private int $col_source_val;
    protected string $table_source_name;
    protected string $table_source_class;
    private array $current_source;

    private string $col_destination_name;
    protected int $col_destination_val;

    private string $table_destination_name;
    protected string $table_destination_class;
    protected array $current_destination;
    protected array $current_row;
    private string $where = '';
    private array $where_vals = [];

    protected array $cols_to_add = [];
    protected array $cols_to_edit = [];
    protected array $cols_to_filter = [];

    public function __construct()
    {
        parent::__construct();
        $this->table_destination_name = $this->table_destination_class::TABLE_NAME;
        $this->col_destination_name = $this->table_destination_class::IDENTIFY_TABLE_ID_COL_NAME;
        $this->table_source_name = $this->table_source_class::TABLE_NAME;
        $this->col_source_name = $this->table_source_class::IDENTIFY_TABLE_ID_COL_NAME;
    }


    protected function ValidatePostedSource(): int
    {
        $this->col_source_val = (int)$this->postValidator->Require($this->col_source_name, 'int');
        if (! ($this->current_source = $this->RowWithNames($this->table_source_class, $this->col_source_name, $this->col_source_val))) {
            Json::Incorrect($this->col_source_name, $this->col_source_name . ' not found ', $this->class_name . __LINE__);
        }

        return $this->col_source_val;
    }

    protected function ValidateOptionalPostedSource(): int
    {
        if(isset($_POST[$this->col_source_name])){
            $this->ValidatePostedSource();
        }
        return 0;
    }

    protected function ValidatePostedSourceOtherPostedName(string $posted_col_name): int
    {
        $row_id = (int)$this->postValidator->Require($posted_col_name, 'int');
        if (! ($this->RowWithNames($this->table_source_class, $this->col_source_name, $row_id))) {
            Json::Incorrect($posted_col_name, "$posted_col_name Not Found", $this->class_name . __LINE__);
        }
        return $row_id;
    }

    protected function ValidatePostedDestination(): int
    {
        $this->col_destination_val = (int)$this->postValidator->Require($this->col_destination_name, 'int');
        if (! ($this->current_destination = $this->RowWithNames($this->table_destination_class, $this->col_destination_name, $this->col_destination_val))) {
            Json::Incorrect($this->col_destination_name, $this->col_destination_name . ' not found ', $this->class_name . __LINE__);
        }

        return $this->col_destination_val;
    }

    protected function ValidateOptionalPostedDestination(): int
    {
        if(isset($_POST[$this->col_destination_name])){
            $this->ValidatePostedDestination();
        }
        return 0;
    }

    private function RowWithNames($table, $col, $val): array
    {
        [$inner_add, $cols_add] = $table::obj()->InnerLanguageNameTablesAndCols();
        $tb_name = $table::TABLE_NAME;

        return $this->Row("`$tb_name` " . $inner_add,
            "`$tb_name`.* " . $cols_add,
            "`$tb_name`.`$col` = ? GROUP BY `$tb_name`.`$col`",
            [$val]);
    }

    protected function ValidateCurrentSourceAndDestination(): void
    {
        if (empty($this->where)) {
            $this->where = "`$this->tableName`.`$this->col_source_name` = ? AND `$this->tableName`.`$this->col_destination_name` = ? ";
            $this->where_vals = [$this->col_source_val, $this->col_destination_val];
        }
        $this->current_row = $this->Row("`$this->tableName` "
            ,
            "`$this->tableName`.* "
            ,
            $this->where,
            $this->where_vals);
    }

    public function InitializeAddsToSource(): void
    {
        $this->ValidatePostedSource();
        [$inner_add, $cols_add] = $this->table_destination_class::obj()->InnerLanguageNameTablesAndCols($this->table_destination_name);
        $this->JsonHandlerWithOther(
            $this->Rows("`$this->table_destination_name` 
        LEFT JOIN `$this->tableName` 
        ON `$this->tableName`.`$this->col_destination_name` = `$this->table_destination_name`.`$this->col_destination_name` 
        AND `$this->table_destination_name`.`$this->col_destination_name` = '$this->col_source_val' " . $inner_add,
                "`$this->table_destination_name`.* " . $cols_add,
                " `$this->tableName`.`$this->col_destination_name` IS NULL GROUP BY `$this->table_destination_name`.`$this->col_destination_name` "),
            $this->current_source,
            line: $this->class_name . __LINE__
        );
    }

    public function InitializeAddsToDestination(): void
    {
        $this->ValidatePostedDestination();
        [$inner_add, $cols_add] = $this->table_source_class::obj()->InnerLanguageNameTablesAndCols($this->table_source_name);
        $this->JsonHandlerWithOther(
            $this->Rows("`$this->table_source_name` 
        LEFT JOIN `$this->tableName` 
        ON `$this->tableName`.`$this->col_source_name` = `$this->table_source_name`.`$this->col_source_name` 
        AND `$this->tableName`.`$this->col_source_name` = '$this->col_destination_val' " . $inner_add,
                "`$this->table_source_name`.* " . $cols_add,
                "`$this->tableName`.`$this->col_source_name` IS NULL GROUP BY `$this->table_source_name`.`$this->col_source_name` "),
            $this->current_destination,
            line: $this->class_name . __LINE__
        );
    }

    public function SwitchAssign(): void
    {
        $this->ValidatePostedSource();
        $this->ValidatePostedDestination();
        $this->ValidateCurrentSourceAndDestination();
        $logger = $this->logger_keys = [$this->col_source_name => $this->col_source_val, $this->col_destination_name => $this->col_destination_val];
        $this->row_id = $this->col_source_val;
        $logger[$this->col_source_name] = $this->col_source_val;
        $logger[$this->col_destination_name] = $this->col_destination_val;
        if (empty($this->current_row)) {
            $action = 'Assign';
            $array_to_add = [
                $this->col_source_name      => $this->col_source_val,
                $this->col_destination_name => $this->col_destination_val,
            ];

            if(!empty($this->cols_to_add)) {
                foreach ($this->cols_to_add as $col) {
                    if(str_contains($col[0], 'image')){
                        $array_to_add[$col[0]] = 'image-not-found.png';
                    }elseif ($col[0] == 'icon'){
                        $array_to_add[$col[0]] = 'icon-image-not-found.png';
                    }else{
                        if($col[0] != $this->col_source_name && $col[0] != $this->col_destination_name  ) {
                            $array_to_add[$col[0]] = $col[2];
                        }
                    }
                }
            }

            $this->Add($array_to_add);
            $changes = [
                [
                    'assign',
                    '',
                    'Assign',
                ],
                [
                    $this->col_source_name,
                    '',
                    $this->col_source_val,

                ],
                [
                    $this->col_destination_name,
                    '',
                    $this->col_destination_val,
                ],
            ];
        } else {
            $action = 'UnAssign';
            $this->Delete("`$this->col_source_name` = ? AND `$this->col_destination_name` = ? ", [$this->col_source_val, $this->col_destination_val]);
            $changes = [
                [
                    'assign',
                    '',
                    'UnAssign',
                ],
                [
                    $this->col_source_name,
                    $this->col_source_val,
                    '',
                ],
                [
                    $this->col_destination_name,
                    $this->col_destination_val,
                    '',
                ],
            ];
        }
        $this->Logger($logger, $changes, $action);
        Json::Success(line: $this->class_name . __LINE__);
    }

    public function AssignSilent(): void
    {
        $this->ValidatePostedSource();
        $this->ValidatePostedDestination();
        $this->ValidateCurrentSourceAndDestination();
        if (! empty($this->current_row)) {
            Json::Exist($this->col_source_name, $this->col_source_name . ' & ' . $this->col_destination_name . ' Already Exist', $this->class_name . __LINE__);
        } else {
            $this->Add([
                $this->col_source_name      => $this->col_source_val,
                $this->col_destination_name => $this->col_destination_val,
            ]);
            $logger = $this->logger_keys = [$this->col_source_name => $this->col_source_val, $this->col_destination_name => $this->col_destination_val];
            $logger[$this->col_source_name] = $this->col_source_val;
            $logger[$this->col_destination_name] = $this->col_destination_val;
            $changes = [
                [
                    'assign',
                    '',
                    'Assign',
                ],
                [
                    $this->col_source_name,
                    '',
                    $this->col_source_val,

                ],
                [
                    $this->col_destination_name,
                    '',
                    $this->col_destination_val,
                ],
            ];
            $this->Logger($logger, $changes, 'Assign');
        }
    }

    public function Assign(): void
    {
        $this->AssignSilent();
        Json::Success(line: $this->class_name . __LINE__);
    }

    public function UnAssignSilent(): void
    {
        $this->ValidatePostedSource();
        $this->ValidatePostedDestination();
        $this->ValidateCurrentSourceAndDestination();
        if (empty($this->current_row)) {
            Json::NotExist($this->col_source_name, $this->col_source_name . ' & ' . $this->col_destination_name . ' Not Exist', $this->class_name . __LINE__);
        } else {
            $this->Delete("`$this->col_source_name` = ? AND `$this->col_destination_name` = ? ", [$this->col_source_val, $this->col_destination_val]);
            $logger = $this->logger_keys = [$this->col_source_name => $this->col_source_val, $this->col_destination_name => $this->col_destination_val];
            $logger[$this->col_source_name] = $this->col_source_val;
            $logger[$this->col_destination_name] = $this->col_destination_val;
            $changes = [
                [
                    'assign',
                    '',
                    'UnAssign',
                ],
                [
                    $this->col_source_name,
                    $this->col_source_val,
                    '',
                ],
                [
                    $this->col_destination_name,
                    $this->col_destination_val,
                    '',
                ],
            ];
            $this->Logger($logger, $changes, 'UnAssign');
        }
    }

    public function UnAssign(): void
    {
        $this->UnAssignSilent();
        Json::Success(line: $this->class_name . __LINE__);
    }

    public function AssignedListBySource(string $order_by = ''): void
    {
        $this->ValidatePostedSource();
        $this->AssignedListByHandler($this->table_destination_class, $this->col_destination_name, $this->col_source_name, $this->col_source_val, $this->current_source, $order_by);
    }

    public function AssignedListByDestination(): void
    {
        $this->ValidatePostedDestination();
        $this->AssignedListByHandler($this->table_source_class, $this->col_source_name, $this->col_destination_name, $this->col_destination_val, $this->current_destination);
    }

    private function AssignedListByHandler($otherTable, $other_col_name, $this_col_name, $this_col_val, array $current, string $order_by = ''): void
    {
        $otherTableName = $otherTable::TABLE_NAME;
        if(empty($order_by)){
           $order_by = "`$otherTableName`.`$other_col_name` ASC";
        }

        [$inner_add, $cols_add] = $otherTable::obj()->InnerLanguageNameTablesAndCols($otherTable);
        $this->JsonHandlerWithOther(
            $this->Rows("`$otherTableName` 
            INNER JOIN `$this->tableName` ON 
            `$this->tableName`.`$other_col_name` = `$otherTableName`.`$other_col_name` 
            AND `$this->tableName`.`$this_col_name` = '$this_col_val'
            " . $inner_add,
                "`$otherTableName`.*, `$this->tableName`.`$this_col_name` IS NOT NULL as assigned" . $cols_add . ", $this->tableName.*",
                "`$otherTableName`.`$other_col_name` > ? 
            GROUP BY `$otherTableName`.`$other_col_name` 
            ORDER BY $order_by",
                [0]),
            $current
        );
    }

    public function ListByDestination(): void
    {
        $this->ValidatePostedDestination();
        $this->ListByHandler($this->table_source_class, $this->col_source_name, $this->col_destination_name, $this->col_destination_val, $this->current_destination);
    }

    public function ListBySource(): void
    {
        $this->ValidatePostedSource();
        $this->ListByHandler($this->table_destination_class, $this->col_destination_name, $this->col_source_name, $this->col_source_val, $this->current_source);
    }

    private function ListByHandler($otherTable, $other_col_name, $this_col_name, $this_col_val, array $current): void
    {
        $otherTableName = $otherTable::TABLE_NAME;
        [$inner_add, $cols_add] = $otherTable::obj()->InnerLanguageNameTablesAndCols($otherTable);

        $result = $this->Rows("`$otherTableName` 
            LEFT JOIN `$this->tableName` ON 
            `$this->tableName`.`$other_col_name` = `$otherTableName`.`$other_col_name` 
            AND `$this->tableName`.`$this_col_name` = '$this_col_val'
            " . $inner_add,
            "`$otherTableName`.*, `$this->tableName`.`$this_col_name` IS NOT NULL as assigned" . $cols_add,
            "`$otherTableName`.`$other_col_name` > ? 
            GROUP BY `$otherTableName`.`$other_col_name` 
            ORDER BY `$otherTableName`.`$other_col_name` ASC",
            [0]);

        array_map(function (array $record) {
            $record = $this->AddSiteImageUrls($record);

            return $this->DecodeHtmlCols($record);
        }, $result);

        $this->JsonHandlerWithOther(
            $result,
            $current
        );


    }

    public function AssignSourceFromOtherSourceId(): void
    {
        $this->row_id = $this->ValidatePostedSource();
        $for_id = $this->ValidatePostedSourceOtherPostedName('for_id');
        if($this->row_id == $for_id){
            Json::NotAllowedToUse('for_id', 'Cannot use Same Values', $this->class_name . __LINE__);
        }
        if ($list = array_column($this->RowsThisTable("`$this->col_destination_name`", "`$this->col_source_name` = ? ", [$this->row_id]), $this->col_destination_name)) {
            $this->logger_keys['for_id'] = $for_id;
            $this->logger_keys[$this->col_source_name] = $this->row_id;
            $changes = array();
            $log = array();
            foreach ($list as $item) {
                if(!$this->RowIsExistThisTable("`$this->col_source_name` = ? AND `$this->col_destination_name` = ? ", [$for_id, $item])) {
                    $this->Add([
                        $this->col_source_name      => $for_id,
                        $this->col_destination_name => $item,
                    ]);
                    $changes[] = [$this->col_destination_name, 'cloned from ' . $this->col_source_name . ': '.$this->row_id, $item];
                    $log[]['clone'] = [$this->col_source_name =>$item, 'from'=>$this->row_id, 'to'=>$for_id];
                }
            }
            if(!empty($changes)){
                $this->row_id = $for_id;
                $this->Logger($log, $changes, 'Clone');
            }
        }
        Json::Success(line: $this->class_name . __LINE__);
    }



    public function UpdateBySourceAndDestination(): void
    {
        $this->ValidateEditBySourceAndDestination();
        [$edits, $log_keys, $changes] = $this->AddEditValues($this->current_row);

        foreach ([$this->col_destination_name, $this->col_source_name] as $item){
            if(isset($edits[$item])){
                unset($edits[$item]);
            }
            if(isset($changes[$item])){
                unset($changes[$item]);
            }
            if(isset($log_keys[$item])){
                unset($log_keys[$item]);
            }
        }
        $log_keys = array_merge($this->logger_keys, $log_keys);
        if (empty($edits)) {
            Json::errorNoUpdate(line: $this->class_name . __LINE__);
        } else {
            $this->edit($edits, "`$this->col_source_name` = ? AND `$this->col_destination_name` = ? ", [$this->col_source_val, $this->col_destination_val]);
            $this->Logger($log_keys, $changes, 'Update');
            Json::success(line: $this->class_name . __LINE__);
        }
    }

    public function SwitchKeyBySourceAndDestination(string $key = 'status'): void
    {
        $this->SwitchKeyBySourceAndDestinationSilent($key);
        Json::Success(line: $this->class_name . 'Switch::' . __LINE__);
    }

    public function SwitchKeyBySourceAndDestinationSilent(string $key = 'status'): void
    {
        $this->ValidateEditBySourceAndDestination();
        if (isset($this->current_row[$key])) {
            $edits[$key] = (int)! $this->current_row[$key];
            $from = GeneralFunctions::Bool2String($this->current_row[$key]);
            $to = GeneralFunctions::Bool2String($edits[$key]);
            $logger[$key] = ['from' => $from, 'to' => $to];
            $changes[] = [$key, $from, $to];
            $log_keys = array_merge($this->logger_keys, $logger);
            $this->Edit($edits, "`$this->col_source_name` = ? AND `$this->col_destination_name` = ? ", [$this->col_source_val, $this->col_destination_val]);
            $this->Logger($log_keys, $changes, $_GET['action'] ?? 'Switch ' . $key);
        } else {
            Json::Invalid($key, 'there is no key with name ' . $key, $this->class_name . __LINE__);
        }
    }

    private function ValidateEditBySourceAndDestination(): void
    {
        $this->ValidatePostedSource();
        $this->ValidatePostedDestination();
        $this->ValidateCurrentSourceAndDestination();
        $this->row_id = $this->col_source_val;
        if (empty($this->current_row)) {
            Json::Invalid($this->col_source_name,  " $this->col_source_name & $this->col_destination_name Not Found in records", $this->class_name . __LINE__);
        }
        $this->logger_keys = [
            $this->col_source_name => $this->col_source_val, $this->col_destination_name => $this->col_destination_val,
        ];
    }

}