<?php
namespace system\basic;

/**
 * Class Session
 * @package system\basic
 */
class Session {

    /**
     * @var array session params
     */
    private static $_params = array();

    /**
     * inits the session
     */
    public static function init()
    {
        session_start();
        self::$_params = $_SESSION;
    }

    /**
     * saves var to session
     * @param $paramName
     * @param $paramValue
     * @return bool
     */
    public static function set($paramName, $paramValue)
    {
        $_SESSION[$paramName] = $paramValue;
        if (isset($_SESSION[$paramName])) {
            self::$_params[$paramName] = $paramValue;
            return true;
        } else {
            return false;
        }
    }

    /**
     * returns requested session var
     * @param $paramName
     * @return mixed
     */
    public static function get($paramName)
    {
        if (self::has($paramName)) {
            return self::$_params[$paramName];
        } else {
            return false;
        }
    }

    /**
     * checks param presence
     * @param $paramName
     * @return bool
     */
    public static function has($paramName)
    {
        return isset(self::$_params[$paramName]);
    }

    /**
     * clears whole session or session param
     * @param null $paramName
     * @return bool
     */
    public static function clear($paramName = null)
    {
        if (!is_null($paramName)) {
            unset(self::$_params[$paramName]);
            unset($_SESSION[$paramName]);
            return true;
        } else {
            self::$_params = array();
            unset($_SESSION);
        }
        return true;
    }
}