<?php
namespace system\basic;

use \system\App;
use \system\basic\exceptions\WrongActionException;
use \system\basic\View;
use \system\basic\view\TemplateEngineFactory;

/**
 * Class BaseController
 * @package system\basic
 * @property View $view
 */
abstract class BaseController
{
    /**
     * @var App
     */
    protected $_app;

    /**
     * @var View
     */
    protected $_view;

    /**
     * @var array get parameters storage
     */
    protected $_params;

    /**
     * construct. Cannot be overriden
     * @param App $application
     */
    final public function __construct(App $application)
    {
        $this->_app = $application;
    }

    /**
     * inits the view component of MVC pattern
     * @return void
     */
    public function initView()
    {
        $this->_view = new View(
            TemplateEngineFactory::factory( Config::getSection('templateengine') )
        );
    }

    /**
     * send all the rendered content (if any) to the Output
     * @return void
     */
    final public function display()
    {
        if (!$this->_app->getResponse()->hasOutput() && $this->_view) {
            $this->_app->getResponse()->setOutput( $this->_view->getRenderedContent() );
        }
    }

    public function callAction($actionName)
    {
        $methodName = $actionName . 'Action';
        if (method_exists($this, $methodName)) {
            $this->$methodName();
        } else {
            throw new WrongActionException('Action `' . $actionName . '` is not defined in class ' . get_called_class(), 404 );
        }
    }

    public function __get($paramName)
    {
        if ($paramName === 'view') {
            if (is_null($this->_view)) {
                $this->initView();
            }
            return $this->_view;
        } else {
            return null;
        }
    }

    /**
     * provides convenient way to get the input GET params
     * @param $paramName
     * @param null $default
     * @return null
     */
    public function getParam($paramName, $default = null)
    {
        if (is_null($this->_params)) {
            $this->_params = $this->_app->getRouter()->getParams();
        }

        if (isset($this->_params[$paramName])) {
            return $this->_params[$paramName];
        } else {
            return $default;
        }
    }

    /**
     * returns list of input params detected by router
     * @return array
     */
    public function getParams()
    {
        if (is_null($this->_params)) {
            $this->_params = $this->_app->getRouter()->getParams();
        }
        return $this->_params;
    }

    /**
     * returns section of input params
     * @param $section
     * @return array
     */
    public function getInputSection($section)
    {
        return $this->_app->getRequest()->getParams($section);
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->_app->getRequest();
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->_app->getResponse();
    }

    /**
     * method called before the main action is called
     */
    public function _preDispatch() {
        Session::init();
        date_default_timezone_set( Config::getSectionParam('general', 'default_timezone'));
    }

    /**
     * method called after the main action is called
     */
    public function _postDispatch() {}
}
