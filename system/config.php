<?php

//--- file structure ---//
define('SYS_LIB_DIR', SYS_DIR . '/libraries');
define('APP_PATH', 'app');
define('APP_DIR', ROOT_DIR . '/' . APP_PATH);
//--- /file structure ---//


if (file_exists(ROOT_DIR . '/site.inc')) {
    require_once ROOT_DIR . '/site.inc';
}
require_once SYS_DIR . '/app_helper.php';


//--- app config ---//
function app_config(string $property = '', $value = null)
{
    static $config = [
        'debug' => false,

        //--- app file structure ---//
        'controllers_path'  => APP_PATH . '/controllers',
        'models_path'       => APP_PATH . '/models',
        'views_path'        => APP_PATH . '/views',
        'libraries_path'    => APP_PATH . '/libraries',
        'styles_path'       => 'styles',
        'js_path'           => 'js',
        //--- /app file structure ---//
    ];

    if (!$property) return $config;

    if (is_null($value)) return $config[$property] ?? null;

    $config[$property] = $value;
}

function js_config(string $property = '', $value = null)
{
    static $config = [];

    if (!$property) return $config;

    if (is_null($value)) return $config[$property] ?? null;

    $config[$property] = $value;
}

require_once APP_DIR . '/config/config.php';
//--- /app config ---//


//--- autoloader ---//
define('CUSTOM_AUTOLOADER', 0);
if (CUSTOM_AUTOLOADER) { // https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
    spl_autoload_register(function ($class) {
        $file = ROOT_DIR . '/' . str_replace('\\', '/', $class) . '.php';

        if (file_exists($file)) {
            require $file;
        }
    });
} else { // https://www.php.net/manual/en/function.spl-autoload.php#92767
    set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR);
    spl_autoload_extensions('.php');
    spl_autoload_register();
}
//--- /autoloader ---//


//--- error handling ---//
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});
//--- /error handling ---//