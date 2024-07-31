<?php
/**
 * @PHP       Version >= 8.0
 * @Liberary  AdminPortalHandler
 * @Project   AdminPortalHandler
 * @copyright ©2024 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-07-20 11:07 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/AdminPortalHandler  view project on GitHub
 * @Maatify   AdminPortalHandler :: SubLanguageSliderHandler
 */

namespace Maatify\Portal\DbHandler;

use App\Assist\AppFunctions;
use Maatify\Json\Json;
use Maatify\Portal\Language\DbLanguage;
use Maatify\PostValidatorV2\ValidatorConstantsTypes;
use Maatify\PostValidatorV2\ValidatorConstantsValidators;

abstract class SubLanguageSliderHandler extends AddRemoveTwoColsHandler
{
    protected string $logger_type = '';
    protected string $image_folder = 'category_slider';

    public function __construct()
    {
        parent::__construct();
        $this->cols_to_add = [
            [$this->table_source_class::IDENTIFY_TABLE_ID_COL_NAME, ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Require],
            [DbLanguage::IDENTIFY_TABLE_ID_COL_NAME, ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Require],
            ['image_type', ValidatorConstantsTypes::Small_Letters, ValidatorConstantsValidators::Require],
            ['image', ValidatorConstantsTypes::String, ValidatorConstantsValidators::Optional],
            ['sort', ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
            ['status', ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
            ['start', ValidatorConstantsTypes::DateTime, ValidatorConstantsValidators::Optional],
            ['stop', ValidatorConstantsTypes::DateTime, ValidatorConstantsValidators::Optional],
            ['title', ValidatorConstantsTypes::Description, ValidatorConstantsValidators::Optional],
            ['sub_title', ValidatorConstantsTypes::Description, ValidatorConstantsValidators::Optional],
            ['slug_url', 'slug_url', ValidatorConstantsValidators::Optional],
        ];

        $this->cols_to_edit = [
            [$this->table_source_class::IDENTIFY_TABLE_ID_COL_NAME, ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
            [DbLanguage::IDENTIFY_TABLE_ID_COL_NAME, ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
            ['image_type', ValidatorConstantsTypes::Small_Letters, ValidatorConstantsValidators::Optional],
            ['sort', ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
            ['start', ValidatorConstantsTypes::DateTime, ValidatorConstantsValidators::Optional],
            ['stop', ValidatorConstantsTypes::DateTime, ValidatorConstantsValidators::Optional],
            ['title', ValidatorConstantsTypes::Description, ValidatorConstantsValidators::Optional],
            ['sub_title', ValidatorConstantsTypes::Description, ValidatorConstantsValidators::Optional],
            ['slug_url', 'slug_url', ValidatorConstantsValidators::Optional],
        ];

        $this->cols_to_filter = [
            [$this->identify_table_id_col_name, ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
            [$this->table_source_class::IDENTIFY_TABLE_ID_COL_NAME, ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
            [DbLanguage::IDENTIFY_TABLE_ID_COL_NAME, ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
            ['image_type', ValidatorConstantsTypes::Small_Letters, ValidatorConstantsValidators::Optional],
            ['title', ValidatorConstantsTypes::Description, ValidatorConstantsValidators::Optional],
            ['status', ValidatorConstantsTypes::Status, ValidatorConstantsValidators::Optional],
            ['sub_title', ValidatorConstantsTypes::Description, ValidatorConstantsValidators::Optional],
            ['slug_url', 'slug_url', ValidatorConstantsValidators::Optional],
        ];
    }

    public function Record(): void
    {
        $type = UploaderWebPPortalHandler::obj($this->image_folder)->ValidatePostType();
        parent::Record();
    }

    protected function ValidatePostedTableId(): int
    {
        $this->row_id = (int)$this->postValidator->Require($this->identify_table_id_col_name, 'int');
        if (! ($this->current_row = $this->RowThisTable('*', "`$this->identify_table_id_col_name` = ? ", [$this->row_id]))) {
            Json::Incorrect($this->identify_table_id_col_name, $this->identify_table_id_col_name . ' not found', $this->class_name . __LINE__);
        } else {
            $this->row_id = $this->current_row[$this->identify_table_id_col_name];
            $this->language_id = $this->current_row['language_id'];
            $this->ReInitiateLanguage($this->language_id);
        }
        return $this->row_id;
    }

    public function UploadImage(): void
    {
        $this->ValidatePostedTableId();
        $parent_row = $this->table_source_class::obj()->RowThisTableByID($this->current_row[$this->table_source_class::IDENTIFY_TABLE_ID_COL_NAME]);
        if(empty($parent_row[$this->table_source_class::IDENTIFY_TABLE_ID_COL_NAME])){
            Json::Incorrect($this->table_source_class::IDENTIFY_TABLE_ID_COL_NAME);
        }
        $file = UploaderWebPPortalHandler::obj($this->image_folder)
            ->ImageUpload($this->row_id,
                $parent_row[$this->table_source_class::IDENTIFY_TABLE_ID_COL_NAME] . '-' . $this->language_short_name,
                $this->current_row['image']);
        if (! empty($file['image'])) {
            $old_file = ! empty($file['deleted']) ? AppFunctions::SiteImageURL() . $file['deleted'] : '';
            $new_file = AppFunctions::SiteImageURL() . $this->image_folder . '/' . $file['image'];
            $this->Edit([
                'image' => $this->image_folder . '/' . $file['image'],
            ], "`$this->identify_table_id_col_name` = ? ", [$this->row_id]);
            $log = $this->logger_keys = [
                'language'                                            => $this->language_short_name,
                $this->table_source_class::IDENTIFY_TABLE_ID_COL_NAME => $this->current_row[$this->table_source_class::IDENTIFY_TABLE_ID_COL_NAME],
                $this->identify_table_id_col_name                     => $this->row_id,
                DbLanguage::IDENTIFY_TABLE_ID_COL_NAME                => $this->language_id,
            ];
            $log['image'] = ['from' => $old_file, 'to' => $new_file];
            $changes[] = ['image', $old_file, $new_file];
            $this->Logger($log, $changes, 'Upload');
            Json::Success(line: $this->class_name . __LINE__);
        }
    }
}