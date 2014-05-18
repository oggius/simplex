<?php
namespace system;

use system\basic\BaseErrorController;
use system\basic\ControllerFactory;
use system\basic\EmergencyHandler;
use system\basic\exceptions\BadClassInstantiationException;
use system\basic\Registry;
use \system\basic\Request;
use \system\basic\Response;
use \system\basic\Router;
use \system\basic\Config;
use \system\basic\exceptions\MissingClassException;
use system\basic\exceptions\BaseException;
use system\cache\CacheFactory;
use system\db\DbDriverFactory;
use system\logger\LoggerFactory;

/**
 * Class App is the main application class. It inits the whole app
 *
 * @package system
 */
class App
{
    /**
     * @var App
     */
    private static $_instance;

    /**
     * @var Request
     */
    private $_request;

    /**
     * @var Response
     */
    private $_response;

    /**
     * @var Router
     */
    private $_router;

    private function __construct(){}

    private function __clone(){}

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
            self::$_instance->_init();
        }
        return self::$_instance;
    }

    private function _init()
    {

        $this->_request = Request::getInstance();
        $this->_response = Response::getInstance();
        $this->_router = Router::getInstance();
    }

    private function _dispatch($controllerName, $actionName)
    {
        $controllerFactory = new ControllerFactory();
        $controller = $controllerFactory->buildController($controllerName, $this);
        if (($controllerCreationError = $controllerFactory->getError()) === null) {
            $controller->_preDispatch();
            $controller->callAction($actionName);
            $controller->_postDispatch();
            $controller->display();
        } else {
            throw new MissingClassException(
                $controllerCreationError->getErrorMessage(),
                $controllerCreationError->getErrorCode()
                );
        }
    }

    /**
     * @param array $autoloadConfig
     * @todo refactor, make automated and configurable
     * @return void
     */
    private function _loadModules(array $autoloadConfig)
    {
        if (in_array('database', $autoloadConfig)) {
            try {
                $databaseDriver = DbDriverFactory::factory( Config::getSectionParam('db', 'driver') );
                // store autoloaded driver to the database
                Registry::set('dbdriver', $databaseDriver);
            } catch (BadClassInstantiationException $e) {
                //die($e->getMessage());
            }
        }
        if (in_array('cache', $autoloadConfig)) {
            try {
                $cacheDriver = CacheFactory::factory( Config::getSectionParam('cache', 'driver') );
                Registry::set('cache', $cacheDriver);
            } catch(BadClassInstantiationException $e) {
                die( $e->getMessage() );
            }
        }
        if (in_array('logger', $autoloadConfig)) {
            try {
                $loggerAdapter = LoggerFactory::factory( Config::getSectionParam('logger', 'destination'));
                Registry::set('logger', $loggerAdapter);
            } catch (BadClassInstantiationException $e) {
                die ( $e->getMessage() );
            }
        }
    }

    /**
     *
     */
    public function run()
    {
        try {
            /**
             * start collection all the output information into the output buffer
             */
            $this->_response->startOutput();
            /**
             * start processing the incoming request
             */
            $this->_request->processRequest();
            /**
             * set the routes for the Router
             */
            $this->_router->setRoutingConfig(Config::getSection('routes', false));

            /**
             * start routing process. Define Controller, Action and action params
             */
            $this->_router->route($this->_request);

            /**
             * autoload
             */
            $this->_loadModules(Config::getSection('autoload'));

            /**
             * dispatch the call to the corresponding controller and its action
             */
            $this->_dispatch($this->_router->getController(), $this->_router->getAction());

            /**
             * end up displaying what we have just rendered
             */
            $this->_response->getOutput();

        } catch (BaseException $applicationException) {
            /**
            /* something went wrong in our app, lets try to render a corresponding fancy error page
             */
            try {
                $errorController = new BaseErrorController($this);
                $errorController->renderErrorPageAction($applicationException);
                $errorController->display();
                $this->_response->getOutput();
            } catch (\Exception $lastException) {
                /**
                /* can not render anything user-friendly? Now it's emergency handler show time
                 */
                $emergencyHandler = new EmergencyHandler();;
                $emergencyHandler->registerShutdownHandlers();
                $emergencyHandler->processUncaughtException($lastException);
            }
        }
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->_router;
    }
}