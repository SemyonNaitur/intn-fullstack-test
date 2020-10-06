<?php

//--- file structure ---//
define('APP_DIR', ROOT_DIR . '/app');
define('CONTROLLERS_DIR', APP_DIR . '/controllers');
define('MODELS_DIR', APP_DIR . '/models');
define('LIB_DIR', ROOT_DIR . '/lib');
define('TEMPLATE_DIR', APP_DIR . '/template');
define('PAGES_DIR', ROOT_DIR . '/pages');
//--- /file structure ---//


spl_autoload_register(function ($class) {
    include SYS_DIR . "/$class.php";
});


set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});
