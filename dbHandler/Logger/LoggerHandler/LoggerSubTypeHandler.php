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

use \App\DB\DBS\DbLogger;
use Maatify\Json\Json;

abstract class LoggerSubTypeHandler extends DbLogger
{
    public function RecordType(string $type, string $sub_type): void
    {
        if(!$this->RowIsExistThisTable('`type` = ? AND `sub_type` = ?', [$type, $sub_type])){
            $this->Add(['type' => $type, 'sub_type' => $sub_type]);
        }
    }
    public function JsonAllOfType(): void
    {
        $type = $this->postValidator->Require('type', 'col_name');
        Json::Success($this->RowsThisTable('*', '`type` = ? ', [$type]), line: $this->class_name . __LINE__);
    }
}