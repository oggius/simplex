<?php
namespace system\db\querybuilder;
use system\db\QueryBuilder;

/**
 * Class MysqliQueryBuilder gives a wrapper to represent the query as an object
 * @package system\db\querybuilder
 */
class MysqliQueryBuilder extends QueryBuilder
{
    /**
     * builds the result query
     * @return string
     */
    public function build()
    {
        $fromFields = array();
        if (count($this->_fields) > 0) {
            foreach ($this->_fields as $alias => $name) {
                $name = strpos($name, '{unchangable}.') === false ? "`" . str_replace(".", "`.`", $name) . "`" : str_replace("{unchangable}.", "", $name);
                if (!is_numeric($alias)) {
                    $fromFields[] = $name . " AS `$alias`";
                } else {
                    $fromFields[] = $name;
                }
            }
        } else {
            $fromFields[] = "*";
        }

        $joins = array();
        foreach ($this->_joins as $join) {
            $j = "";
            $j .= mb_strtoupper($join['joinType']) . " JOIN ";
            $j .= "`" . $join['tableName'] . "`" . ($join['alias'] ? " AS `" . $join['alias'] . "` " : " ");
            $j .= " ON (" . $join['joinCondition'] . ")";
            $joins[] = $j;
        }

        $query = "SELECT " . ($this->_calcFoundRows ? "SQL_CALC_FOUND_ROWS " : " ")
               . implode(", ", $fromFields) . " FROM `" . $this->_from['baseTable']['name'] . "` ";
        if(isset($this->_from['baseTable']['alias'])) $query .= ($this->_from['baseTable']['alias'] && $this->_from['baseTable']['alias'] != $this->_from['baseTable']['name']) ? " AS `" . $this->_from['baseTable']['alias'] . "`" : " ";
        if (count($joins) > 0) {
            $query .= " " . implode(" ", $joins);
        }
        $query .= " WHERE 1 ";
        if (count($this->_whereParams) > 0) {
            foreach($this->_whereParams as $field => $value) {
                $this->_where[] = "`" . $field . "` = '" . $value . "'";
            }
        }
        if (count($this->_where) > 0) {
            $query .= " AND " . implode(" AND ", $this->_where);
        }
        if (count($this->_group) > 0) {
            $query .= " GROUP BY " . implode(", ", $this->_group);
        }
        if (!empty($this->_having)) {
            $query .= " HAVING " . $this->_having;
        }
        if (count($this->_order) > 0) {
            $query .= " ORDER BY " . implode(", ", $this->_order);
        }
        if (!empty($this->_limit)) {
            $query .= " LIMIT " . $this->_limit;
        }

        $this->_lastBuiltQuery = $query;
        $this->clean();
        return $query;
    }
}