<?php
namespace system\db\driver;

use system\basic\exceptions\DatabaseException;
use system\db\DbDriver;
use system\db\querybuilder\MysqliQueryBuilder;

class MysqliDbDriver extends DbDriver
{
    /**
     * @var \mysqli_result
     */
    protected $_res;

    /**
     * @var \mysqli
     */
    protected $_connection;

    /**
     * @return bool
     * @throws \system\basic\exceptions\DatabaseException
     */
    public function connect()
    {
        if (!$this->_connection) {
            $this->_connection = mysqli_connect(
                $this->_config['host'],
                $this->_config['login'],
                $this->_config['password'],
                $this->_config['database']
            );
            if (mysqli_connect_error()) {
                throw new DatabaseException('Couldn\'t connect to database ' . $this->_config['database'] . ' on host ' . $this->_config['host']);
            }
            $this->query("SET NAMES 'UTF8'");
        }
        return true;
    }

    /**
     * @param $query
     * @param bool $log
     * @return \mysqli_result
     * @throws \system\basic\exceptions\DatabaseException
     */
    public function query($query, $log = false) {
        $this->connect();
        // если включено профилирование, логгируем запрос и время его выполнения
        if ($this->_profile || $log === true) {
            //Logger::getInstance()->log($query);
            $time1 = microtime(true);            
            $this->_res = mysqli_query($this->_connection, $query);
            $time2 = microtime(true);
            $executionTime = $time2 - $time1;
            //Logger::getInstance()->log('Execution time: ' . $executionTime);
            if ($executionTime > 0.1) {
                //Logger::getInstance()->log($query . "\n\n"  . 'Execution time: ' . $executionTime, 'slow_queries');
            }            
        } else {
            $this->_res = mysqli_query($this->_connection, $query);
        }
        // calculate affected row for select with SQL_CALC_FOUND_ROWS query
        if (strpos($query, 'SQL_CALC_FOUND_ROWS') !== false) {                
            $res = $this->_res;
            $this->_totalRows = $this->fetchOne( "SELECT FOUND_ROWS()" );
            $this->_res = $res;
        }
        // log the error message if the result is false
        if ($this->_res == false) {
            //Logger::getInstance()->log($query . ' ' . mysqli_error($this->_connection), 'error_queries');
            throw new DatabaseException(
                        'Database query ' . $query . ' failed with error ' . mysqli_error($this->_connection)
                      );
        }
        // log the query
        /*
        if ($this->_res) {
            $logger = LoggerFactory::factory('file');
            $logger->log($query, 'queries');
        }
        */
        return $this->_res;
    }

