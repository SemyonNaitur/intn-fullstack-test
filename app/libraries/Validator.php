<?php

namespace App\Libraries;

use System\Libraries\{Validator as SysValidator, Db};

class Validator extends SysValidator
{
    public function __construct(Db $db = null)
    {
        parent::__construct($db);
    }
}
