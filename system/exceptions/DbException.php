<?php

namespace System\Exceptions;

class DbException extends \Exception
{
    public function __construct(string $message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
