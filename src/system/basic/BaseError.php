<?php
namespace system\basic;

class BaseError {

    /**
     * @var int
     */
    protected $_errorCode;

    /**
     * @var string
     */
    protected $_errorMessage;

    public function __construct($errorMessage = null, $errorCode = null)
    {
        if (!empty($errorMessage)) {
            $this->_errorMessage = $errorMessage;
        }

        if (!empty($errorCode)) {
            $this->_errorCode = $errorCode;
        }
    }

    public function __toString()
    {
        return $this->_errorMessage;
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->_errorCode;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }
} 