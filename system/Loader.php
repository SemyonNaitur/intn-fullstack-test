<?php

namespace System;

use System\Controller;

class Loader
{

    private $dbs = [];
    private $models = [];

    /**
     * Creates a Db instance, adds it to loaded dbs pool and returns the instance.
     * 
     * @param   string  $name
     * @return  Db
     */
    public function db(string $name): Db
    {
        if (isset($this->dbs[$name])) {
            return $this->dbs[$name];
        }
        $cfg = get_config('db')[$name] ?? null;
        if (!$cfg) throw new \Exception("No configuration for database: $name");
        $db = $this->dbs[$name] = new Db($cfg);
        return $db;
    }

    /**
     * Loads the requested controller class, creates an instance and returns it.
     * 
     * @param   string  $path
     * @return  Controller
     */
    public function controller(string $path): Controller
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
