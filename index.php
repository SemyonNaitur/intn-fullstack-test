<?php

use System\Core\{Core, Loader, Request, Router};

define('ROOT_DIR', __DIR__);
define('SYS_DIR', ROOT_DIR . '/system');

require_once SYS_DIR . '/config.php';
require_once APP_DIR . '/config/config.php';

$__core = new Core(
    new Loader(),
    new Request(),
    new Router(['routes' => get_config('routes')])
);
$__core->init();
