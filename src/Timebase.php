<?php
declare(strict_types=1);

namespace Boruta\Timebase;


use Boruta\Timebase\Filesystem\Manager\FilesystemManager;
use Boruta\Timebase\Operation\InsertOperation;
use Boruta\Timebase\Operation\SearchOperation;
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
     * @return SearchOperation
     */
    public function search(): SearchOperation
    {
        return new SearchOperation($this->filesystemManager);
    }
}
