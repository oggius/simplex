<?php
namespace system\logger;

/**
 * Class Logger
 * @package system\logger
 */
abstract class Logger {

    /**
     * @param array $config
     * @return void
     */
    abstract public function init(array $config);

    /**
     * @param $logData
     * @param string $logSection
     * @return bool
     */
    abstract public function log($logData, $logSection = 'general');
}