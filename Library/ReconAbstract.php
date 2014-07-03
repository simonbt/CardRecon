<?php
/**
 * slim -- ReportsAbstract.php
 * User: Simon Beattie
 * Date: 10/06/2014
 * Time: 16:15
 */

namespace Library;


use StormFramework\Logger\Logger;

class ReconAbstract
{

    /**
     * @var \PDO $pdo
     */
    protected $pdo;

    /**
     * @var \Pheanstalk_Pheanstalk
     */
    protected $queue;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var array $config
     */
    protected $config;

    public function __construct($pdo, $queue, $logger)
    {
        $this->setPdo($pdo);
        $this->setQueue($queue);
        $this->setLogger($logger);
    }

    /**
     * @param $logger Logger
     * @return $this
     * @throws \OutOfBoundsException
     */
    public function setLogger($logger)
    {
        if (empty($logger))
        {
            throw new \OutOfBoundsException(__METHOD__ . ' cannot accept and empty logger');
        }
        $this->logger = $logger;
        return $this;
    }

    /**
     * setPdo sets the pdo property in object storage
     *
     * @param \PDO $pdo
     * @throws \InvalidArgumentException
     */
    public function setPdo($pdo)
    {
        if (empty($pdo)) {
            throw new \InvalidArgumentException(__METHOD__ . ' cannot accept an empty pdo');
        }
        $this->pdo = $pdo;
        return $this;
    }

    /**
     * setQueue sets the beanstalkd queue property in object storage
     *
     * @param \Pheanstalk_Pheanstalk $queue
     * @return $this
     * @throws \InvalidArgumentException
     */

    public function setQueue($queue)
    {
        if (empty($queue))
        {
            throw new \InvalidArgumentException(__METHOD__ . ' cannot accept an empty queue');
        }
        $this->queue = $queue;
        return $this;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * getPdo returns the pdo from the object
     *
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * getQueue returns the queue from the object
     *
     * @return \Pheanstalk_Pheanstalk
     */
    public function getQueue()
    {
        return $this->queue;
    }
} 