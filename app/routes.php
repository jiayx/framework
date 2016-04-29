<?php

// 路由定义

// Route::get('/', 'Controller\Home::index');
Route::get('aaa/(\d+)/(\d+)', 'Controller\Home::index');
Route::get('aaa/111/111', 'Controller\Home1::index1');

Route::missing(function() {
    echo '404';
});
