<?php

namespace System\Core;

use System\Libraries\Db;

class Loader
{
    /**
     * Create a new instance even if an instance already exists.
     * The instance won't be cached.
     */
    const NEW_INSTANCE = 'new_instance';

    /**
     * Create a new instance even if an instance already exists.
     * The instance won't be cached.
     */
    const RETURN_VIEW = 'return_view';

    private $pools = [
        'dbs' => [],
        'models' => [],
        'libraries' => [],
    ];

    /**
     * Creates a Db instance, adds it to loaded dbs pool and returns the instance.
     * Excepts an optional config array to merge into the default config, if exists.
     * 
     * @param   string  $name
     * @param   array   $config
     * @param   array   $opts
     * @return  Db
     */
    public function db(string $name = 'default', array $config = [], array $opts = []): Db
    {
        $pool = 'dbs';
        $dbs = &$this->pools[$pool];

        if (!in_array(self::NEW_INSTANCE, $opts)) {
            if (isset($dbs[$name])) return $dbs[$name];
        }

        $defaults = get_config('db')[$name] ?? [];
        $config = array_merge($defaults, $config);
        if (!$config) throw new \Exception("No configuration for database: $name");

        $db = new Db($config);

        if (!in_array(self::NEW_INSTANCE, $opts)) {
            $dbs[$name] = $db;
        }
        return $db;
    }

    public function model(string $name, array $args = null, array $opts = []): Model
    {
        return $this->loadClass('models', $name, $args, $opts);
    }

    public function library(string $name, array $args = null, array $opts = [])
    {
        return $this->loadClass('libraries', $name, $args, $opts);
    }

    public function loadClass(string $pool, string $name, array $args = null, array $opts = [])
    {
        if (!isset($this->pools[$pool])) {
            throw new \Exception("Invalid class type: $pool");
        }
        $p = &$this->pools[$pool];

        if (!in_array(self::NEW_INSTANCE, $opts)) {
            if (isset($p[$name])) return $p[$name];
        }

        $cls = get_config("{$pool}_path") . '/' . trim($name, '/');
        $cls = str_replace('/', '\\', $cls);
        $instance = new $cls(...$args);

        if (!in_array(self::NEW_INSTANCE, $opts)) {
            $p[$name] = $instance;
        }
        return $instance;
    }

    /**
     * Extracts variables to be used in the view and loads the view.
     * If $return = true, the view is returned as a string, otherwise it is sent to output.
     * 
     * @param   string      $__name
     * @param   array       $__data
     * @param   array       $__opts
     * @return  string|void 
     */
    public function view(string $__name, array $__data = [], array $__opts = [])
    {
        $__file = sprintf('%s/%s/%s.php', ROOT_DIR, get_config('views_path'), trim($__name, '/'));
        if (!file_exists($__file)) {
            throw new \Exception("Failed to load view: $__file");
        }

        extract($__data);

        ob_start();
        include $__file;

        if (in_array(self::RETURN_VIEW, $__opts)) {
            $buffer = ob_get_contents();
            @ob_end_clean();
            return $buffer;
        }

        ob_end_flush();
    }
}
