<?php
namespace Enginewerk\Common\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

trait HasLoggerTrait
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @return LoggerInterface|NullLogger
     */
    public function getLogger()
    {
        return $this->logger ?: new NullLogger();
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
