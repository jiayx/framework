<?php

namespace Controller;

use Config;

class Home
{
    public function index()
    {
        // echo 'Hello World!' . PHP_EOL;
        $c = Config::get('database.default');
        var_dump($c);die;
    }
}