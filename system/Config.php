<?php

/**
* 配置文件读取
*/
class Config
{
    private static $configPath = ROOT_PATH . 'config';

    // 配置文件缓存
    private static $configCache = [];
    // 已加载文件缓存
    private static $loadedCache = [];

    // 取配置
    public static function get($key, $default = null)
    {
        // 先查找取缓存
        if (isset(self::$configCache[$key])) {
            return self::$configCache[$key];
        }

        // 缓存没有的话查找文件
        $keys = explode('.', $key);
        $fileName = array_shift($keys);

        if (empty(self::$loadedCache[$fileName])) {

            // 当前环境配置文件路径
            $envConfigPath = self::$configPath . DS . ENV . DS . $fileName . '.php';
            // 公共配置文件路径
            $commonConfigPath = self::$configPath . DS . $fileName . '.php';

            if (file_exists($envConfigPath)) {
                $envCconfig = (array) require $envConfigPath;
            }
            if (file_exists($commonConfigPath)) {
                $commonConfig = (array) require $commonConfigPath;
            }
            // 合并环境配置和公共配置 并写入缓存
            self::$loadedCache[$fileName] = $envCconfig + $commonConfig;
        }
        
        $config = self::$loadedCache[$fileName];
        // 循环取出要获取的值 没有则返回默认值
        foreach ($keys as $k) {
            if (isset($config[$k])) {
                $config = $config[$k];
            } else {
                return $default;
            }
        }
        return self::$configCache[$key] = $config;
    }

    // 设定配置 - 设置完之后 在此脚本之后的代码获取到的值都变为此值 - 通过set(key, '') 来恢复默认 
    public static function set($key, $value)
    {
        if (!empty($key)) {
            self::$configCache[$key] = $value;
        }
    }
}
