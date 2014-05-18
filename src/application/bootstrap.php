<?php
define('DS', DIRECTORY_SEPARATOR);
/*
 * Path to the src root of the project
 */
define('ROOT', str_replace('\\',DS, dirname(__FILE__)) . DS . '..' . DS);

/**
 *  application path
 */
define('APP_PATH', ROOT . 'application' . DS);

/**
 * define system path, where all the system libs and framework components are
 */
define('SYSTEM_PATH', ROOT . 'system' . DS);

/**
 * define the path to the basic framework components
 */
define('BASESYS_PATH', SYSTEM_PATH . 'basic' . DS);

/**
 * register the autoloader
 */
require_once BASESYS_PATH . "Autoloader.php";

/**
 * defines the app mode
 */
define('DEBUG', true);

DEBUG ? error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT) : error_reporting(0);
