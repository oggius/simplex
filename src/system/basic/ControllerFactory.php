<?php
namespace system\basic;

use system\App;
use system\basic\exceptions\MissingClassException;

/**
 * Class ControllerFactory
 * @package system\basic
 */
class ControllerFactory {

    /**
     * @var BaseError
     */
    protected $_error;

    /**
     * @param $controllerName
     * @param App $app
     * @return BaseController | null
     */
    public function buildController($controllerName, App $app)
    {
        // error must represent the result of current operation, not the previous one
        $this->_error = null;

        $controllerClassName = '\\application\\controllers\\' . ucfirst($controllerName) . 'Controller';
        try {
            $controller = new $controllerClassName($app);
            if ($controller instanceof BaseController) {
                return $controller;
            } else {
                $this->_error = new BaseError(
                    'ControllerFactory: Controller {' . $controllerName . '} must implement BaseController abstraction',
                    503
                );
            }
        } catch (MissingClassException $e) {
            $this->_error = new BaseError(
                'ControllerFactory: Controller {' . $controllerName . '} is missing',
                404
            );
        }

        return null;
    }

    /**
     * @return BaseError
     */
    public function getError()
    {
        return $this->_error;
    }
}