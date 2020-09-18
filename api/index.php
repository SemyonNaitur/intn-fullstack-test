<?php
require_once '../config.php';
require_once UTILS_DIR . '/helper.php';
require_once UTILS_DIR . '/DBUtil.php';
require_once UTILS_DIR . '/CURLUtil.php';

require_once MODELS_DIR . '/Post.php';
require_once MODELS_DIR . '/User.php';

require_once 'BlogAjax.php';

try {
    $pdo = DBUtil::PDO(DB_CONFIG);
    $post = new Post($pdo);
    $user = new User($pdo);
    $curl = new CURLUtil();
    $post->debug = $user->debug = $curl->debug = DEBUG;
    echo 'REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD'];
    print_r($_GET);
    print_r($_POST);
    $input = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST : $_GET;
    $ajax = new BlogAjax($input, $post, $user, $curl);
    $ajax->run();
} catch (Throwable $e) {
    if (DEBUG) {
        echo $e->getMessage;
    } else {
        http_response_code(500);
    }
}
