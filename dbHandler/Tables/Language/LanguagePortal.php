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
 * @Maatify   AdminPortalHandler :: LanguagePortal
 */

namespace Maatify\Portal\Language;

use App\Assist\AppFunctions;
use Maatify\Json\Json;
use Maatify\Logger\Logger;
use Maatify\Portal\DbHandler\UploaderWebPPortalHandler;

class LanguagePortal extends DbLanguage
{
    private static self $instance;
    private array $languages;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private array $ids = [];
    private array $id_name_code = [];

    public function __construct()
    {
        parent::__construct();
        $this->languages = $this->RowsThisTable();
        foreach ($this->languages as $language) {
            $this->ids[] = $language['language_id'];
            $this->id_name_code[] = [$language['language_id'], $language['name'], $language['code']];
        }
    }

    public function Ids(): array
    {
        return $this->ids;
    }

    public function IdNameCode(): array
    {
        return $this->id_name_code;
    }

    protected function CheckColExist(string $col, string $val): bool
    {
        return $this->RowIsExistThisTable("`$col` = ? ", [$val]);
    }

    public function List(): void
    {
        $result = $this->RowsThisTable();

        $result = array_map(function (array $record) {
            if (! empty($record['image'])) {
                $record['image'] = AppFunctions::SiteImageURL() . $record['image'];
            }

            return $record;
        }, $result);

        $this->JsonData(
            $result,
            line: $this->class_name . __LINE__
        );
    }

    public function Update(): void
    {
        $this->PostedLanguageId();
        $this->row_id = $this->language_id;
        $name = $this->postValidator->Optional('name', 'name');
        $short_name = $this->postValidator->Optional('short_name', 'letters');
        $code = $this->postValidator->Optional('code', 'letters');
        $locale = $this->postValidator->Optional('locale', 'string');
        $directory = $this->postValidator->Optional('directory', 'string');
        $sort = $this->postValidator->Optional('sort', 'int');
        $current = $this->RowByID();
        $edits = array();
        $changes = array();
        $this->logger_keys[self::IDENTIFY_TABLE_ID_COL_NAME] = $this->language_id;
        $log[self::IDENTIFY_TABLE_ID_COL_NAME] = $this->language_id;
        if (isset($_POST['name']) && $name != $current['name']) {
            if ($this->CheckColExist('name', $name)) {
                Json::Exist('name', 'Name ' . $name . ' Already Exist', $this->class_name . __LINE__);
            } else {
                $edits['name'] = $name;
                $log['name'] = ['from' => $current['name'], 'to' => $name];
                $changes[] = ['name', $current['name'], $name];
            }
        }
        if (isset($_POST['short_name']) && $short_name != $current['short_name']) {
            if ($this->CheckColExist('short_name', $short_name)) {
                Json::Exist('short_name', 'Short Name ' . $short_name . ' Already Exist', $this->class_name . __LINE__);
            } else {
                $edits['short_name'] = $short_name;
                $changes[] = ['short_name', $current['short_name'], $short_name];
                $log['short_name'] = ['from' => $current['short_name'], 'to' => $short_name];
            }
        }
        if (isset($_POST['code']) && $code != $current['code']) {
            if ($this->CheckColExist('code', $code)) {
                Json::Exist('code', 'Code ' . $code . ' Already Exist', $this->class_name . __LINE__);
            } else {
                $edits['code'] = $code;
                $changes[] = ['code', $current['code'], $code];
                $log['code'] = ['from' => $current['code'], 'to' => $code];
            }
        }
        if (isset($_POST['locale']) && $locale != $current['locale']) {
            if ($this->CheckColExist('locale', $locale)) {
                Json::Exist('locale', 'locale ' . $locale . ' Already Exist', $this->class_name . __LINE__);
            } else {
                $edits['locale'] = $locale;
                $changes[] = ['locale', $current['locale'], $locale];
                $log['locale'] = ['from' => $current['locale'], 'to' => $locale];
            }
        }
        if (isset($_POST['directory']) && $directory != $current['directory']) {
            $edits['directory'] = $directory;
            $changes[] = ['directory', $current['directory'], $directory];
            $log['directory'] = ['from' => $current['directory'], 'to' => $directory];
        }
        if (isset($_POST['sort']) && $sort != $current['sort']) {
            $edits['sort'] = $sort;
            $changes[] = ['sort', $current['sort'], $sort];
            $log['sort'] = ['from' => $current['sort'], 'to' => $sort];
        }
        if (empty($edits)) {
            Json::ErrorNoUpdate($this->class_name . __LINE__);
        } else {
            $this->Edit($edits, '`language_id` = ?', [$this->language_id]);
            $this->Logger($log, $changes, 'Update');
        }
        Json::Success(line: $this->class_name . __LINE__);
    }



    public function UploadImage(): void
    {
        $this->row_id = $this->ValidatePostedTableId();
        $file = UploaderWebPPortalHandler::obj($this->image_folder)->IconUpload($this->row_id, $this->current_row['short_name'], $this->current_row['image']);
        if (! empty($file['image'])) {
            $log = $this->logger_keys = [self::IDENTIFY_TABLE_ID_COL_NAME => $this->row_id];
            $old_file = ! empty($file['deleted']) ? AppFunctions::SiteImageURL() . $file['deleted'] : '';
            $new_file = AppFunctions::SiteImageURL() . $this->image_folder . '/' . $file['image'];
            $log['image'] = ['from' => $old_file, 'to' => $new_file];
            $changes[] = [
                'image',
                $old_file,
                $new_file,
            ];
            $this->Edit([
                'image' => $this->image_folder . '/' . $file['image'],
            ], '`language_id` = ? ', [$this->row_id]);
//            Logger::RecordLog($changes);
            $this->Logger($log, $changes, 'UploadImage');
            Json::Success(line: $this->class_name . __LINE__);
        }
    }
}