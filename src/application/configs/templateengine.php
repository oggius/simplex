<?php
/**
 * configuration file containing information about the template engine being used
 */

/**
 *
 */
$templateengineCfg['engine'] = 'Smarty';

$templateengineCfg['smarty'] = array(
    'cache_dir' => 'cache/smarty/cache/',
    'compiled_dir'  => 'cache/smarty/compiled/',
    'templates_dir' => 'application/views/',
    'plugins_dir' => 'application/views/_plugins/'
);
