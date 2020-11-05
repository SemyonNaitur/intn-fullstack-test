<?php

//--- system file structure ---//
// define('LIBRARIES_DIR', ROOT_DIR . '/libraries');
// define('LIBRARIES_DIR', ROOT_DIR . '/libraries');
// define('TEMPLATE_DIR', APP_DIR . '/template');
// define('PAGES_DIR', ROOT_DIR . '/pages');
//--- /system file structure ---//

//--- app file structure ---//
define('APP_PATH', 'app');
define('APP_DIR', ROOT_DIR . '/app');
define('CONTROLLERS_DIR', APP_DIR . '/controllers');
define('MODELS_DIR', APP_DIR . '/models');
define('VIEWS_DIR', APP_DIR . '/views');
define('LIBRARIES_DIR', ROOT_DIR . '/libraries');
define('TEMPLATE_DIR', APP_DIR . '/template');
// define('PAGES_DIR', ROOT_DIR . '/pages');
//--- /app file structure ---//


//--- app config ---//
$app_config = [
    'controllers_path'  => APP_PATH . '/controllers',
    'models_path'       => APP_PATH . '/models',
    'views_path'        => APP_PATH . '/views',
    'libraries_path'    => APP_PATH . '/libraries',
];

function get_config(string $item = '')
{
    global $app_config;
    return ($item) ? ($app_config[$item] ?? null) : $app_config;
}

function base_url()
{
    return System\Core\Request::base();
}
//--- /app config ---//


//--- autoloader ---//
define('CUSTOM_AUTOLOADER', 0);
if (CUSTOM_AUTOLOADER) { // https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
    spl_autoload_register(function ($class) {
        // $pfx = 'System\\';
        // $len = strlen($pfx);
        // if (strncmp($pfx, $class, $len) !== 0) return;
        // $cls = substr($class, $len);
        // $file = SYS_DIR . '/' . str_replace('\\', '/', $class) . '.php';
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