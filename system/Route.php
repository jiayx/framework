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
    	$uri = Uri::parse();
    	// print_r($uri);die;
        $routes = self::routes();
        if (array_key_exists($uri, $routes)) {
        	self::dispatch($routes[$uri]);
        } else {
        	foreach ($routes as $key => $route) {
        		// echo $key, '</br>';
        		// echo $uri, '</br>';
	        	if (preg_match('~^' . $key . '$~', $uri, $match)) {
	        		array_shift($match);
		        	self::dispatch($route, $match);
	        	}
			}

        }
    }

	// 路由分发
	private static function dispatch($method, $params = [])
	{
		// echo $method;die;
		if (is_string($method)) {
			list($className, $methodName) = explode('::', $method);
			// echo $className, $methodName;die;
			return call_user_func_array([new $className, $methodName], $params);
		} elseif (is_callable($method)) {
			return call_user_func_array($method, $params);
		}
		return false;
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