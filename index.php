<?php

use System\Core\{App, Loader, Request, Router};

define('ROOT_DIR', __DIR__);
define('SYS_DIR', ROOT_DIR . '/system');

if (file_exists(ROOT_DIR . '/site.inc')) {
    require_once ROOT_DIR . '/site.inc';
}

require_once SYS_DIR . '/app_helper.php';
require_once SYS_DIR . '/config.php';

header("Access-Control-Allow-Origin: *");

(App::bootstrap(
    new Loader(),
    new Request(),
    new Router(['routes' => app_config('routes')])
))->run();
