<?php
require 'databases.php';
require 'routes.php';

define('DEBUG', true);

$app_config['app_name'] = 'blog';
$app_config['log_dir'] = ROOT_DIR . '/log';
$app_config['log_format'] = '[Y-m-d H:i:s] {level}: {message}';
