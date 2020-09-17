<?php
require_once '../config.php';
require_once UTILS_DIR . '/helper.php';
require_once UTILS_DIR . '/DBUtil.php';
require_once UTILS_DIR . '/CURLUtil.php';

require_once MODELS_DIR . '/Post.php';
require_once MODELS_DIR . '/User.php';

require_once 'BlogAjax.php';


$pdo = new DBUtil(DB_CONFIG);

$post = new Post($pdo);
$post->debug = true;

$user = new User($pdo);
$post->debug = true;

$curl = new CURLUtil();
$post->debug = true;

$input = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST : $_GET;

$ajax = new BlogAjax($input, $post, $user, $curl);
$ajax->run();
