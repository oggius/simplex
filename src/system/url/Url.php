<?php
namespace system\url;

use system\basic\Registry;
use system\basic\Router;

/**
 * Class Url - helper for creating valid urls
 * @package application\helpers
 */
class Url {

    /**
     * @var array parsed routes
     */
    private static $_parsedRoutes = array();

    /**
     * primary method for creating urls
     * @param $controller
     * @param null $action
     * @param array $params
     * @param bool $checkRoutes
     * @return string
     */
    public static function _($controller, $action = null, $params = array(), $checkRoutes = true)
    {
        self::setRoutes(Router::getInstance()->getParsedRoutes());

        $queryString =  '/' . $controller . '/' . ($action ? $action . '/' : '');
        if (is_array($params) && count($params) > 0) {
            foreach($params as $key => $val) {
                $queryString .= $key . '/' . $val . '/';
            }
        }

        $routedString = $queryString;
        if ($checkRoutes) {
            // loop through roots, considering keys as destination and values as inputs
            foreach (self::$_parsedRoutes as $baseRoute => $destRoute) {
                if (preg_match('#^'.$baseRoute.'$#', $routedString, $match)) {
                    if (strpos($destRoute, '$') !== FALSE AND strpos($baseRoute, '(') !== FALSE)  {
                        $routedString = preg_replace('#^'.$baseRoute.'$#', $destRoute, $routedString);
                        break;
                    } else {
                        $routedString = $destRoute;
                    }
                }
            }
        }

        return self::getMainUrl() . ltrim($routedString, '/');
    }

    /**
     * set routes for parsing
     * @param array $routes
     */
    public static function setRoutes(array $routes)
    {
        if (!self::$_parsedRoutes) {
            $cache = Registry::get('cache');
            if ($cache && $cachedRoutes = $cache->load('parsed_routes')) {
                self::$_parsedRoutes = $cachedRoutes;
            } else {
                self::$_parsedRoutes = self::_reverseParseRoutes($routes);
                if ($cache) {
                    $cache->save('parsed_routes', self::$_parsedRoutes);
                }
            }
        }
    }

    /**
     * @param $routes
     * @return array
     */
    protected static function _reverseParseRoutes($routes)
    {
        $reverseParsedRoutes = array();
        foreach ($routes as $key => $val) {
            preg_match_all('/\([a-zA-Z0-9-_|.\[\]\+]+\)/', $key, $matchesKeys);
            preg_match_all('/\$[0-9]+/', $val, $matchesVals);
            foreach ($matchesKeys[0] as $i => $match) {
                $key = str_replace($match, $matchesVals[0][$i], $key);
                $val = str_replace($matchesVals[0][$i], $match, $val);
            }
            $reverseParsedRoutes[$val] = $key;
        }
        return $reverseParsedRoutes;
    }

    /**
     * returns correct domain url without subdomain part
     * @return string
     */
    public static function getMainUrl()
    {
        return 'http://' . $_SERVER['SERVER_NAME'] . '/';
    }
}