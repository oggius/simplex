<?php
namespace system\basic;

/**
 * Class Response
 * @package system\basic
 */
class Response
{
    private static $_instance;

    private $_output;

    private $_contentTypeHeader = 'html';

    private static $_contentTypeHeaders = array(
        'html' => 'text/html',
        'json' => 'application/json',
        'text' => 'text/plain',
        'zip'  => 'application/zip'
    );

    private static $_errorHeaders = array(
        '403' => 'HTTP/1.0 404 Forbidden',
        '404' => 'HTTP/1.0 404 Not Found',
        '500' => 'HTTP/1.0 500 Internal Server Error',
        '503' => 'HTTP/1.0 503 Service Unavailable'

    );

    private $_setHeaders = array();

    /**
     * make construct private in order to prohibit explicit object creation with new
     */
    private function __construct() {}

    /**
     * make __clone method private
     */
    private function __clone() {}

    /**
     * returns instance of the Response object or creates it if not created yet
     * @return Response
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

	/**
	 * Starts output buffering
	 */
	public function startOutput()
	{
		ob_start();
		ob_implicit_flush(false);
	}

	/**
	 * Returns contents of the output buffer and discards it
	 * @return string output buffer contents
	 */
	public function endOutput()
	{
		return ob_get_clean();
	}

    public function setOutput($content, $contentTypeHeader = 'html')
    {
        ob_end_clean();
        $this->_output = $content;
        if (array_key_exists($contentTypeHeader, self::$_contentTypeHeaders)) {
            $header = self::$_contentTypeHeaders[$this->_contentTypeHeader];
        } else {
            $header = 'text/html';
        }
        $this->setHeader('Content-Type: ' . $header);
    }

	/**
	 * Returns contents of the output buffer
	 * @return string output buffer contents
	 */
	public function getOutput()
	{
        $this->getHeaders();
        // output the content
        if (empty($this->_output)) {
            $this->_output = ob_get_contents();
        }
        echo $this->_output;
	}

    /**
     * checks if the output is set
     * @return bool
     */
    public function hasOutput()
    {
        return !empty($this->_output);
    }

	/**
	 * Discards the output buffer
	 * @param boolean $all if true recursively discards all output buffers used
	 */
	public function cleanOutput($all = true)
	{
		if ($all) {
			for ($level = ob_get_level(); $level > 0; --$level) {
				if (!@ob_end_clean()) {
					ob_clean();
				}
			}
		} else {
			ob_end_clean();
		}
	}

    public function setErrorHeader($headerCode)
    {
        if (array_key_exists($headerCode, self::$_errorHeaders)) {
           $this->setHeader(self::$_errorHeaders[$headerCode], true);
        }
    }

    public function setHeader($header, $clearPrevious = false)
    {
        if ($clearPrevious) {
            $this->_setHeaders = array();
        }
        $this->_setHeaders[] = $header;
    }

    public function getHeaders()
    {
        foreach($this->_setHeaders as $header) {
            header($header);
        }
    }
}
