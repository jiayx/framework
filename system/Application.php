<?php

use Di\Container;

class Application extends Container
{
    protected $rootPath = '';
    
    public function __construct($rootPath)
    {
        $this['rootPath'] = $rootPath;
    }

    public function rootPath()
    {
        return $this['rootPath'];
    }

    
}
