<?php
/**
 * slim -- config.php
 * User: Simon Beattie
 * Date: 10/06/2014
 * Time: 15:55
 */

date_default_timezone_set('Europe/London');

return array(
    'db' => array(
        'hostname'  =>  '127.0.0.1',
        'database'  =>  'storm_recon',
        'username'  =>  'root',
        'password'  =>  'pa55word'
    ),
    'beanstalkd' => array(
        'hostname'  =>  '127.0.0.1',
        'port'      =>  '11300'
    ),
    'logger'    => array(
        'location'  =>  __DIR__ . '/logs',
        'level'     =>  \Psr\Log\LogLevel::DEBUG
    )
);