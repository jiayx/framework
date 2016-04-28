<?php

// 路由定义

Route::get('/', 'Controller\Home::index');
Route::get('aaa/(\d+)/(\d+)', 'Controller\Home::index');
