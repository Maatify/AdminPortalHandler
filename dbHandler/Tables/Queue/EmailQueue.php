<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2024-04-18
 * Time: 09:36:21
 * https://www.Maatify.dev
 */

namespace Maatify\Portal\Queue;

class EmailQueue extends Queue
{
    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function Email():void
    {
        $this->row_id = 1;
        $this->Start();
    }

}