<?php

namespace System\Core;

interface ILogger
{
    public function debug(string $message): void;

    public function error(string $message): void;

    public function log(string $level, string $message): void;
}
