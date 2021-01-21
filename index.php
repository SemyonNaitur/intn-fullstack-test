<?php

use System\Core\{App, Loader, Request, Router};

define('ROOT_DIR', __DIR__);
define('SYS_DIR', ROOT_DIR . '/system');

require_once SYS_DIR . '/config.php';
require_once SYS_DIR . '/app_helper.php';

(App::bootstrap(
    new Loader(),
    new Request(),
    new Router(['routes' => app_config('routes')])
))->run();
