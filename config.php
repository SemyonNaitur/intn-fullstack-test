<?php

define('DEBUG', true);

//--- file structure ---//
define('UTILS_DIR', __DIR__ . '/utils');
define('MODELS_DIR', __DIR__ . '/models');
define('LIB_DIR', __DIR__ . '/lib');
define('TEMPLATE_DIR', __DIR__ . '/template');
define('PAGES_DIR', __DIR__ . '/pages');
//--- /file structure ---//


//---  DB ---//
define('DB_CONFIG', [
    'host' => 'localhost',
    'dbname' => 'mvc_sandbox',
    'user' => 'root',
    'pass' => ''
]);
//---  /DB ---//


function exception_error_handler($severity, $message, $file, $line)
{
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}
set_error_handler("exception_error_handler");
