<?php
declare(strict_types=1);

namespace Boruta\Timebase\Operation;


use Boruta\Timebase\Filesystem\Entity\FileSearchResultEntity;
use Boruta\Timebase\Filesystem\Manager\FilesystemManager;
use Exception;

/**
 * Class QueryOperation
 * @package Boruta\Timebase\Operation
 */
class QueryOperation
{
    /**
     * @var FilesystemManager
     */
    private $filesystemManager;

    /**
     * @var array
     */
    private $storagePath = [];
    /**
     * @var int|null
     */
    private $timestamp;
    /**
     * @var bool
     */
    private $exact = false;

    /**
     * InsertOperation constructor.
     * @param FilesystemManager $filesystemManager
     */
    public function __construct(FilesystemManager $filesystemManager)
    {
        $this->filesystemManager = $filesystemManager;
    }

    /**
     * @param array $storagePath
     * @return $this
     */
    public function storage(array $storagePath): self
    {
        $this->storagePath = $storagePath;
        return $this;
    }

    /**
     * @param int $timestamp
     * @return $this
     */
    public function timestamp(int $timestamp): self
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    public function exact(): self
    {
        $this->exact = true;
        return $this;
    }

    public function approximate(): self
    {
        $this->exact = false;
        return $this;
    }

    /**
     * @return FileSearchResultEntity
     * @throws Exception
     */
    public function execute(): FileSearchResultEntity
    {
        return $this->filesystemManager->fetch($this->storagePath, $this->timestamp);
    }
}
