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

// 修正cli模式下运行目录不同部分系统函数取值不同的问题 比如: getcwd
defined('STDIN') AND chdir(ROOT_PATH);

require ROOT_PATH . SYSTEM_FOLDER . DIRECTORY_SEPARATOR .'bootstrap.php';
