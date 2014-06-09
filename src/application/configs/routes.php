<?php
$routesCfg = array(
    // default controller and action
    'defaultController' => 'index',
    'defaultAction' => 'index',
    // url suffix which is appended at the end of every url
    'urlSuffix' => '/',
    // collection of routes to remap the predefined application flow
    // NOTE: the order is important. More concrete rules must go first
    'routes' => array(
        // test route for hello world
        '/hello/[any1]/' => '/test/test/name/$1/',
        '/hello/' => '/test/test/',
        '/test/[any1]/' => '/test/test/var/$1/'
    ),

    // routes to be ignored while mapping
    // NOTE: this should be used to exclude some specific routing rules from the very general ones
    'ignoreRoutes' => array(
        '/test/me/'
    )
);