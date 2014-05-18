<?php
namespace system\cache;


abstract class CacheDriver
{
    /**
     * inits cache object and configures it with $config data
     * @param array $config
     * @return mixed
     */
    abstract public function init(array $config);

    /**
     * checks validity of cache
     * @param $cacheLabel
     * @return mixed
     */
    abstract public function test($cacheLabel);

    /**
     * saves data into cache
     * @param $cacheLabel
     * @param $cacheData
     * @param null $expire
     * @return bool
     */
    abstract public function save($cacheLabel, $cacheData, $expire = null);

    /**
     * loads data from cache
     * @param $cacheLabel
     * @return mixed
     */
    abstract public function load($cacheLabel);

    /**
     * clear all stored cache
     * @return bool
     */
    abstract public function flush();

    /**
     * alias for save()
     * @param $cacheLabel
     * @param $cacheData
     * @param null $expire
     * @return bool
     */
    public function write($cacheLabel, $cacheData, $expire = null)
    {
        return $this->save($cacheLabel, $cacheData, $expire);
    }

    /**
     * alias for load()
     * @param $cacheLabel
     * @return mixed
     */
    public function read($cacheLabel)
    {
        return $this->load($cacheLabel);
    }
}