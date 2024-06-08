<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2024-07-20
 * Time: 10:56 AM
 * https://www.Maatify.dev
 */

namespace Maatify\Portal\DbHandler;

use App\Assist\AppFunctions;
use Maatify\Json\Json;

abstract class AddRemoveTwoColsTypeNameHandler extends AddRemoveTwoColsHandler
{

    protected string $type_name = '';

    public function InitializeAddsToDestination(): void
    {
        $this->ValidatePostedDestination();
        [$inner_add, $cols_add] = $this->table_source_class::obj()->InnerLanguageNameTablesAndCols($this->table_source_name);
        $result = $this->Rows("`$this->table_source_name` 
        LEFT JOIN `$this->tableName` 
        ON `$this->tableName`.`$this->col_source_name` = `$this->table_source_name`.`$this->col_source_name` 
        AND `$this->tableName`.`$this->col_destination_name` = '$this->col_destination_val' " . $inner_add,
            "`$this->table_source_name`.* " . $cols_add,
            "`$this->table_source_name`.`type_name` = ? 
            AND `$this->tableName`.`$this->col_source_name` IS NULL 
            GROUP BY `$this->table_source_name`.`$this->col_source_name` ",
            [$this->type_name]);
        $this->JsonHandlerWithOther(
            AppFunctions::MapArrayImages($result)
            ,
            AppFunctions::MapRowImages($this->current_destination),
            line: $this->class_name . __LINE__
        );
    }

    public function InitializeAddsToSource(): void
    {
        $this->ValidatePostedSourceTypeId();
        parent::InitializeAddsToSource();
    }

    public function Assign(): void
    {
        $this->ValidatePostedSourceTypeId();
        parent::Assign();
    }

    public function UnAssign(): void
    {
        $this->ValidatePostedSourceTypeId();
        parent::UnAssign();
    }

    public function AssignedListBySource(string $order_by = ''): void
    {
        $this->ValidatePostedSourceTypeId();
        parent::AssignedListBySource($order_by);
    }

    public function ListBySource(): void
    {
        $this->ValidatePostedSourceTypeId();
        parent::ListBySource();
    }

    public function SwitchAssign(): void
    {
        $this->ValidatePostedSourceTypeId();
        parent::SwitchAssign();
    }

    private function ValidatePostedSourceTypeId(): void
    {
        $this->ValidatePostedSource();
        if(!empty($this->current_source['type_name']) && $this->current_source['type_name'] !== $this->type_name ){
            Json::NotAllowedToUse('type_name');
        }
    }

    public function ListByDestination(): void
    {
        $this->ValidatePostedDestination();
        [$inner_add, $cols_add] = $this->table_source_class::obj()->InnerLanguageNameTablesAndCols($this->table_source_class);
        $result = $this->Rows("`$this->table_source_name` 
            LEFT JOIN `$this->tableName` ON 
            `$this->tableName`.`$this->col_source_name` = `$this->table_source_name`.`$this->col_source_name` 
            AND `$this->tableName`.`$this->col_destination_name` = '$this->col_destination_val'
            " . $inner_add,
            "`$this->table_source_name`.*, `$this->tableName`.`$this->col_source_name` IS NOT NULL as assigned" . $cols_add,

            "`$this->table_source_name`.`type_name` = ? 
            GROUP BY `$this->table_source_name`.`$this->col_source_name` 
            ORDER BY `$this->table_source_name`.`$this->col_source_name` ASC",
            [$this->type_name]);
        $this->JsonHandlerWithOther(
            AppFunctions::MapArrayImages($result),
            AppFunctions::MapRowImages($this->current_destination)
        );
    }


}