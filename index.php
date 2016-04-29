<?php

if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    exit('PHP版本必须高于5.4');
}


define('APP_DEBUG', true);
define('APP_FOLDER', 'app');
define('SYSTEM_FOLDER', 'system');
define('ENV', 'development');
// 应用根目录
define('ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);
define('APP_PATH', ROOT_PATH . APP_FOLDER . DIRECTORY_SEPARATOR);
define('SYSTEM_PATH', ROOT_PATH . SYSTEM_FOLDER . DIRECTORY_SEPARATOR);

require 'system/bootstrap.php';
