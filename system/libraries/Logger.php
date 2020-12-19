<?php

namespace System\Libraries;

use System\Core\Logger as ILogger;

class Logger implements ILogger
{
    private static $time_rgx = '/^\[(.+?)\]/';

    public function debug(string $message): void
    {
        $this->log(__FUNCTION__, $message);
    }

    public function error(string $message): void
    {
        $this->log(__FUNCTION__, $message);
    }

    public function log(string $level, string $message): void
    {
        $format = trim(static::getFormat(), "\n") . "\n";
        $line = strtr($format, ['{level}' => strtoupper($level), '{message}' => $message]);
        $dir = app_config('log_dir');
        try {
            $name = app_config('app_name') . '_log.txt';
            $file = "$dir/$name";
            $fh = fopen($file, 'a');
            fwrite($fh, $line);
            fclose($fh);
        } catch (\Throwable $e) {
            $line = preg_replace(self::$time_rgx, '[' . app_config('app_name') . ']', $line);
            error_log($line);
        }
    }

    protected static function getFormat(): string
    {
        $f = app_config('log_format');
        $m = null;
        if (preg_match(self::$time_rgx, $f, $m)) {
            $t = date($m[1]);
            $f = preg_replace(self::$time_rgx, "[$t]", $f);
        }
        return $f;
    }
}
