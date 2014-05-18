<?php 
namespace system\basic;

use \system\basic\view\TemplateEngineAdapter;

/**
 * Class View
 * @package system\basic
 */
class View
{
    /**
     * @var TemplateEngineAdapter
     */
    private $_engine;

    private $_renderResult;

    private $_data = array();

    public function __construct(TemplateEngineAdapter $engine)
    {
        $this->_engine = $engine;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function assignArray(array $data)
    {
        if ($this->_data = array_merge($this->_data, $data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * assign value by name
     * @param $name
     * @param $value
     * @return bool
     */
    public function assignValue($name, $value)
    {
        if (!is_scalar($name)) {
            return false;
        }
        $this->_data[$name] = $value;
    }


    /**
     * @param $templateName
     * @param bool $return
     * @return mixed
     */
    public function render($templateName, $return = false)
    {
        $this->_engine->assign( $this->_data );
        $renderResult = $this->_engine->render($templateName);
        if (!$return) {
            $this->_renderResult = $renderResult;
        } else {
            return $renderResult;
        }
    }

    /**
     * @return mixed
     */
    public function getRenderedContent()
    {
        return $this->_renderResult;
    }

    /**
     * @return string
     */
    public function getViewsPath()
    {
        return ROOT . 'application/views/';
    }
}