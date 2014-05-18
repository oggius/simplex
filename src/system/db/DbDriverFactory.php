<?php
namespace system\db;


use system\basic\Config;
use system\basic\exceptions\BadClassInstantiationException;
use system\basic\exceptions\MissingClassException;

class DbDriverFactory {
    private static $_availableDrivers = array('mysql', 'mysqli', 'pdo');

    /**
     * Instantiates correct DatabaseDriver
     * @param string $driver
     * @return DbDriver
     * @throws \system\basic\exceptions\MissingClassException
     * @throws \system\basic\exceptions\BadClassInstantiationException
     */
    public static function factory($driver)
    {
        if (!in_array($driver, self::$_availableDrivers)) {
            throw new MissingClassException('Database driver `' . $driver . '` is not available');
        }

        $driverClass = __NAMESPACE__ . '\\driver\\' . ucfirst($driver) . 'DbDriver';
        $driver = new $driverClass( Config::getSectionParam('db', Config::getSectionParam('application', 'mode')));
        if (!($driver instanceof DbDriver)) {
            throw new BadClassInstantiationException('Database driver `' . $driverClass . '` wasn\'t instantiated correctly');
        }
        return $driver;
    }
}