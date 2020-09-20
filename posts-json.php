<?php
require_once 'config.php';
require_once UTILS_DIR . '/helper.php';

require_once MODELS_DIR . '/Post.php';


try {
    $by = array_keys($_GET)[0];
    $param = $_GET[$by];

    $post = new Post(DB_CONFIG);
    $res = $post->{"search_by_$by"}($param);
    if ($res['error']) {
        $data = $res;
    } else {
        $data = $res['row'] ?? $res['result'];
    }

    header('Content-type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
    die;
} catch (Throwable $e) {
    if (DEBUG) {
        throw $e;
    } else {
        http_response_code(500);
    }
}
