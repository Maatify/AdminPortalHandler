<?php

/**
 * @PHP       Version >= 8.0
 * @copyright ©2024 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-07-20 11:07 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/AdminPortalHandler  view project on GitHub
 * @Maatify   DB :: AdminPortalHandler
 */


namespace Maatify\Portal\DbHandler;

use App\Assist\AppFunctions;
use App\DB\DBS\DbPortalHandler;
use Maatify\Json\Json;
use Maatify\Portal\Language\DbLanguage;

abstract class ParentLanguageSliderHandler extends DbPortalHandler
{
    protected string $logger_type = '';
    protected string $image_folder = 'category_slider';

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
        $file = UploaderWebPPortalHandler::obj($this->image_folder)
            ->ImageUpload($this->row_id,
                $this->identify_table_id_col_name . '-' . $this->language_short_name,
                $this->current_row['image']);
        if (! empty($file['image'])) {
            $old_file = ! empty($file['deleted']) ? AppFunctions::SiteImageURL() . $file['deleted'] : '';
            $new_file = AppFunctions::SiteImageURL() . $this->image_folder . '/' . $file['image'];
            $this->Edit([
                'image' => $this->image_folder . '/' . $file['image'],
            ], "`$this->identify_table_id_col_name` = ? ", [$this->row_id]);
            $log = $this->logger_keys = [
                'language'                                            => $this->language_short_name,
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