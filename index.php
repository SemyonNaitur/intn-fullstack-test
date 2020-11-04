<?php

use System\{Core, Logger};

define('ROOT_DIR', __DIR__);
define('SYS_DIR', ROOT_DIR . '/system');

require_once SYS_DIR . '/config.php';
require_once APP_DIR . '/config/config.php';


include TEMPLATE_DIR . '/header.php';

$core_cfg = [
    'routes' => get_config('routes'),
];

if (!empty(get_config('db')['default']['dbname'])) {
    $core_cfg['db'] = 'default';
}

$core = new Core($core_cfg);
$core->init();

include TEMPLATE_DIR . '/footer.php';
