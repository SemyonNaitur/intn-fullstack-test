<?php
require_once '../config.php';
require_once UTILS_DIR . '/helper.php';
require_once UTILS_DIR . '/DBUtil.php';
require_once UTILS_DIR . '/CURLUtil.php';

require_once MODELS_DIR . '/Post.php';
require_once MODELS_DIR . '/User.php';

require_once 'BlogAjax.php';

try {
    $input = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST : $_GET;
    $db = DBUtil::PDO(DB_CONFIG);
    $curl = new CURLUtil();
    $ajax = new BlogAjax($input, $db, $curl);
    $ajax->run();
} catch (Throwable $e) {
    if (DEBUG) {
        throw $e;
    } else {
        http_response_code(500);
    }
}
