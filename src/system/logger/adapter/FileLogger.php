<?php
namespace system\logger\adapter;

use system\logger\Logger;

/**
 * Class FileLogger
 * @package system\logger\adapter
 */
class FileLogger extends Logger
{

    /**
     * @var resource File Handler
     */
    private $_fileHandler;

    /**
     * @var string current logging directory
     */
    private $_logsDir;

    /**
     * @var string log files extension
     */
    private $_logFilesExtension;

    /**
     * @param array $config
     */
    public function init(array $config)
    {
        if (!empty($config['folder'])) {
            $folder = ROOT . trim($config['folder'], DS) . DS;
            if (!is_dir($folder)) {
                $folder = ROOT . 'logs/';
            }
        } else {
            $folder = ROOT . 'logs/';
        }
        $this->_logsDir = $folder;
        $this->_logFilesExtension = $config['extension'] ? $config['extension'] : 'log';
    }

    /**
     * closes file handler
     * @return bool
     */
    public function close() {
        return fclose($this->_fileHandler);
    }

    /**
     * logging. Checks logData type and chooses the needed log format
     * @param $logData
     * @param string $logSection
     * @return bool|int
     */
    public function log($logData, $logSection = 'general') {
        $this->_fileHandler = fopen($this->_logsDir . $logSection . '.' . $this->_logFilesExtension, "a+");

        if (is_object($logData)) {
            $result = $this->logObject($logData);
        } else if (is_array($logData)) {
            $result = $this->logArray($logData);
        } else if (is_long($logData) || is_int($logData) || is_float($logData) || is_double($logData)) {
            $result = $this->logScalar($logData);
        } else if (is_string($logData)) {
            $result = $this->logString($logData);
        } else {
            $result = false;
        }
        $this->close();
        return $result;
    }

    /**
     * logging arrays
     * @param array $array
     * @return int
     */
    private function logArray(array $array) {
        return $this->logString("Logging array: " . print_r($array, true));
    }

    /**
     * logging objects
     * @param $object
     * @return bool
     */
    private function logObject($object)
    {
        $logObject = method_exists($object, '__toString') ? (string)$object : ' [__toString not implemented]';
        return $this->logString("Logging " . get_class($object) . " object: " . $logObject);
    }

    /**
     * logging scalars
     * @param $value
     * @return bool
     */
    private function logScalar($value) {
        return $this->logString("Logging scalar: " . (string)$value);
    }

    /**
     * @param string $string
     * @return bool
     */
    private function logString($string) {
        $str = '--- ' . date('d-m-Y H:i:s') . ' ---' . "\n";
        $str.= $string . "\n\n";
        return fwrite($this->_fileHandler, $str) ? true : false;
    }
}