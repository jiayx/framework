<?php

/**
* 路由
*/

class Route
{
    private static $rules = [
        'GET' => [],
        'POST' => [],
        'ALL' => [],
        'MISSING' => '',
    ];

    public static function run()
    {
        $uri = Request::uri();
        // print_r($uri);die;
        $routes = self::routes();
        if (array_key_exists($uri, $routes)) {
            if (self::dispatch($routes[$uri])) {
                return;
            }
        } else {
            foreach ($routes as $key => $route) {
                if (! preg_match('~^' . $key . '$~', $uri, $match)) {
                    continue;
                }
                array_shift($match);
                if (self::dispatch($route, $match)) {
                    return;
                }
            }
        }
        self::dispatch(self::$rules['MISSING']);
    }

    // 路由分发
    private static function dispatch(&$method, $params = [])
    {
        if (is_string($method)) {
            if ($isCallable = self::isCallable($method)) {
                call_user_func_array($isCallable, $params);
                return true;
            }
        } elseif (is_callable($method)) {
            call_user_func_array($method, $params);
            return true;
        } else {
            // todo 
            return false;
        }
    }

    // 是否可调用
    private static function isCallable($method)
    {
        $method = explode('@', $method, 2);
        if (count($method) < 2) {
            return false;
        }
        $className = $method[0];
        $methodName = $method[1];
        if (! class_exists($className)) {
            // echo 'class: ', $className , ' not found!';
            return false;
        }
        $class = new $className;
        if (! method_exists($class, $methodName)) {
            // echo 'method: ', $methodName, ' not found!';
            return false;
        }

        return [$class, $methodName];
    }

    private static function routes()
    {
        $routes = isset(self::$rules[REQUEST_METHOD]) ? self::$rules[REQUEST_METHOD] : [];
        return $routes + self::$rules['ALL'];
    }

    // get请求路由
    public static function get($uri, $method)
    {
        self::$rules['GET'][$uri] = $method;
    }
    
    // post请求路由
    public static function post($uri, $method)
    {
        self::$rules['POST'][$uri] = $method;
    }

    // 任意请求路由
    public static function all($uri, $method)
    {
        self::$rules['ALL'][$uri] = $method;
    }

    // 找不到路由时
    public static function missing($method)
    {
        self::$rules['MISSING'] = $method;
    }

}