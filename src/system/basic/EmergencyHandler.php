<?php
namespace system\basic;

/**
 * Class EmergencyHandler
 * @package system\basic
 */
class EmergencyHandler {

    /**
     * registers the shutdown function
     */
    public function registerShutdownHandlers()
    {
        register_shutdown_function(array($this, 'handleShutdown'));
    }

    /**
     * handles scripts normal or emergency shutdown
     */
    public function handleShutdown()
    {
        $error = error_get_last();
        if (DEBUG) {
            $doTrace = !is_null($error) ? true : false;
        } else {
            if (!is_null($error) && in_array($error['type'], array(E_ERROR, E_COMPILE_ERROR, E_CORE_ERROR, E_RECOVERABLE_ERROR, E_USER_ERROR) )) {
                $doTrace = true;
            } else {
                $doTrace = false;
            }
        }
        if ($doTrace) {
            $errno   = $error["type"];
            $errfile = $error["file"];
            $errline = $error["line"];
            $errstr  = $error["message"];
            $this->logError('fatal', $errno, $errfile, $errline, $errstr);
        }
    }

    /**
     * @param $emergencyType
     * @param $errorCode
     * @param $errorFile
     * @param $errorLine
     * @param $errorMessage
     */
    public function logError($emergencyType, $errorCode, $errorFile, $errorLine, $errorMessage) {
        $f = fopen(ROOT . 'logs/' . $emergencyType . '.log', 'a+');
        fwrite($f, $errorCode . ' - ' . $errorFile . '::' . $errorLine . ' - ' . $errorMessage . "\n");
        fclose($f);
    }

    /**
     * @param \Exception $e
     */
    public function processUncaughtException(\Exception $e) {
        if (DEBUG) {
            var_dump( $e->getTrace() );
        }
        $this->logError('uncaught_exceptions', $e->getCode(), $e->getFile(), $e->getLine(), $e->getMessage());
    }
}