    /**
     * returns the value of the first column in the select query
     * @param $query
     * @param bool $preserveTypes
     * @return bool
     */
    public function fetchOne($query, $preserveTypes = false) {
        $q = (string)$query;
        $r = $this->query($q);

        if (empty($r)) {
            return false;
        }
        $row = mysqli_fetch_row($r);
        if ($row) {
            if ($preserveTypes) {
                $row = $this->setCorrectTypes($r, $row);
            }
            $result = $row[0];
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * Returns row as associative array
     * @param $query QueryBuilder | string
     * @param bool $preserveTypes
     * @return array|bool
     */
    public function fetchRow($query, $preserveTypes = false) {
        $q = (string)$query;
        $r = $this->query($q);

        if (empty($r)) {
            return false;
        }
        
        $row = mysqli_fetch_assoc($r);
        if ($row) {
            if ($preserveTypes) {
                $result = $this->setCorrectTypes($r, $row);
            } else {
                $result = $row;
            }
        } else {
            $result = false;
        }
        return $result;        
    }

    /**
     * returns values in the column
     * @param $query QueryBuilder|string
     * @param bool $preserveTypes
     * @return array|bool
     */
    public function fetchCol($query, $preserveTypes = false) {
        $q = (string)$query;
        $r = $this->query($q);

        if (empty($r)) {
            return false;
        }
        $result = array();
        
        while($row = mysqli_fetch_array($r)) {
            if ($preserveTypes) {
                $row = $this->setCorrectTypes($r, $row);
            }
            $result[] = $row[0];
        }
        return $result;        
    }
    
    /**
    * @desc выдает результат запроса в виде ассоциативного массива, где ключи - это значения первой колонки, а значения - значения второй колонки
    * @param string
    * @param bool
    * @return array
    */
    public function fetchPairs($query, $preserveTypes = false)
    {
        $q = (string)$query;
        $r = $this->query($q);
        
        if (empty($r)) {
            return false;
        }
        $result = array();
        
        while($row = mysqli_fetch_array($r)) {
            $result[$row[0]] = $row[1];
        }
        return $result;        
    }

    /**
     * @param $query QueryBuilder | string
     * @param bool $preserveTypes
     * @param bool $idKey if true then array keys will correspond to primary key of the result dataset
     * @return array|bool
     */
    public function fetchAll($query, $preserveTypes = false, $idKey = false)
    {
        $q = (string)$query;
        $r = $this->query($q);

        if (empty($r)) {
            return false;
        }
        $result = array();
        
        $i = 0;        
        while($row = mysqli_fetch_assoc($r)) {
            if ($idKey && !is_null($row['id'])) {
                $key = $row['id'];
            } else {
                $key = $i;
            }
            if ($preserveTypes) {
                $result[$key] = $this->setCorrectTypes($r, $row);
            } else {
                $result[$key] = $row;
            }
            $i++;
        }
        return $result;
    }

    protected function setCorrectTypes($res, $dataRow) {
        $fieldInfo = mysqli_fetch_fields($res);
        foreach ($fieldInfo as $fld) {
            switch ($fld->type) {
                case MYSQLI_TYPE_LONG:
                case MYSQLI_TYPE_LONGLONG:
                case MYSQLI_TYPE_INT24:
                case MYSQLI_TYPE_SHORT:
                    $dataRow[$fld->name] = (int)$dataRow[$fld->name];
                    break;
                case MYSQLI_TYPE_FLOAT:
                case MYSQLI_TYPE_DECIMAL:
                case MYSQLI_TYPE_DOUBLE:
                case MYSQLI_TYPE_NEWDECIMAL:
                    $dataRow[$fld->name] = (float)$dataRow[$fld->name];
                    break;
                default : 
                    $dataRow[$fld->name] = (string)$dataRow[$fld->name];
                    break;
            }
        }
        return $dataRow;
    }

    /**
     * Escapes query params
     * @param $strValue
     * @return string
     */
    public function escape($strValue)
    {
        $this->connect();
        return mysqli_real_escape_string($this->_connection, $strValue);
    }

    /**
     * returns rows count of the result dataset
     * @param null $res
     * @param bool $ignoreLimit
     * @return bool|int
     * @throws \system\basic\Exceptions\DatabaseException
     */
    public function countRows($res = null, $ignoreLimit = false) {
        if ($ignoreLimit) {
            if (!empty($this->_totalRows)) {
                $result = intval($this->_totalRows);
            } else {
                $result = 0;
            }
            return $result;
        } else {
            // if no resourse given as param, use the $this->_res property
            if ($res === null) {
                if ($this->_res) {
                    $result = mysqli_num_rows($this->_res);
                } else {
                    throw new DatabaseException('Wrong resource given for row count operation');
                }
            } elseif (is_resource($res) || $res instanceof \mysqli_result) {
                $result = mysqli_num_rows($res);
            } else {
                $result = false;
            }
            return $result;
        }
    }

    /**
     * @param $table
     * @param $data
     * @param null $condition
     * @return bool|\mysqli_result
     */
    public function update($table, $data, $condition = null) {
        $updQuery = array();
        foreach ($data as $key => $val) {
            if (is_null($val)) {
                $updQuery[] = "`" . $key . "` = NULL";                
            } else {
                $updQuery[] = "`" . $key . "` = '" . $this->escape($val) . "'";
            }
        }
        
        $query = "UPDATE `" . $table . "` SET " . implode(', ', $updQuery) . " WHERE 1";
        
        if (!empty($condition)) {
            if (is_string($condition)) {
                $query .= " AND " . $condition;
            } elseif (is_array($condition)) {
                foreach($condition as $paramName => $paramValue) {
                    if ($paramValue === null) {
                        $query .= " AND `" . $paramName . "` IS NULL";
                    } else {
                        $query .= " AND `" . $paramName . "` = '" . $this->escape($paramValue) . "'";
                    }
                }
            }
        }
        
        $result = $this->query($query);
        return $result;
    }

    /**
     * prepares data for the SQL INSERT operation
     * @param array $data
     * @return array
     */
    private function _prepareInsertParams(array $data)
    {
        $updQuery = array();
        foreach ($data as $key => $val) {
            if (is_null($val)) {
                $updQuery[] = "`" . $key . "` = NULL";
            } else {
                $updQuery[] = "`" . $key . "` = '" . $this->escape($val) . "'";
            }
        }
        return $updQuery;
    }
    /**
     * Database INSERT helper method
     * @param $table
     * @param $data
     * @return bool|\mysqli_result
     */
    public function insert($table, $data) {
        $updQuery = $this->_prepareInsertParams($data);
        $query = "INSERT INTO `" . $table . "` SET " . implode(', ', $updQuery);
        $result = $this->query($query);
        return $result;        
    }

    /**
     * @param $table
     * @param $data
     * @return bool|\mysqli_result
     */
    public function insertIgnore($table, $data)
    {
        $updQuery = $this->_prepareInsertParams($data);
        $query = "INSERT IGNORE INTO `" . $table . "` SET " . implode(', ', $updQuery);
        $result = $this->query($query);
        return $result;
    }

    /**
     * Database DELETE operation helper
     * @param $table
     * @param null $conditions
     * @return bool
     */
    public function delete($table, $conditions = null)
    {
        $query = "DELETE FROM `" . $table . "` WHERE 1";

        if (is_string($conditions)) {
            $query .= " AND " . $conditions;
        } elseif (is_array($conditions)) {
            foreach ($conditions as $field => $value) {
                $query .= " AND `" . $field . "` = '" . $this->escape($value) . "'";
            }
        }
        return $this->query($query);
    }

    /**
     * returns last inserted row id
     * @return int|string
     */
    public function lastInsertId() {
        return mysqli_insert_id($this->_connection);
    }

    /**
     * @return MysqliQueryBuilder
     */
    public function getQueryBuilder()
    {
        return new MysqliQueryBuilder();
    }

    /**
     * returns count of the affected rows by previous insert, update, delete, replace
     * @return int
     */
    public function getAffectedRowsCount()
    {
        return mysqli_affected_rows($this->_connection);
    }


}