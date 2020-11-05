<?php

namespace System\Libraries;

class Logger
{
    private static $time_rgx = '/^\[(.+?)\]/';

    /**
     * 
     * @param   string $message
     * @return  void
     */
    public static function debug(string $message): void
    {
        static::log(__FUNCTION__, $message);
    }

    /**
     * 
     * @param   string $message
     * @return  void
     */
    public static function error(string $message): void
    {
        static::log(__FUNCTION__, $message);
    }

    /**
     * 
     * @param   string  $level
     * @param   string  $message
     * @return  void
     */
    public static function log(string $level, string $message): void
    {
        $level = strtoupper($level);
        $format = trim(static::getFormat(), "\n") . "\n";
        $line = strtr($format, ['{level}' => $level, '{message}' => $message]);
        $dir = get_config('log_dir');
        try {
            $name = get_config('app_name') . '_log.txt';
            $file = "$dir/$name";
            $fh = fopen($file, 'a');
            fwrite($fh, $line);
            fclose($fh);
        } catch (\Throwable $e) {
            if ($level == 'ERROR') {
                $line = preg_replace(self::$time_rgx, '[' . get_config('app_name') . ']', $line);
                error_log($line);
            }
            throw $e;
        }
    }

    protected static function getFormat(): string
    {
        $f = get_config('log_format');
        $m = null;
        if (preg_match(self::$time_rgx, $f, $m)) {
            $t = date($m[1]);
            $f = preg_replace(self::$time_rgx, "[$t]", $f);
        }
        return $f;
    }
}
