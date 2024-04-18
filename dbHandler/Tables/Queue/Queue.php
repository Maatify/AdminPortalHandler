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

namespace Maatify\Portal\Queue;

use \App\DB\DBS\DbConnector;

abstract class Queue extends DbConnector
{
    protected string $tableName = 'queue';
    private int $time;
    private int $timeout = 59;

    public function __destruct()
    {
        $this->Stop();
    }

    private function Stop(): void
    {
        $this->time = time()-(60*60*24);
        $this->QueueAction();
    }

    protected function Start(): void
    {
        if($this->CurrentQueue() < time()-$this->timeout){
            $this->time = time();
            $this->QueueAction();
        }else{
            die();
            sleep(10);
            $this->Start();
        }
    }

    private function QueueAction(): void
    {
        $this->Edit(['timestamp'=>$this->time], '`id` = ?', [$this->row_id]);
    }
    private function CurrentQueue(): int
    {
        return (int)$this->ColThisTable('timestamp', '`id` = ?', [$this->row_id]);
    }
}