<?php

namespace Ucenter\Mdu;

class ModuleBase
{
    protected $di;

    protected function initModel($model)
    {
        $modObj = new $model();
        $this->di = $modObj->getDI();
        return $modObj;
    }

    public function showMsg($msg)
    {
        echo $msg;
        exit();
    }
}
