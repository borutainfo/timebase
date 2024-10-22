<?php
declare(strict_types=1);

namespace Boruta\Timebase\Operation;


use Boruta\Timebase\Common\Exception\DataSavingException;
use Boruta\Timebase\Filesystem\Manager\FilesystemManager;

/**
 * Class InsertOperation
 * @package Boruta\Timebase\Operation
 */
class InsertOperation
{
    /**
     * @var FilesystemManager
     */
    private $filesystemManager;

    /**
     * @var array
     */
    private $storage = [];
    /**
     * @var int|null
     */
    private $timestamp;
    /**
     * @var mixed
     */
    private $data;

    /**
     * InsertOperation constructor.
     * @param FilesystemManager $filesystemManager
     */
    public function __construct(FilesystemManager $filesystemManager)
    {
        $this->filesystemManager = $filesystemManager;
    }

    /**
     * @param array $storage
     * @return $this
     */
    public function storage(array $storage): self
    {
        $this->storage = $storage;
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

    /**
     * @param $data
     * @return $this
     */
    public function set($data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @throws DataSavingException
     */
    public function execute(): void
    {
        $this->filesystemManager->save($this->storage, $this->data, $this->timestamp);
    }
}
