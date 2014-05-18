<?php 
namespace system\basic;

/**
 * Class Router
 *
 * Is responsible for parsing the request and defining further execution map.
 * @package system
 */
class Router
{
    private static $_instance;

    private $_routingConfig = array();

    private $_parsedRoutes = array();

    /**
     * @var string
     */
    private $_controller;

    /**
     * @var string
     */
    private $_action;

    /**
     * @var array $_params
     */
    private $_params;

    /**
     * @var string canonical URL for the current URL
     */
    private $_canonical;

    /**
     * make construct private in order to prohibit explicit object creation with new
     */
    private function __construct() {}

    /**
     * make __clone method private
     */
    private function __clone() {}

    /**
     * returns instance of the Response object or creates it if not created yet
     * @return Router
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @param array $routes
     */
    public function setRoutingConfig(array $routes)
    {
        $this->_routingConfig = $routes;
    }

    /**
     * @param Request $request
     */
    public function route(Request $request)
    {
        $queryString = $request->getRequestString();
        // delete trailing slash for the correct url parse
        //$queryString = trim($queryString, '/');
        $urlParts = parse_url($queryString);
        $urlParts['path'] = isset($urlParts['path']) ? $urlParts['path'] : '';
        $fullUrl = $urlParts['scheme'] . '://' . $urlParts['host'] . $urlParts['path'];
        if (!empty($this->_routingConfig['urlSuffix'])
            && substr($fullUrl, -strlen($this->_routingConfig['urlSuffix'])) !== $this->_routingConfig['urlSuffix']) {
            // if url doesn't contain suffix, redirect to the one with the suffix
            Redirector::redirect($fullUrl . $this->_routingConfig['urlSuffix']);
        }
            // set the canonical url (mostly SEO optimization)
        if (isset($urlParts['query'])) {
            $this->_canonical = $fullUrl;
        }
        $queryString = $urlParts['path'];
        // delete the suffix as it is unnecessary for parsing
        /*
        if ($this->_routingConfig['urlSuffix']) {
            $queryString = str_replace($this->_routingConfig['urlSuffix'], '', $queryString);
        }
        */
        // perform mapping basing on routes
        $routedString = $this->map($queryString);
        // split the url into segments
        $segments = explode('/', trim($routedString, '/'));

        // define controller and action basing on the routing info
        if (count($segments) > 0 && $segments[0]) {
            $definedController = $segments[0];
            $segments = array_slice($segments, 1);
        } else {
            $definedController = $this->_routingConfig['defaultController'];
        }
        if (count($segments) > 0 && $segments[0]) {
            $definedAction = $segments[0];
            $segments = array_slice($segments, 1);
        } else {
            $definedAction = $this->_routingConfig['defaultAction'];
        }
        $definedParams = array();
        // iterate through the rest of segments to store them as key => value
        if (count($segments) > 0) {
            foreach ($segments as $key => $segment) {
                if ($key % 2 == 0) {
                    $definedParams[$segment] = null;
                    $storedSegment = $segment;
                } else {
                    $definedParams[$storedSegment] = $segment;
                }
            }
        } else {
            $definedParams = array();
        }

        $this->_controller = mb_strtolower($definedController);
        $this->_action = mb_strtolower($definedAction);
        $this->_params = $definedParams;
    }

    /**
     * maps query string to the corresponding route if any
     * @param $queryString
     * @param $routes
     * @return string
     */
    public function map($queryString, $routes = null)
    {
        $routedString = $queryString;
        // check for external routes
        if (empty($routes)) {
            $routes = $this->_routingConfig['routes'];
        }
        if (!empty($routes)) {
            // parse routes, remove masks
            if (empty($this->_parsedRoutes)) {
                $this->_parsedRoutes = $this->parseRoutes($routes);
            }
            // routing here
            // сравниваем введённый урл с маршрутами
            foreach ($this->_parsedRoutes as $key => $val)
            {
                // если урл совпал с маршрутом, ищем внутри него ссылки на найденные совпадения
                if (preg_match('#^'.$key.'$#', $queryString, $match)) {
                    //$queryString = rtrim($queryString, '/');
                    $routedString = preg_replace('#^'.$key.'$#', $val, $queryString);
                    break;
                }
            }

        } else {
            $routedString = $queryString;
        }
        return $routedString;
    }

    /**
     * returns current controller name
     * @return string
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * returns current action name
     * @return string
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * returns set of parsed input params
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * @desc replaces masks with regexp-s
     * @param array
     * @return array
     */
    public function parseRoutes($routes) {
        if (!is_array($routes) || count($routes) == 0) {
            return array();
        }
        $routesParsed = array();
        foreach ($routes as $key => $value) {
            $key = preg_replace(array("/\[any([0-9]+)\]/", "/\[num([0-9]+)\]/"), array('([0-9a-zA-Z._-]+)', '([-0-9]+)'), $key);
            if (substr($key, -1) != '/') {
                $key .= '/{0,1}';
            }
            $value = preg_replace(array("/\[any([0-9]+)\]/", "/\[num([0-9]+)\]/"), '$$1', $value);
            $routesParsed[$key] = $value;
        }
        return $routesParsed;
    }

    /**
     * @return array
     */
    public function getParsedRoutes()
    {
        return $this->_parsedRoutes;
    }
}