<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2023-10-25
 * Time: 2:58 PM
 * https://www.Maatify.dev
 */

namespace Maatify\Portal\DbHandler;

use Maatify\Json\Json;
use Maatify\Logger\Logger;
use Maatify\PostValidatorV2\PostValidatorV2;
use Maatify\Uploader\Images\UploadImageToWebP;
use ReflectionClass;

class UploaderWebPPortalHandler extends UploadImageToWebP
{
    protected string $site_image_folder = __DIR__ . "/../../../public_html/images/";

    protected int|string $uploaded_for_id;
    protected int|string $uploaded_target_folder = '';
    private static self $instance;
    protected string $class_name;

    protected string $type;
    const IMAGE_TYPES = ['app', 'agent', 'web', 'api'];


    public static function obj(string $uploaded_target_folder): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self($uploaded_target_folder);
        }

        return self::$instance;
    }

    public function __construct(string $uploaded_target_folder)
    {
        $this->uploaded_target_folder = $uploaded_target_folder;
        $this->upload_folder = $this->site_image_folder . $this->uploaded_target_folder;
        $this->class_name = (new ReflectionClass($this))->getShortName() . '::';
    }

    public function ValidatePostType(): string
    {
        $type = PostValidatorV2::obj()->Require('image_type', 'letters');
        if(!in_array($type, self::IMAGE_TYPES)){
            Json::Incorrect('image_type', 'Incorrect Image Type', $this->class_name . __LINE__);
        }else{
            $this->type = $type;
        }
        return $this->type;
    }

    public function GetType(): string
    {
        return $this->type;
    }

    public function IconUpload(int $id, string $file_name, string $current_file): array
    {
        $file_name = $file_name !== '' ? $file_name . '-' : '';
        $this->file_name = $id . '-'. 'icon-' . $file_name . time();
        return $this->UploadHandler($current_file);
    }

    public function ImageUpload(int $id, string $file_name, string $current_file): array
    {
        $this->file_name = $id . '-'.  ($file_name !== '' ? $file_name . '-' : '') . time();
        return $this->UploadHandler($current_file);
    }

    public function ImageTypeUpload(string $language_code, int $id, string $file_name, array $current): array
    {
        $this->ValidatePostType();
        $current_file = $current[$this->type.'_image'];
        $this->file_name = $id . '-'. $this->type . '-' . strtolower($language_code) . '-'. $file_name . '-' . time();
        return $this->UploadHandler($current_file);
    }

    private function UploadHandler(string $current_file): array
    {
        try {
            $uploaded = $this->Upload();
        }catch (\WebPConvert\Exceptions\InvalidInput\InvalidImageTypeException $exception){
            Logger::RecordLog($exception, 'WebPConverter');
            Json::Invalid('file', 'file not supported', $this->class_name . __LINE__);
        }

        if(empty($uploaded['uploaded']) && !empty($uploaded['description'])){
            Json::Invalid('file', $uploaded['description'], $this->class_name . __LINE__);
        }else {
            $uploaded['file'] = !empty($uploaded['file']) ? $this->uploaded_target_folder . '/' . $uploaded['file'] : '';

            $uploaded['deleted'] = (!empty($current_file) ? $this->DeleteOldFile($current_file) : '');
        }
        return $uploaded;
    }

    private function DeleteOldFile(string $current_file): string
    {
        if(!str_contains($current_file, 'not-found')){
            $parse = parse_url($current_file, PHP_URL_PATH);
            $extension = pathinfo($parse, PATHINFO_EXTENSION);
            $filename = pathinfo($parse, PATHINFO_FILENAME);
            $new_filename = $filename . '_deleted_' . date('YmdHis', time()) . '.' . $extension;
            $old_file = $this->site_image_folder . $current_file;
            $new_file = $this->site_image_folder . $this->uploaded_target_folder . '/deleted/' . $new_filename;
            if (file_exists($old_file) && !is_dir($old_file)) {
                if(!file_exists($this->site_image_folder . $this->uploaded_target_folder . '/deleted')){
                    mkdir($this->site_image_folder . $this->uploaded_target_folder . '/deleted');
                }
                rename($old_file,
                    $new_file);
                return $this->uploaded_target_folder . '/deleted/' . $new_filename;
            }
        }
        return '';
    }
}