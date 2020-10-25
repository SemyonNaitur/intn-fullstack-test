<?php

//--- file structure ---//
define('APP_DIR', ROOT_DIR . '/app');
define('CONTROLLERS_DIR', APP_DIR . '/controllers');
define('MODELS_DIR', APP_DIR . '/models');
define('LIB_DIR', ROOT_DIR . '/lib');
define('TEMPLATE_DIR', APP_DIR . '/template');
define('PAGES_DIR', ROOT_DIR . '/pages');
//--- /file structure ---//


$app_config = [];
function get_config(string $item = '')
{
    global $app_config;
    return ($item) ? ($app_config[$item] ?? null) : $app_config;
}


spl_autoload_register(function ($class) { // https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
    $pfx = 'System\\';
    $len = strlen($pfx);
    if (strncmp($pfx, $class, $len) !== 0) return;
    $cls = substr($class, $len);
    $file = SYS_DIR . '/' . str_replace('\\', '/', $cls) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});


set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});
