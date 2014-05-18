<?php
namespace system\language;

/**
 * Class LanguageString
 * @package system\language
 */
class LanguageString
{
    /**
     * @var array
     */
    private static $_langResources = array();

    /**
     * @var string
     */
    private static $_defaultLanguage = 'en';

    /**
     * @var
     */
    private static $_definedLanguage;

    /**
     * @var string
     */
    private $_stringInterpreted = '';

    /**
     * @param $resId
     * @param null $section
     * @param null $language
     */
    public function __construct($resId, $section = null, $language = null)
    {
        if (!empty($resId)) {
            $this->_setLangResource($language);

            if (empty($language)) {
                $language = self::$_definedLanguage;
            }

            if (!empty($section)) {
                if (array_key_exists($resId, self::$_langResources[$language][$section])) {
                    $this->_stringInterpreted =  self::$_langResources[$language][$section][$resId];
                }
            } else {
                if (array_key_exists($resId, self::$_langResources[$language]) && is_string(self::$_langResources[$language][$resId])) {
                    $this->_stringInterpreted = self::$_langResources[$language][$resId];
                }
            }
        } else {
            $this->_stringInterpreted = '';
        }
    }

    /**
     * @param $language
     */
    private function _setLangResource($language) {
        // define language
        if (empty($language)) {
            if (empty(self::$_definedLanguage)) {
                $language = $_COOKIE['language'];
                if (empty($language)) {
                    $language = self::$_defaultLanguage;
                }
                self::$_definedLanguage = $language;
            } else {
                $language = self::$_definedLanguage;
            }
        }
        // check if the lang resource was initialised
        if (!array_key_exists($language, self::$_langResources)) {
            include_once(ROOT . 'resources/lang/' . $language . '.php');
            if (!empty($langResource)) {
                self::$_langResources[$language] = $langResource;
            }
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->_stringInterpreted;
    }
}