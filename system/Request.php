<?php

/**
* 请求解析
*/
class Request
{
    // public function __construct()
    // {
    //     if (IS_CLI) {
    //         parse_str($_SERVER['QUERY_STRING'], $_GET);
    //     }
    // }

    public static function uri()
    {
        $uri = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            // nginx
            $parseUrl = parse_url('http://dummy' . $_SERVER['REQUEST_URI']);
            $uri = isset($parseUrl['path']) ? trim($parseUrl['path'], '/') : '';
            // $queryString = isset($parseUrl['query']) ? $parseUrl['query'] : '';
        } elseif ($_SERVER['PATH_INFO']) {
            // apache
            $uri = trim($_SERVER['PATH_INFO'], '/');
        } elseif (IS_CLI) {
            // cli
            $query = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '';
            $query = explode('?', $query, 2);
            $uri = $query[0];
            $queryString = isset($query[1]) ? $query[1] : '';
            parse_str($queryString, $_GET);
        }
        return $uri ?: '/';
    }

    public static function parse()
    {
        if (IS_CLI) {
            $query = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '';
            $query = explode('?', $query, 2);
            $_SERVER['PATH_INFO'] = $query[0];
            $_SERVER['QUERY_STRING'] = isset($query[1]) ? $query[1] : '';
            parse_str($_SERVER['QUERY_STRING'], $_GET);
        }
        print_r($_SERVER);die;
        $uri = trim($_SERVER['PATH_INFO'], '/');
        return $uri ?: '/';
    }
}