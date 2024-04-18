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

abstract class LoggerChangesHandler extends LoggerKeysHandler
{

    public function RecordLog(int $log_id, array $changes_col_from_to): void
    {
        foreach ($changes_col_from_to as $col_from_to) {
            [$col, $from, $to] = $col_from_to;
            $expect = [];
            if ($col == 'description') {
                $expect = ['col_change'];
            }
            $this->Add([
                'log_id'      => $log_id,
                'change_col'  => $col,
                'change_from' => $from,
                'change_to'   => $to,
            ], $expect);
        }
    }
}