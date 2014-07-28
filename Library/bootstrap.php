<?php
/**
 * nessus-report-parser -- bootstrap.php
 * User: Simon Beattie
 * Date: 16/06/2014
 * Time: 14:52
 */

require __DIR__ . '/../vendor/autoload.php';

spl_autoload_register(function($className)
{
    $fileName = __DIR__ . '/../' . str_replace('\\', '/', $className) . '.php';

    if(!file_exists($fileName))
    {
        return false;
    }

    require($fileName);
});

if (file_exists(__DIR__ . '/../config.php'))
{
    $config = require(__DIR__ . '/../config.php');
}
else
{
    die('Config.php does not exist');
}

$logger = new \StormFramework\Logger\ContextLogger($config['logger']['location'], $config['logger']['level']);

try {
    $pdo = new \PDO(
        'mysql:host=' . $config['db']['hostname'] . ';dbname=' . $config['db']['database'],
        $config['db']['username'],
        $config['db']['password']
    );
}
catch (\PDOException $pdoError)
{
    print $pdoError->getMessage() . PHP_EOL;
}

try {
    $queue = new \Pheanstalk_Pheanstalk($config['beanstalkd']['hostname'] . ':' . $config['beanstalkd']['port']);
}
catch (\Pheanstalk_Exception $queueError)
{
    print $queueError->getMessage() . PHP_EOL;
}

try
{
    $memcache = new Memcache();

} catch (\MemcachedException $memcacheError)
{
    print $memcacheError->getMessage() . PHP_EOL;
}

$reportData = new \Library\ReportData($pdo);