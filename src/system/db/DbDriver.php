<?php
namespace system\db;

/**
 * Class DbDriver
 * @package system\db
 */
abstract class DbDriver {
    /**
     * @var resource active connection
     */
    protected $_connection;

    /**
     * @var resource query execution result
     */
    protected $_res;

    /**
     * @var bool turn on/off profiling
     */
    protected $_profile = false;

    /**
     * @var int total number of rows for the select query with limit ignoring
     */
    protected $_totalRows;

    /**
     * @var connection config
     */
    protected $_config;

    /**
     * @param array $dbconfig
     */
    public function __construct(array $dbconfig)
    {
        $this->_config = $dbconfig;
    }

    abstract protected function setCorrectTypes($query, $dataRow);

    abstract public function connect();

    abstract public function insert($table, $params);

    abstract public function insertIgnore($table, $params);

    abstract public function update($table, $params, $conditions = null);

    abstract public function delete($table, $conditions = null);

    abstract public function query($query, $log = false);

    abstract public function fetchOne($query, $preserveTypes = false);

    abstract public function fetchRow($query, $preserveTypes = false);

    abstract public function fetchAll($query, $preserveTypes = false);

    abstract public function fetchPairs($query, $preserveTypes = false);

    abstract public function fetchCol($query, $preserveTypes = false);

    abstract public function escape($query);

    abstract public function countRows($res = null, $ignoreLimit = false);

    abstract public function lastInsertId();

    abstract public function getAffectedRowsCount();

    /**
     * @return QueryBuilder
     */
    abstract public function getQueryBuilder();
}