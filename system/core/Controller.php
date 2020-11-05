<?php

namespace System\Core;

use System\Libraries\Db;

abstract class Controller
{

    protected Request $request;
    protected Loader $load;
    protected Db $db;

    public function __construct()
    {
    }

    public function init(
        Request &$request,
        Loader &$loader,
        ?Db &$db
    ): Controller {
        $this->request = &$request;
        $this->load = &$loader;
        $this->db = &$db;
        return $this;
    }
}
