<?php

define('DEBUG', true);

//--- file structure ---//
define('UTILS_DIR', __DIR__ . '/utils');
define('CONTROLLERS_DIR', __DIR__ . '/controllers');
define('MODELS_DIR', __DIR__ . '/models');
define('LIB_DIR', __DIR__ . '/lib');
define('TEMPLATE_DIR', __DIR__ . '/template');
define('PAGES_DIR', __DIR__ . '/pages');
//--- /file structure ---//


//---  DB ---//
define('DB_CONFIG', [
    'host' => 'localhost',
    'dbname' => 'intn_blog',
    'user' => 'root',
    'pass' => ''
]);
//---  /DB ---//

$routes = [
    ['path' => '/print-request', 'method' => 'test/test::print_request'],
];

function exception_error_handler($severity, $message, $file, $line)
{
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}
set_error_handler("exception_error_handler");
