<?php

namespace System;

class Controller
{

    protected Request $request;
    protected DB $db;

    public function __construct()
    {
    }

    public function init(Request &$request, DB $db)
    {
        $this->request = &$request;
        $this->db = &$db;
        return $this;
    }
}
