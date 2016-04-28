<?php

// 修正cli模式下运行目录不同部分系统函数取值不同的问题 比如: getcwd
defined('STDIN') AND chdir(__DIR__);


defined('APP_DEBUG') OR define('APP_DEBUG', false);


define('IS_CGI', (0 === strpos(PHP_SAPI, 'cgi') || false !== strpos(PHP_SAPI, 'fcgi')) ? 1 : 0);
define('IS_WIN', strstr(PHP_OS, 'WIN') ? 1 : 0);
define('IS_CLI', PHP_SAPI == 'cli' ? 1 : 0);

define('REQUEST_METHOD', IS_CLI ? 'GET' : $_SERVER['REQUEST_METHOD']);

include SYSTEM_PATH . 'Loader.php';

Loader::register();
Loader::addAutoLoadPath(SYSTEM_PATH);
Loader::addAutoLoadPath(APP_PATH);

Loader::import(APP_PATH . 'route.php');

Route::run();
