<?php

//--- file structure ---//
define('SYS_LIB_DIR', SYS_DIR . '/libraries');
define('APP_PATH', 'app');
define('APP_DIR', ROOT_DIR . '/' . APP_PATH);
//--- /file structure ---//


//--- app config ---//
$app_config = [
    'debug' => false,

    //--- app file structure ---//
    'controllers_path'  => APP_PATH . '/controllers',
    'models_path'       => APP_PATH . '/models',
    'views_path'        => APP_PATH . '/views',
    'libraries_path'    => APP_PATH . '/libraries',
    //--- /app file structure ---//
];

require_once APP_DIR . '/config/config.php';

function get_config(string $item = '')
{
    global $app_config;
    return ($item) ? ($app_config[$item] ?? null) : $app_config;
}
//--- /app config ---//


//--- autoloader ---//
define('CUSTOM_AUTOLOADER', 1);
if (CUSTOM_AUTOLOADER) { // https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
    spl_autoload_register(function ($class) {
        $file = ROOT_DIR . '/' . str_replace('\\', '/', $class) . '.php';

        if (!file_exists($file)) {
            $app_lib_dir = ROOT_DIR . '/' . get_config('libraries_path');
            /**
             * If a library is requsted, but isn't found under app/, it is searched again under system/.
             */
            if (stripos($file, $app_lib_dir) === 0) {
                str_ireplace($app_lib_dir, SYS_LIB_DIR, $file);
            } else {
                return;
            }
        }

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