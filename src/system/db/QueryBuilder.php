<?php
namespace system\db;

/**
 * Class querybuilder is an abstraction which allows to represent the DB query as an object
 * @package system\db
 */
abstract class QueryBuilder
{
    protected $_from = array();
    protected $_fields = array();
    protected $_joins = array();
    protected $_where = array();
    protected $_whereParams = array();
    protected $_order = array();
    protected $_group = array();
    protected $_limit;
    protected $_having;

    protected $_calcFoundRows = false;

    protected $_lastBuiltQuery;

    /**
     * builds the final query basing on the filled params
     * @return string
     */
    abstract public function build();

    /**
     * adds table to the query and optionally fields to fetch data from
     * @param $baseTable
     * @param null $fields
     * @return $this|QueryBuilder
     */
    public function from($baseTable, $fields = null)
    {
        if (is_array($baseTable)) {
            foreach ($baseTable as $alias => $tableName) {
                if (!is_numeric($alias)) {
                    $this->_from['baseTable']['alias'] = $alias;
                } else {
                    $this->_from['baseTable']['alias'] = $tableName;
                }
                $this->_from['baseTable']['name'] = $tableName;
            }
        } elseif (is_string($baseTable)) {
            $this->_from['baseTable']['name'] = $baseTable;
        }
        $this->addFileds($fields);

        return $this;
    }

    /**
     * adds fields to the query
     * @param $fields
     * @return $this|QueryBuilder
     */
    public function addFileds($fields)
    {
        // поля, которые нужно выбрать
        if ($fields) {
            foreach ($fields as $alias => $name) {
                if (!is_numeric($alias)) {
                    $this->_fields[$alias] = $name;
                } else {
                    $this->_fields[] = $name;
                }
            }
        }

        return $this;
    }

    /**
     * @desc перезаписывает поля, которые нужно выбирать из базы
     * @param array
     * @return QueryBuilder
     */
    public function setFields($fields)
    {
        if ($fields) {
            $this->_fields = array();
            foreach ($fields as $alias => $name) {
                if (!is_numeric($alias)) {
                    $this->_fields[$alias] = $name;
                } else {
                    $this->_fields[] = $name;
                }
            }
        }

        return $this;
    }

    /**
     * @desc добавляет условие JOIN в запрос
     * @param string тип объеденения
     * @param mixed array | string
     * @param string
     * @return QueryBuilder
     */
    public function join($joinType, $joinTable, $joinOn)
    {
        if (!in_array($joinType, array('inner', 'left', 'right'))) {
            return false;
        }

        $joinArray = array();
        if (is_array($joinTable)) {
            foreach ($joinTable as $alias => $name) {
                if (!is_numeric($alias)) {
                    $joinArray['alias'] = $alias;
                } else {
                    $joinArray['alias'] = $name;
                }
                $joinArray['tableName'] = $name;
            }
        } elseif (is_string($joinTable)) {
            $joinArray['alias'] = '';
            $joinArray['tableName'] = $joinTable;
        }
        $joinArray['joinCondition'] = $joinOn;
        $joinArray['joinType'] = $joinType;
        $this->_joins[] = $joinArray;

        return $this;
    }

    /**
     * @desc добвляет условие WHERE
     * @param string
     * @return QueryBuilder
     */
    public function where($whereCondition)
    {
        if (is_array($whereCondition)) {
            foreach ($whereCondition as $field => $value) {
                $this->_whereParams[$field] = $value;
            }
        } elseif (is_string($whereCondition)) {
            $this->_where[] = $whereCondition;
        }
        return $this;
    }

    /**
     * @desc adds sorting condition
     * @param mixed string | array
     * @return QueryBuilder
     */
    public function order($orderData)
    {
        if (is_array($orderData)) {
            foreach($orderData as $order) {
                $this->_order[] = $order;
            }
        } elseif (is_string($orderData)) {
            $this->_order[] = $orderData;
        }

        return $this;
    }

    /**
     * @desc adds group condition
     * @param mixed string | array
     * @return QueryBuilder
     */
    public function group($groupData)
    {
        if (is_array($groupData)) {
            foreach($groupData as $group) {
                $this->_group[] = $group;
            }
        } elseif (is_string($groupData)) {
            $this->_group[] = $groupData;
        }

        return $this;
    }

    /**
     * @desc добавление лимита
     * @param int
     * @param int
     * @return QueryBuilder
     */
    public function limit($offset, $amount)
    {
        if ($amount > 0) {
            $this->_limit = $offset . ", " . $amount;
        }

        return $this;
    }

    public function having($expr)
    {
        if (!empty($expr)) {
            $this->_having = $expr;
        }

        return $this;
    }

    /**
     * @desc обеспечивает отсутствие экранирования для данного аргумента
     * @param string
     * @return string
     */
    public function expression($arg)
    {
        return "{unchangable}." . $arg;
    }

    /**
     * Magic method to be called when object is used as a string
     * @return string
     */
    public function __toString()
    {
        return $this->build();
    }

    /**
     * cleans all the filled fields
     * @return bool
     */
    public function clean()
    {
        /**
         * too expensive
         *
        $reflect = new \ReflectionClass($this);
        $props = $reflect->getProperties();
        foreach ($props as $prop) {
            $propname = $prop->name;
            $this->$propname = array();
        }
        */
        $this->_fields =
        $this->_from =
        $this->_group =
        $this->_where =
        $this->_whereParams =
        $this->_joins =
        $this->_order = array();

        return true;
    }

    /**
     * returns last successfully built query
     * @return mixed
     */
    public function getLastBuiltQuery()
    {
        return $this->_lastBuiltQuery;
    }

    /**
     * @param $calcRowsState
     */
    public function setCalculateFoundRows($calcRowsState)
    {
        $this->_calcFoundRows = (bool)$calcRowsState;
    }
}