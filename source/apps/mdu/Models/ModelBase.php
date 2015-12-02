<?php

namespace Ucenter\Mdu\Models;

class ModelBase extends \Phalcon\Mvc\Model
{
    protected $di;
    protected $db;

    public function initialize()
    {
        $this->di = self::getDI();
        $this->db = $this->di['db'];
    }
}