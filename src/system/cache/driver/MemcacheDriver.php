<?php
namespace system\cache\driver;

use system\cache\CacheDriver;

/**
 * Class MemcacheDriver
 * @package system\cache\driver
 */
class MemcacheDriver extends CacheDriver
{
    /**
     * @var \Memcache
     */
    private $_memcache;

    /**
     * @param array $config
     * @return bool
     */
    public function init(array $config)
    {
        if (class_exists("Memcache", false)) {
            $cache = new \Memcache();
            if ($cache->connect($config['host'], $config['port'])) {
                $this->_memcache = $cache;
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $cacheLabel
     * @return array|mixed|null|string
     * @todo remove host prefix when deploying
     */
    public function load($cacheLabel)
    {
        if (!$this->_memcache) {
            return null;
        }

        return $this->_memcache->get($_SERVER['SERVER_NAME'] . $cacheLabel);
    }

    /**
     * @param $cacheLabel
     * @param $cacheData
     * @param null $expire
     * @return bool
     * @todo remove host prefix when deploying
     */
    public function save($cacheLabel, $cacheData, $expire = null)
    {
        if (!$this->_memcache) {
            return false;
        }

        return $this->_memcache->set($_SERVER['SERVER_NAME'] . $cacheLabel, $cacheData);
    }

    /**
     * @param $cacheLabel
     * @return bool
     * @todo remove host prefix when deploying
     */
    public function test($cacheLabel)
    {
        if (!$this->_memcache) {
            return false;
        }

        if ($data = $this->_memcache->get($_SERVER['SERVER_NAME'] . $cacheLabel)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @return bool
     */
    public function flush()
    {
        if ($this->_memcache) {
            $this->_memcache->flush();
        }
        return true;
    }
}