<?php

define('ROOT_DIR', __DIR__);
define('SYS_DIR', ROOT_DIR . '/system');

require_once SYS_DIR . '/config.php';
require_once APP_DIR . '/config.php';

//$page = (empty($_GET['page'])) ? 'create-post' : $_GET['page'];

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


// function load_controller(string $path)
// {
//     $controller_class = ltrim(strrchr($path, '/'), '/');
//     $file = CONTROLLERS_DIR . "/$path.php";
//     if (!file_exists($file)) {
//         throw new Exception("Failed to load controller: $file");
//     }
//     require_once $file;
//     return new $controller_class();
// }
