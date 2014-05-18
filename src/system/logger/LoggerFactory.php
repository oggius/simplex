<?php

namespace system\logger;

use system\basic\Config;
use system\basic\exceptions\WrongConfigException;

/**
 * Class LoggerFactory
 * @package system\logger
 */
class LoggerFactory
{
    /**
     * @param $loggerAdapter
     * @return Logger
     */
    public static function factory($loggerAdapter)
    {
        try {
            $adapterClass = __NAMESPACE__ . '\\adapter\\' . ucfirst($loggerAdapter) . 'Logger';
            $adapter = new $adapterClass();
            if ($adapter instanceof Logger) {
                $adapterConfig = Config::getSectionParam('logger', $loggerAdapter);
                $adapter->init($adapterConfig);
            }
            return $adapter;

        } catch (WrongConfigException $e) {

        }
    }
}