<?php
require_once 'config.php';
require_once UTILS_DIR . '/helper.php';

$page = (empty($_GET['page'])) ? 'create-post' : $_GET['page'];

include TEMPLATE_DIR . '/header.php';

$index_file = PAGES_DIR . "/$page/index.php";
if (file_exists($index_file)) {
    include $index_file;
} else {
    http_response_code(404);
    echo '<h3>Page not found</h3>';
}

include TEMPLATE_DIR . '/footer.php';
