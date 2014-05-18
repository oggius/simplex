<?php
namespace system\basic;

/**
 * Class Redirector
 * @package system\basic
 */
class Redirector {

    private static $_headers = array(
        '301' => 'HTTP/1.1 301 Moved Permanently',
        '302' => 'HTTP/1.1 302 Found'
    );

    /**
     * redirects to the $redirectUrl location with the corresponding header
     * @param $redirectUrl
     * @param int $redirectCode
     */
    public static function redirect($redirectUrl, $redirectCode = 301)
    {
        if (array_key_exists($redirectCode, self::$_headers)) {
            header(self::$_headers[$redirectCode]);
            header("Location: " . $redirectUrl);
            exit();
        }
    }
}