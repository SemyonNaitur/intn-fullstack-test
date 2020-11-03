<?php

namespace System;

class Controller
{

    protected Request $request;
    protected Db $db;

    public function __construct()
    {
    }

    public function init(Request &$request, Db $db): Controller
    {
        $this->request = &$request;
        $this->db = &$db;
        return $this;
    }
}
