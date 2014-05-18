<?php
namespace system\basic;

use \system\basic\exceptions\WrongConfigException;

class Config {
    /**
     * @var array
     */
    private static $_sections = array();

    /**
     * @param $section
     * @param bool $throw
     * @return array array with config data
     * @throws exceptions\WrongConfigException
     */
    public static function getSection($section, $throw = true)
    {
        if (!is_scalar($section)) {
            if ($throw) {
                throw new WrongConfigException('Wrong config section name, scalar expected, ' . gettype($section) . ' given');
            } else {
                return array();
            }
        }

        if (array_key_exists($section, self::$_sections)) {
            return self::$_sections[$section];
        } else {
            if (is_file( APP_PATH . 'configs' . DS . $section . '.php')) {
                include APP_PATH . 'configs' . DS . $section . '.php';
                $configVar = $section . 'Cfg';
                if (isset($$configVar)) {
                    self::$_sections[$section] = $$configVar;
                    return self::$_sections[$section];
                } else {
                    if ($throw) {
                        throw new WrongConfigException('Config var must be named ' . $section . 'Cfg');
                    } else {
                        return array();
                    }
                }
            } else {
                if ($throw) {
                    throw new WrongConfigException('No config file ' . $section . '.php was found');
                } else {
                    return array();
                }
            }
        }
    }

    /**
     * tries to find $paramName param in the `$section` section of the config
     * @param $section
     * @param $paramName
     * @param bool $throw
     * @return mixed returns found config param or throws exception (if $throw == true) or returns null
     * @throws exceptions\WrongConfigException
     */
    public static function getSectionParam($section, $paramName, $throw = true)
    {
        if ($sectionConfig = self::getSection($section, $throw)) {
            if (array_key_exists($paramName, $sectionConfig)) {
                return $sectionConfig[$paramName];
            } elseif ($throw) {
                throw new WrongConfigException('Missing `' . $paramName . '` param in `' . $section . '` section');
            } else {
                return null;
            }
        }
        return null;
    }
}