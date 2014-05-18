<?php
namespace system\basic\exceptions;

/**
 * Class BaseException
 * @package system\basic\exceptions
 */
class BaseException extends \Exception {
    public function __toString()
    {
        $trace = $this->getTrace();
        $loggedString = $this->message . "\n";
        foreach ($trace as $traceEntry) {
            $loggedString .= ' - /' . str_replace(ROOT, '', $traceEntry['file']) . ':' . $traceEntry['line'] . '  -  ';
            $isClass = false;
            if (!empty($traceEntry['class'])) {
                $isClass = true;
                $loggedString .= $traceEntry['class'];
            }
            if (!empty($traceEntry['function'])) {
                if ($isClass) {
                    $loggedString .= '::';
                }
                $loggedString .= $traceEntry['function'];
                // TODO: arguments logging
                //$loggedString .= ' - [' . print_r($traceEntry['args'], true) . ']';
            }
            $loggedString .= "\n";
        }
        return $loggedString;
    }
}