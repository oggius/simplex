<?php
namespace system\cache;

use system\basic\Config;
use system\basic\exceptions\BadClassInstantiationException;
use system\basic\exceptions\WrongConfigException;

/**
 * Class CacheFactory
 * @package system\cache
 */
class CacheFactory {

    /**
     * @param $driverName
     * @return CacheDriver
     * @throws \system\basic\exceptions\BadClassInstantiationException
     */
    public static function factory($driverName)
    {
        try {
            $driverClass = __NAMESPACE__ . '\\driver\\' . ucfirst($driverName) . 'Driver';
            $driver = new $driverClass();
            if ($driver instanceof CacheDriver) {
                $driverConfig = Config::getSectionParam('cache', $driverName);
                $driver->init($driverConfig);
            }
            return $driver;
        } catch (WrongConfigException $e) {
            throw new BadClassInstantiationException('CacheDriver driver initialisation failed', 500);
        }
    }
}