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
 * @Maatify   AdminPortalHandler :: SubClassLanguageHandler
 */

namespace Maatify\Portal\DbHandler;
use App\DB\DBS\DbPortalHandler;
use Maatify\Json\Json;
use Maatify\Portal\Language\DbLanguage;
use Maatify\Portal\Language\LanguagePortal;
use Maatify\PostValidatorV2\ValidatorConstantsTypes;

abstract class SubClassLanguageHandler extends DbPortalHandler
{
    protected string $identify_table_id_col_name;
    protected string $logger_type = '';
    protected string $logger_sub_type = '';
    protected string $parent_class = '';

    protected string $parent_join_col = '';
    protected array $cols_to_add = [[ValidatorConstantsTypes::Name, ValidatorConstantsTypes::Name, '']];

    private function RecordEmptyName(int $language_id, int $parent_id): void
    {
        $array_to_add = [
            $this->identify_table_id_col_name => $parent_id,
            'language_id'                     => $language_id,
        ];
        if(!empty($this->cols_to_add)) {
            foreach ($this->cols_to_add as $col) {
                if(str_contains($col[0], 'image')){
                    $array_to_add[$col[0]] = 'image-not-found.png';
                }elseif ($col[0] == 'icon'){
                    $array_to_add[$col[0]] = 'icon-image-not-found.png';
                }else{
                    $array_to_add[$col[0]] = $col[2];
                }
            }
        }
        $this->Add($array_to_add);
    }

    public function RecordParentIDForAllLanguages(int $parent_id): void
    {
        foreach (LanguagePortal::obj()->Ids() as $language_id) {
            $this->RecordEmptyName($language_id, $parent_id);
        }
    }

    public function RecordLanguageForAllParentIds(int $language_id): void
    {
        if (! empty($ids = $this->parent_class::obj()->Ids())) {
            foreach ($ids as $id) {
                $this->RecordEmptyName($language_id, $id);
            }
        }
    }

    private function getTableAndColumnsWithShortLanguage(): array
    {
        $table = "`$this->tableName` " . DbLanguage::obj()->JOINTableAdd($this->tableName);
        $columns = DbLanguage::obj()->JoinShortName() . ", `$this->tableName`.*";
        return [$table, $columns];
    }

    protected function ValidateTableRow(): array
    {
        $this->PostedLanguageId();
        $this->row_id = $this->parent_class::obj()->ValidatePostedTableId();
        $whereCondition = "`$this->tableName`.`language_id` = ? AND `$this->tableName`.`$this->identify_table_id_col_name` = ?";
        [$table, $columns] = $this->getTableAndColumnsWithShortLanguage();

        return $this->RetrieveRowHavImageAndHtml($table, $columns, $whereCondition, [$this->language_id, $this->row_id]);
    }

    protected function ValidateTableRowWithoutReplacement(): array
    {
        $this->PostedLanguageId();
        $this->row_id = $this->parent_class::obj()->ValidatePostedTableId();
        $whereCondition = "`$this->tableName`.`language_id` = ? AND `$this->tableName`.`$this->identify_table_id_col_name` = ?";
        [$table, $columns] = $this->getTableAndColumnsWithShortLanguage();

        return $this->RetrieveRow($table, $columns, $whereCondition, [$this->language_id, $this->row_id]);
    }

    public function LanguageRow(): void
    {
        Json::Success($this->ValidateTableRow(), line: $this->class_name . __LINE__);
    }

    public function LanguageRows(): void
    {
        $this->row_id = $this->parent_class::obj()->ValidatePostedTableId();
        [$table, $columns] = $this->getTableAndColumnsWithShortLanguage();
        $whereCondition = "`$this->tableName`.`$this->identify_table_id_col_name` = ?";
        $result = $this->RetrieveRowsImageAndHtml($table, $columns, $whereCondition, [$this->row_id]);
        $source = $this->parent_class::obj()
            ->RetrieveRowsImageAndHtml($this->parent_class::TABLE_NAME,
                '*',
                '`' . $this->parent_class::IDENTIFY_TABLE_ID_COL_NAME . '` = ? ',
                [$this->row_id]);
        $this->JsonHandlerWithOther($result, $source, line: $this->class_name . __LINE__);
    }

    public function Update(): void
    {
        $row = $this->ValidateTableRow();
        $this->logger_keys = [
            'language'                        => $this->language_short_name,
            $this->identify_table_id_col_name => $this->row_id,
            'language_id'                     => $this->language_id,
        ];
        [$edits, $log_keys, $changes] = $this->AddEditValues($row);

        $log_keys = array_merge($this->logger_keys, $log_keys);
        if (empty($edits)) {
            Json::errorNoUpdate(line: $this->class_name . __LINE__);
        } else {
            $this->edit($edits, "`{$this->identify_table_id_col_name}` = ? AND `language_id` = ?", [$this->row_id, $this->language_id]);
            $this->Logger($log_keys, $changes, 'Update');
            Json::success(line: $this->class_name . __LINE__);
        }
    }

    public function UpdateSilent(): void
    {
        $row = $this->ValidateTableRow();
        $this->logger_keys = [
            'language'                        => $this->language_short_name,
            $this->identify_table_id_col_name => $this->row_id,
            'language_id'                     => $this->language_id,
        ];
        [$edits, $log_keys, $changes] = $this->AddEditValues($row);

        $log_keys = array_merge($this->logger_keys, $log_keys);
        if (!empty($edits)) {
            $this->edit($edits, "`{$this->identify_table_id_col_name}` = ? AND `language_id` = ?", [$this->row_id, $this->language_id]);
            $this->Logger($log_keys, $changes, 'Update');
        }
    }

    private function AllPagination(): void
    {
        [$table, $columns] = $this->getTableAndColumnsWithShortLanguage();
        $result = $this->PaginationRows($table, $columns, "`$this->identify_table_id_col_name` > ? 
        ORDER BY `$this->identify_table_id_col_name` DESC", [0]);
        Json::Success($this->PaginationHandler(
            $this->CountThisTableRows($this->identify_table_id_col_name),
            array_map(function (array $record) {
                $this->AddSiteImageUrls($record);
                $this->DecodeHtmlCols($record);
            }, $result)
        ),
            line: $this->class_name . __LINE__);
    }

    public function AllLanguagesPagination(): void
    {
        [$table, $columns] = $this->getTableAndColumnsWithShortLanguage();
        $parent_name = $this->parent_class::TABLE_NAME;
        $table_count = $table;
        if(!empty($this->parent_join_col)){
            $table .=  " INNER JOIN `$parent_name` ON `$parent_name`.`$this->identify_table_id_col_name` = `$this->tableName`.`$this->identify_table_id_col_name`";
            $columns .= ", `$parent_name`.`$this->parent_join_col` as detail";
        }
        $whereCondition = "`$this->tableName`.`$this->identify_table_id_col_name` > ? 
        ORDER BY `$this->tableName`.`$this->identify_table_id_col_name` DESC";
        $result = $this->PaginationHandler(
            $this->CountTableRows($table_count, $this->identify_table_id_col_name, $whereCondition, [0]),
            $this->PaginationRows($table, $columns, $whereCondition, [0]),
        );
        Json::Success($result);
    }

    public function duplicate(int $old_id, int $new_id): void
    {
        $records = $this->RowsThisTable('*', "`$this->identify_table_id_col_name` = ? ", [$old_id]);
        foreach ($records as $record) {
            if(isset($record[$this->identify_table_id_col_name])){
                $record[$this->identify_table_id_col_name]= $new_id;
                $this->Add($record);
            }
        }
    }

}