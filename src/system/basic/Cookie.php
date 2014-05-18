<?php
namespace system\basic;

/**
 * Class Cookie
 * @package system\basic
 */
class Cookie {

    const EXPIRE_DAY = 86400;
    /**
     * @param null $name
     * @param null $value
     * @param null $lifetime
     * @param null $path
     * @param null $domain
     * @param bool $secure
     * @param bool $httponly
     */
    public function __construct($name = null, $value = null, $lifetime = null, $path = null, $domain = null, $secure = false, $httponly = false)
    {
        if (!empty($name)) {
            $this->set($name, $value, $lifetime, $path, $domain, $secure, $httponly);
        }
    }

    /**
     * @param $name
     * @param null $value
     * @param null $lifetime
     * @param null $path
     * @param null $domain
     * @param bool $secure
     * @param bool $httponly
     * @return bool
     */
    public function set($name, $value = null, $lifetime = null, $path = null, $domain = null, $secure = false, $httponly = false)
    {
        if (empty($name)) {
            return false;
        }

        if (is_array($value)) {
            $value = json_encode($value);
        } elseif (is_object($value)) {
            $value = serialize($value);
        }

        return setcookie($name, $value, time() + intval($lifetime), $path, $domain, $secure, $httponly);
    }

    /**
     * @param $cookieName
     * @return null
     */
    public function getValue($cookieName)
    {
        if (isset($_COOKIE[$cookieName])) {
            return $_COOKIE[$cookieName];
        } else {
            return null;
        }
    }
}