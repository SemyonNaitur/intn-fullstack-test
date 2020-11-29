<?php

namespace System\Libraries\Api;

class ApiResponse
{
    public $data = null;

    public function __construct($status = '', $message = '')
    {
        $this->status = $status;
        $this->message = $message;
    }
}
