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
     * Return the loaded view/styles/scripts as a string.
     */
    const RETURN = 'return';

    /**
     * Add loaded styles to the data array.
     */
    const STYLES = 'styles';

    /**
     * Add loaded scripts to the data array.
     */
    const SCRIPTS = 'scripts';

    /**
     * When passed to styles() or scripts(), the names will be treated as external urls.
     * If INLINE is also passed, an exception will be thrown.
     */
    const EXTERNAL = 'external';

    /**
     * Load a stylesheet or script content inline.
     * If passed, external urls are not allowed, passing EXTERNAL will throw an exception.
     */
    const INLINE = 'inline';

    /**
     * Don't throw an exception when a file is missing.
     */
    const SILENT = 'silent';

    private $pools = [
        'dbs' => [],
        'models' => [],
        'libraries' => [],
    ];

    private $styles = '';
    private $scripts = '';

    public static function checkSuffix($suffix, $class)
    {
        if (substr(strtolower($class), -strlen($suffix)) !== $suffix) {
            $class .= ucfirst($suffix);
        }
        return $class;
    }

    /**
     * Creates a Db instance, adds it to loaded dbs pool and returns the instance.
     * Excepts an optional config array to merge into the default config, if exists.
     * 
     * @param   string  $name
     * @param   array   $config
     * @param   array   $opts
     * @return  Db
     */
    public function db(string $name = 'default', array $config = null, array $opts = null): Db
    {
        $pool = 'dbs';
        $dbs = &$this->pools[$pool];

        $opts ??= [];

        if (!in_array(self::NEW_INSTANCE, $opts)) {
            if (isset($dbs[$name])) return $dbs[$name];
        }

        $defaults = app_config('db')[$name] ?? [];
        $config = array_merge($defaults, $config ?? []);
        if (!$config) throw new \Exception("No configuration for database: $name");

        $db = new Db($config);

        if (!in_array(self::NEW_INSTANCE, $opts)) {
            $dbs[$name] = $db;
        }
        return $db;
    }

    public function model(string $name, array $args = null, array $opts = null): Model
    {
        $instance = $this->loadClass('models', $name, $args, $opts);
        return $instance->init($this->getDefaultDb());
    }

    public function library(string $name, array $args = null, array $opts = null): object
    {
        return $this->loadClass('libraries', $name, $args, $opts);
    }

    public function loadClass(string $pool, string $name, array $args = null, array $opts = null): object
    {
        $opts ??= [];
        $args ??= [];

        if (!isset($this->pools[$pool])) {
            throw new \Exception("Invalid class type: $pool");
        }
        $p = &$this->pools[$pool];

        if (!in_array(self::NEW_INSTANCE, $opts)) {
            if (isset($p[$name])) return $p[$name];
        }

        $cls = app_config("{$pool}_path") . '/' . $name;
        $cls = str_replace('/', '\\', $cls);
        try {
            $instance = new $cls(...$args);
        } catch (\Throwable $e) {
            // If the class was loaded but failed to construct, rethrow the exception.
            if (class_exists($cls)) {
                throw $e;
            }

            // If a library is requsted, but isn't found under app/, it is searched again under system/.
            if ($pool === 'libraries') {
                $cls = str_replace('app\\', 'system\\', $cls);
            }

            $instance = new $cls(...$args);
        }

        if (!in_array(self::NEW_INSTANCE, $opts)) {
            $p[$name] = $instance;
        }
        return $instance;
    }

    /**
     * Extracts variables to be used in the view and loads the view.
     * If $return = true, the view is returned as a string, otherwise it is sent to output.
     * 
     * @param   string      $name
     * @param   array       $data
     * @param   array       $opts
     * @return  string|void 
     */
    public function view(string $name, array $data = null, array $opts = null)
    {
        $data ??= [];
        $opts ??= [];

        $__args = compact('name', 'data', 'opts');
        unset($name, $data, $opts);
        $__file = sprintf('%s/%s/%s.php', ROOT_DIR, app_config('views_path'), $__args['name']);
        if (!file_exists($__file)) {
            throw new \Exception("Failed to load view: $__file");
        }

        extract($__args['data']);

        if (in_array(self::STYLES,  $__args['opts'])) {
            $__styles = $this->styles;
        }

        if (in_array(self::SCRIPTS,  $__args['opts'])) {
            $__scripts = $this->scripts;
        }

        ob_start();
        include $__file;

        if (in_array(self::RETURN, $__args['opts'])) {
            $buffer = ob_get_contents();
            @ob_end_clean();
            return $buffer;
        }

        ob_end_flush();
    }

    private static function loadResources($names, string $func, string $inlineFunc, array $opts): string
    {
        if (!is_array($names)) $names = [$names];
        $resources = '';

        if (in_array(self::INLINE, $opts)) {
            if (in_array(self::EXTERNAL, $opts)) {
                throw new \Exception("External resources cannot be loaded inline");
            }
            $func = $inlineFunc;
        }

        foreach ($names as $name) {
            $resources .= self::$func($name, $opts);
        }

        return $resources;
    }

    /**
     * 
     * @param   string|string[] $names
     * @param   array           $opts
     * @return  string|void 
     */
    public function styles($names, array $opts = null)
    {
        $opts ??= [];
        $styles = self::loadResources($names, 'addStylesheet', 'loadStylesheetInline', $opts);
        if (in_array(self::RETURN, $opts)) {
            return $styles;
        }
        $this->styles .= $styles;
    }

    private static function addStylesheet(string $name, array $opts): string
    {
        if (in_array(self::EXTERNAL, $opts)) {
            $href = $name;
        } else {
            $href = sprintf('%s/%s.css', app_config('styles_path'), $name);
        }
        return "<link rel=\"stylesheet\" href=\"$href\">\n";
    }

    private static function loadStylesheetInline(string $name, array $opts): string
    {
        $file = sprintf('%s/%s/%s.css', ROOT_DIR, app_config('styles_path'), $name);
        if (!file_exists($file)) {
            if (in_array(self::SILENT, $opts)) {
                log_debug("Requested stylesheet does not exist: $file");
                return '';
            }
            throw new \Exception("File does not exist: $file");
        }
        return "<style>\n" . file_get_contents($file) . "\n</style>\n";
    }

    /**
     * 
     * @param   string|string[] $names
     * @param   array           $opts
     * @return  string|void 
     */
    public function scripts($names, array $opts = null)
    {
        $opts ??= [];
        $scripts = self::loadResources($names, 'addScript', 'loadScriptInline', $opts);
        if (in_array(self::RETURN, $opts)) {
            return $scripts;
        }
        $this->scripts .= $scripts;
    }

    private static function addScript(string $name, array $opts): string
    {
        if (in_array(self::EXTERNAL, $opts)) {
            $src = $name;
        } else {
            $src = sprintf('%s/%s.js', app_config('js_path'), $name);
        }
        return "<script src=\"$src\"></script>\n";
    }

    private static function loadScriptInline(string $name, array $opts): string
    {
        $file = sprintf('%s/%s/%s.js', ROOT_DIR, app_config('js_path'), $name);
        if (!file_exists($file)) {
            if (in_array(self::SILENT, $opts)) {
                log_debug("Requested script does not exist: $file");
                return '';
            }
            throw new \Exception("File does not exist: $file");
        }
        return "<script>\n" . file_get_contents($file) . "\n</script>\n";
    }

    public function getDefaultDb(): ?Db
    {
        return $this->pools['dbs']['default'] ?? null;
    }
}
