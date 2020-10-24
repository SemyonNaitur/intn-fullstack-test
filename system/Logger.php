<?php
class Logger
{
    private static $time_rgx = '/^\[(.+?)\]/';

    public static function debug(string $msg)
    {
        static::log(__FUNCTION__, $msg);
    }

    public static function error(string $msg)
    {
        static::log(__FUNCTION__, $msg);
    }

    public static function log(string $level, string $msg)
    {
        $level = strtoupper($level);
        $format = trim(static::get_format(), "\n") . "\n";
        $line = str_ireplace(['{level}', '{message}'], [$level, $msg], $format);
        $dir = get_config('log_dir');
        try {
            $name = get_config('app_name') . '_log.txt';
            $file = "$dir/$name";
            $fh = fopen($file, 'a');
            fwrite($fh, $line);
            fclose($fh);
        } catch (Throwable $e) {
            if ($level == 'ERROR') {
                $line = preg_replace(self::$time_rgx, '[' . get_config('app_name') . ']', $line);
                error_log($line);
            }
            throw $e;
        }
    }

    protected static function get_format()
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
