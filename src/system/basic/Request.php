<?php
namespace system\basic;

/**
 * Class Request
 * @package system\basic
 */
class Request
{
    /**
     * @var Request
     */
    private static $_instance;

    /**
     * @var array
     */
    private $_raw = array();

    /**
     * @var string
     */
    private $_requestString;

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
     * @return Request
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function processRequest()
    {
        $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https') === FALSE ? 'http' : 'https';
        $this->_requestString = $protocol . '://' .
                                $_SERVER['SERVER_NAME'] .
                                $_SERVER['REQUEST_URI'];

        $this->_raw = array(
            'POST' => $_POST,
            'GET'  => $_GET,
            'COOKIES' => $_COOKIE,
            'FILES' => $_FILES,
            'REQUEST' => $_REQUEST
        );
    }

    /**
     * returns input parameter $paramName from the corresponding section.
     * if there's no parameter with such a name, returns default value
     * @param $paramName
     * @param null $default
     * @param null $section
     * @return null
     */
    public function getParam($paramName, $default = null, $section = null)
    {
        $paramValue = null;
        if ($section === null) {
            $section == 'request';
        }
        if ($this->isParam($paramName, $section)) {
            $paramValue = $this->_raw[$section][$paramName];
        } else {
            $paramValue = $default;
        }
        return $paramValue;
    }

    /**
     * returns the whole section of params
     * @param null $section
     * @return array
     */
    public function getParams($section = null)
    {
        if ($section === null) {
            $section == 'request';
        }

        if (array_key_exists(strtoupper($section), $this->_raw)) {
            return $this->_raw[strtoupper($section)];
        } else {
            return array();
        }
    }

    /**
     * checks existence of the input param $paramName
     *
     * @param $paramName
     * @param null $section
     * @return bool
     */
    public function isParam($paramName, $section = null)
    {
        if ($section === null) {
            $section = 'request';
        }
        if (array_key_exists($paramName, $this->_raw[$section]) && $this->_raw[$section][$paramName] !== null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * returns post param $paramName or $default
     * @param $paramName
     * @param null $default
     * @return null
     */
    public function post($paramName, $default = null)
    {
        return $this->getParam($paramName, $default, 'POST');
    }

    /**
     * returns get param $paramName or $default
     *
     * @param $paramName
     * @param null $default
     * @return null
     */
    public function get($paramName, $default = null)
    {
        return $this->getParam($paramName, $default, 'GET');
    }

    /**
     * returns request param $paramName or $default
     * @param $paramName
     * @param null $default
     * @return null
     */
    public function request($paramName, $default = null)
    {
        return $this->getParam($paramName, $default, 'REQUEST');
    }

    /**
     * @return string
     */
    public function getRequestString()
    {
        return $this->_requestString;
    }

    /**
     * checks if current request is ajax request
     * @return bool
     */
    public function isAjax()
    {
        return $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * @return bool
     */
    public function isCli()
    {
        return php_sapi_name() == 'cli' || mb_stripos($_SERVER['HTTP_USER_AGENT'], 'wget') !== false;
    }

    /**
     * checks if the request is the Google UTM request
     * @return bool
     */
    public function isUTMRequest()
    {
        $utmMarkers = array('utm_source', 'utm_medium', 'utm_term', 'utm_content', 'utm_campaign');
        $keysGET = array_keys($this->getParams('GET'));
        $hasMarkers = array_intersect($keysGET, $utmMarkers);
        return count($hasMarkers) > 0;
    }
}
