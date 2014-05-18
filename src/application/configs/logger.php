<?php
$loggerCfg = array(
    // logging destination. Available values: file, database
    'destination' => 'file',

    // 'FileLogger config'
    'file' => array(
        'folder' => 'logs',
        'extension' => 'txt',
    ),

    // DbLogger config
    'database' => array(
        'table' => 'logs'
    )
);