<?php
namespace system\pagination;

/**
 * Class PaginationItem
 * @package system\pagination
 */
class PaginationItem implements \ArrayAccess
{
    /**
     * @var array
     */
    private $_params = array();

    /**
     * @var string
     */
    private static $_defaultLink = '#';

    public function __construct($type, $value, $link = null, $active = false)
    {
        $this->_params = array(
            'type' => $type,
            'value' => $value,
            'link' => $link ? $link : self::$_defaultLink,
            'active' => $active
        );
    }

    /**
     * magic getter
     * @param $paramName
     * @return mixed
     */
    public function __get($paramName)
    {
        if (array_key_exists($paramName, $this->_params)) {
            return $this->_params[$paramName];
        }
    }

    /**
     * we cannot set elements from client
     *
     */
    public function offsetSet($offset, $value) {
        return;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->_params[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset) {
        unset($this->_params[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset) {
        return isset($this->_params[$offset]) ? $this->_params[$offset] : null;
    }
}