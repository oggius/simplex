<?php
namespace system\logger\adapter;

use system\basic\Registry;
use system\db\DbDriver;
use system\logger\Logger;

/**
 * Class DbLogger
 * @package system\logger\adapter
 */
class DatabaseLogger extends Logger
{
    /**
     * @var string
     */
    private $_table;

    /**
     * @var DbDriver
     */
    private $_db;

    /**
     * logger init
     * @param array $config
     */
    public function init(array $config)
    {
        if ($config['table']) {
            $this->_table = $config['table'];
        } else {
            $this->_table = 'logs';
        }

        $this->_db = Registry::get('dbdriver');
    }

    /**
     * puts the data into the db
     * @param $logData
     * @param string $logSection
     * @return bool
     */
    public function log($logData, $logSection = 'general')
    {
        if ($this->_db) {
            if (is_object($logData) || is_array($logData)) {
                $logData = serialize($logData);
            }

            $logTime = new \DateTime('now', new \DateTimeZone('UTC'));
            // store the info into the db
            return $this->_db->insert(
                                'logs',
                                array(
                                    'section' => $logSection,
                                    'data' => $logData,
                                    'time' => $logTime->format('Y-m-d H:i:s')
                                )
                            );
        } else {
            return false;
        }
    }
}