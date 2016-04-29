<?php

/**
* 自动加载类 
*/
class Loader
{
    // 自动加载搜索目录
    private static $loadPaths = [];
    // 这个暂时没用
    private static $LoadedCache = [];

    // 添加自动加载路径
    public static function addAutoLoadPath($path)
    {
        self::$loadPaths[] = $path;
    }

    // 注册自动加载函数
    public static function register()
    {
        spl_autoload_register('self::autoload');
    }

    // 自动加载函数
    public static function autoload($className)
    {
        $className = ltrim($className, '\\');
        $namespace = '';
        $fileName = '';
        if ($lastPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastPos);
            $className = substr($className, $lastPos + 1);
            $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace(['\\', '_'], DIRECTORY_SEPARATOR, $className) . '.php';
        foreach (self::$loadPaths as $loadPath) {
            $loadPath = rtrim($loadPath, DIRECTORY_SEPARATOR);
            $path = $loadPath . DIRECTORY_SEPARATOR . $fileName;
            if (self::import($path)) {
                break;
            }
        }
    }

    // 包含一个已存在的文件
    public static function import($path)
    {
        return file_exists($path) ? include_once($path) : false;
    }

}
