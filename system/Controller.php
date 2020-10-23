<?php

class Controller
{

    private Core $core;

    public function __construct()
    {
    }

    public function init(Core &$core)
    {
        $this->core = &$core;
        return $this;
    }
}
