<?php
namespace system\db;

use system\basic\BaseModel;
use system\basic\exceptions\DatabaseException;

/**
 * Class ActiveRecord
 * @package system\db
 */
abstract class ActiveRecord extends BaseModel {
    /**
     * @var string corresponding database table
     */
    protected $_table;

    /**
     * @var string primary key of the object in the database
     */
    protected $_key = 'id';

    /**
     * @var array
     */
    protected $_params = array();

    /**
     * @var array
     */
    protected $_extraParams = array();

    /**
     * @var array data schema for the model
     */
    protected static $_schema = array();

    /**
     * constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * @param $key
     * @return ActiveRecord
     */
    public function find($key) {
        return $this->findByParams( array($this->_key => $key) );
    }

    /**
     * @param array $params
     * @return ActiveRecord $this
     */
    public function findByParams(array $params) {
        $queryBuilder = $this->db->getQueryBuilder();
        $queryBuilder->from($this->_table)
                     ->where($params);
        try {
            $data = $this->db->fetchRow($queryBuilder);
        } catch (DatabaseException $e) {
            // log exception message here
        }
        if (!empty($data)) {
            $this->_params = $data;
        }
        return $this;
    }

    /**
     * updates the object in the database
     * @return bool
     */
    public function update()
    {
        if (!$this->isVoid()) {
            $result = $this->db->update(
                                    $this->_table,
                                    $this->_params,
                                    array($this->_key => $this->_params[$this->_key])
                                 );
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * inserts new object into the database
     * @return mixed
     */
    public function insert()
    {
        if (!$this->isVoid()) {
            $insertResult = $this->db->insert(
                                $this->_table,
                                $this->_params
                            );
            if ($insertResult) {
                $result = $this->db->lastInsertId();
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * deletes corresponding row from the database
     * @return bool
     */
    public function delete() {
        if (!$this->isVoid()) {
            $result = $this->db->delete($this->_table, array($this->_key => $this->_params[$this->_key]) );
            unset($this->_params);
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * Populates the object with the outer data
     * @param array $params
     * @return ActiveRecord
     */
    public function populate(array $params)
    {
        $paramsCheck = true;
        foreach($params as $paramName => $paramValue) {
            if (!$this->_isValidParam($paramName)) {
                $paramsCheck = false;
                break;
            }
        }
        // if any of params is invalid, abort the population of object
        if ($paramsCheck) {
            $this->_params = $params;
        }
        return $this;
    }

    /**
     * Magic method get
     * @param $paramName
     * @return mixed
     */
    public function __get($paramName)
    {
        if (array_key_exists($paramName, $this->_params)) {
            return $this->_params[$paramName];
        } elseif (array_key_exists($paramName, $this->_extraParams)) {
            return $this->_extraParams[$paramName];
        } else {
            return null;
        }
    }

    /**
     * Magic method SET
     * @param $paramName
     * @param $paramValue
     * @return null
     */
    public function __set($paramName, $paramValue)
    {
        // check correspondence of param name and schema
        if ($this->_isValidParam($paramName)) {
            $this->_params[$paramName] = $paramValue;
        }
    }

    /**
     * Magic method isset
     * @param $paramName
     * @return bool
     */
    public function __isset($paramName)
    {
        return (array_key_exists($paramName, $this->_params) && $this->_params[$paramName] !== null);
    }

    /**
     * checks if the object is populated correctly
     * @return bool
     */
    public function isVoid()
    {
        return empty($this->_params);
    }

    /**
     * fills the object scheme
     * @throws \system\basic\exceptions\DatabaseException
     */
    public function setSchema()
    {
        if (empty(static::$_schema[$this->_table])) {
            $schema = $this->cache->load($this->_table . '_schema');
            if ($schema) {
                static::$_schema[$this->_table] = $schema;
            } else {
                $data = $this->db->fetchAll("DESCRIBE " . $this->_table);
                if ($data) {
                    foreach ($data as $datarow) {
                        static::$_schema[$this->_table][$datarow['Field']] = array(
                                                                  'type' => $datarow['Type'],
                                                                  'null' => $datarow['Null'] === 'No' ? false : true
                                                              );
                    }
                    $this->cache->save($this->_table . '_schema', static::$_schema[$this->_table]);
                } else {
                    throw new DatabaseException("Failed to load schema of the `" . $this->_table . "` table");
                }
            }
        }
    }

    /**
     * @param $paramName
     * @param null $paramValue
     * @todo add param value check for correct type
     * @return bool
     */
    protected function _isValidParam($paramName, $paramValue = null)
    {
        $this->setSchema();
        if (!empty(static::$_schema[$this->_table])) {
            if (array_key_exists($paramName, static::$_schema[$this->_table])) {
                if ($paramValue !== null) {
                    // add param type check
                    return true;
                } else {
                    return true;
                }
            } else {
                return false;
            }
        } else {
            // if no schema set, any parameter should be considered as valid
            return true;
        }
    }

    /**
     * returns object params as array
     * @return array
     */
    public function getParams()
    {
        return array_merge($this->_params, $this->_extraParams);
    }

    /**
     * allows to add extra params without scheme validation
     * @param $paramName
     * @param $paramValue
     * @return bool
     */
    public function setExtra($paramName, $paramValue)
    {
        // extra param must not be present in the scheme
        if (array_key_exists($paramName, $this->_params)) {
            return false;
        }

        $this->_extraParams[$paramName] = $paramValue;
        return true;
    }
}