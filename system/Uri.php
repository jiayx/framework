<?php

/**
* uri解析
*/
class Uri
{
    public static function parse()
    {
        if (IS_CLI) {
            $query = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '';
            $query = explode('?', $query, 2);
            $_SERVER['PATH_INFO'] = $query[0];
            $_SERVER['QUERY_STRING'] = isset($query[1]) ? $query[1] : '';
            parse_str($_SERVER['QUERY_STRING'], $_GET);
        }
        $uri = trim($_SERVER['PATH_INFO'], '/');
        return $uri ?: '/';
    }
}