<?php
// require_once '../config.php';
// require_once UTILS_DIR . '/helper.php';
// require_once UTILS_DIR . '/DBUtil.php';
// require_once UTILS_DIR . '/CURLUtil.php';
// require_once UTILS_DIR . '/Validator.php';

// require_once 'BlogApi.php';

// try {
//     $input = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST : $_GET;
//     $db = new DBUtil(DB_CONFIG);
//     $curl = new CURLUtil();
//     $validator = new Validator($db);
//     $api = new BlogApi($input, $db, $curl, $validator);
//     $api->run();
// } catch (Throwable $e) {
//     if (DEBUG) {
//         throw $e;
//     } else {
//         http_response_code(500);
//     }
// }
