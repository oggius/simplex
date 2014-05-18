<?php
namespace system\basic;

/**
 * Class Registry implements global data repository
 * @package system\basic
 */
class Registry
{
    /**
     * @var Registry
     */
    private static $_instance;

    /**
     * @var array
     */
    private $_data;
    
    /**
    * @desc запрещаем использование конструктора и клона
    */
    private function __construct() {}
    
    private function __clone() {}
    
    /**
    * @desc Метод получения текущего экземпляра
    */
    public static function getInstance() 
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }
    
    /**
    * @desc сохранение данных в хранилище
    * @param string ключ, по которому данные будут храниться
    * @param mixed данные 
    * @return bool
    */
    private function _setData($dataKey, $data) 
    {
        return $this->_data[$dataKey] = $data;
    }
    
    /**
    * @desc добавление данных по уже существующему ключу
    */
    private function _addData($dataKey, $dataValue) 
    {
        $type = gettype($dataValue);
        if ($this->_checkData($dataKey)) {
            $result = array_push($this->_data[$dataKey], $dataValue);
        } else {
            $result = $this->_setData($dataKey, (array)$dataValue);
        }
        return $result;
    }
    
    /**
    * @desc получение данных из регистра по ключу
    * @param string ключ для выборки
    */
    private function _getData($dataKey) 
    {
        if ($this->_checkData($dataKey)) {
            $result = $this->_data[$dataKey];
        } else {
            $result = null;
        }
        
        return $result;
    }
    
    /**
    * @desc проверка данных на наличие в хранилище по ключу. Дополнительно можно проверять по типу данных
    * @param string ключ для проверки
    * @param string тип данных
    * @return bool
    */
    private function _checkData($dataKey, $dataType = null) 
    {
        if (isset($this->_data[$dataKey])) {
            if (!is_null($dataType)) {
                if (gettype($this->_data[$dataKey]) == $dataType) {
                    $result = true;
                } else {
                    $result = false;
                }
            } else {
                $result = true;
            }
        } else {
            $result = false;
        }
        
        return $result;
    }
    
    /**
    * @desc сохраняет данные по указанному ключу
    * @param string
    * @param mixed
    */
    public static function set($dataKey, $dataValue) 
    {
        return self::getInstance()->_setData($dataKey, $dataValue);
    }
    
    /**
    * @desc публичный метод для добавления данных по ключу.
    *       Проверяет существование данных по такому ключу, 
    *       и если данные есть, добавляет в массив
    * @param string
    * @param mixed
    */
    public static function add($dataKey, $dataValue) 
    {
        return self::getInstance()->_addData($dataKey, $dataValue);        
    }
    
    /**
    * @desc публичный метод для получения данных из реестра
    */
    public static function get($dataKey)
    {
        return self::getInstance()->_getData($dataKey);
    }
}