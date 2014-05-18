<?php
namespace system\basic;

use system\cache\CacheDriver;
use system\db\DbDriver;
use system\logger\Logger;

/**
 * Class BaseModel class is an abstract base class for all the MVC Models components
 * @package system\basic
 */
abstract class BaseModel {
    /**
     * @var DbDriver link to the Database driver
     */
    protected $db;

    /**
     * @var DbDriver for static functions
     */
    protected static $dbstatic;

    /**
     * @var CacheDriver
     */
    protected $cache;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct()
    {
        // bind database
        $databaseDriver = Registry::get('dbdriver');
        if ($databaseDriver && $databaseDriver instanceof DbDriver) {
            $this->setDbDiver($databaseDriver);
        }

        // bind logger
        $logger = Registry::get('logger');
        if ($logger && $logger instanceof Logger) {
            $this->logger = $logger;
        }

        // bind cache
        $cache = Registry::get('cache');
        if ($cache && $cache instanceof CacheDriver) {
            $this->cache = $cache;
        }
    }

    /**
     * workaround for static controller
     */
    public static function __constructStatic()
    {
        self::$dbstatic = Registry::get('dbdriver');
    }

    /**
     * @param DbDriver $driver
     */
    public function setDbDiver(DbDriver $driver)
    {
        $this->db = $driver;
    }

    /**
     * @return DbDriver
     */
    public static function getDb()
    {
        return self::$dbstatic;
    }
}
BaseModel::__constructStatic();