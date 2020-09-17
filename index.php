<?php
require_once 'config.php';
require_once UTILS_DIR . '/helper.php';

$page = (empty($_GET['page'])) ? 'create-post' : $_GET['page'];

include TEMPLATE_DIR . '/header.php';
switch ($page) {
    case 'sales':
        include PAGES_DIR . "/$page/index.php";
        break;
    default:
        http_response_code(404);
        echo 'Page not found';
}
include TEMPLATE_DIR . '/footer.php';
