<?php
declare(strict_types=1);

namespace Boruta\Timebase;


use Boruta\Timebase\Filesystem\Manager\FilesystemManager;
use Boruta\Timebase\Operation\InsertOperation;
use Boruta\Timebase\Operation\QueryOperation;
use Psr\Log\LoggerInterface;

/**
 * Class Timebase
 * @package Boruta\Timebase
 */
class Timebase
{
    /**
     * @var FilesystemManager
     */
    private $filesystemManager;

    /**
     * Timebase constructor.
     * @param string $path
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $path, LoggerInterface $logger = null)
    {
        $this->filesystemManager = new FilesystemManager($path, $logger);
    }

    /**
     * @return InsertOperation
     */
    public function insert(): InsertOperation
    {
        return new InsertOperation($this->filesystemManager);
    }

    /**
     * @return QueryOperation
     */
    public function query(): QueryOperation
    {
        return new QueryOperation($this->filesystemManager);
    }
}
