<?php

namespace System;

class Loader
{

    private $pool = [
        'db' => [],
        'model' => [],
        'library' => [],
    ];

    /**
     * Creates a Db instance, adds it to loaded dbs pool and returns the instance.
     * Excepts an optional config array to merge into the default config, if exists.
     * 
     * @param   string  $name
     * @param   array   $config
     * @return  Db
     */
    public function db(string $name, array $config = []): Db
    {
        if (isset($this->pool['db'][$name])) {
            return $this->pool['db'][$name];
        }
        $defaults = get_config('db')[$name] ?? [];
        $config = array_merge($defaults, $config);
        if (!$config) throw new \Exception("No configuration for database: $name");
        $db = $this->pool['db'][$name] = new Db($config);
        return $db;
    }

    /**
     * Loads the requested controller class, creates an instance and returns it.
     * 
     * @param   string  $path
     * @return  Controller
     */
    public function model(string $path): Controller
    {
        if (isset($this->pool['model'][$path])) {
            return $this->pool['model'][$path];
        }
        $controller_class = ltrim(strrchr($path, '/'), '/');
        $file = sprintf('%s/%s.php', MODELS_DIR, trim($path, '/'));
        if (!file_exists($file)) {
            throw new \Exception("Failed to load controller: $file");
        }
        include_once $file;
        return new $controller_class();
    }

    /**
     * Loads the requested controller class, creates an instance and returns it.
     * 
     * @param   string  $path
     * @return  Controller
     */
    public function library(string $path)
    {
        $controller_class = ltrim(strrchr($path, '/'), '/');
        $file = sprintf('%s/%s.php', CONTROLLERS_DIR, trim($path, '/'));
        if (!file_exists($file)) {
            throw new \Exception("Failed to load controller: $file");
        }
        include_once $file;
        return new $controller_class();
    }

    /**
     * Extracts variables to be used in the view and loads the view.
     * If $return = true, the view is returned as a string, otherwise it is sent to output.
     * 
     * @param   string      $_name
     * @param   array       $_data
     * @param   bool        $_return
     * @return  string|void 
     */
    public function view(string $_name, array $_data = [], bool $_return = false)
    {
        $_file = sprintf('%s/%s.php', VIEWS_DIR, trim($_name, '/'));
        if (!file_exists($_file)) {
            throw new \Exception("Failed to load view: $_file");
        }

        extract($_data);

        ob_start();
        include $_file;

        if ($_return) {
            $buffer = ob_get_contents();
            @ob_end_clean();
            return $buffer;
        }

        ob_end_flush();
    }
}
