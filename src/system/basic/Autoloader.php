<?php
namespace system\basic;

use system\basic\exceptions\MissingClassException;

class Autoloader
{
    public static function autoload($class)
    {
        $file = str_replace('\\', '/', $class);
        $filepath = ROOT . $file . '.php';
        if (file_exists($filepath)) {
            require_once $filepath;
        } else {
            throw new MissingClassException('Class ' . $class . ' wasn\'t autoloaded correctly. Full path ' . $filepath);
        }
    }
}

\spl_autoload_register(array('\system\basic\Autoloader', 'autoload'));