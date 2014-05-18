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
        '/routedfake/[any1]/'   => '/fakecontroller/fakeaction/name/$1/',
    )
);