<?php
declare(strict_types=1);

namespace Boruta\Timebase\Operation;


use Boruta\Timebase\Common\Constant\SearchStrategyConstant;
use Boruta\Timebase\Common\Exception\DataReadingException;
use Boruta\Timebase\Common\Presenter\SearchResultPresenter;
use Boruta\Timebase\Filesystem\Manager\FilesystemManager;

/**
 * Class SearchOperation
 * @package Boruta\Timebase\Operation
 */
class SearchOperation
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
     * @var string
     */
    private $strategy = SearchStrategyConstant::NEAREST;
    /**
     * @var bool
     */
    private $all = false;

    /**
     * SearchOperation constructor.
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
     * @param string $strategy
     * @return $this
     */
    public function strategy(string $strategy = SearchStrategyConstant::NEAREST): self
    {
        $this->strategy = $strategy;
        return $this;
    }


    /**
     * @return $this
     */
    public function all(): self
    {
        $this->all = true;
        return $this;
    }

    /**
     * @return array|null
     * @throws DataReadingException
     */
    public function execute(): ?array
    {
        if ($this->timestamp === null) {
            $this->timestamp = time();
        }
        $resultEntity = $this->filesystemManager->read($this->storage, $this->timestamp);
        return SearchResultPresenter::present($resultEntity, $this->strategy, $this->timestamp, $this->all);
    }
}
