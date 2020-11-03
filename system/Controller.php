<?php

namespace System;

class Controller
{

    protected Request $request;
    protected Db $db;
    protected Loader $load;

    public function __construct()
    {
    }

    public function init(Request &$request, Db &$db, Loader &$loader): Controller
    {
        $this->request = &$request;
        $this->db = &$db;
        $this->load = &$loader;
        return $this;
    }
}
