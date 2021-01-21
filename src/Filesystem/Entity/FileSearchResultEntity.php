<?php
declare(strict_types=1);

namespace Boruta\Timebase\Filesystem\Entity;


/**
 * Class FileSearchResultEntity
 * @package Boruta\Timebase\Filesystem\Entity
 */
class FileSearchResultEntity
{
    /**
     * @var array|null
     */
    public $exact;
    /**
     * @var array|null
     */
    public $before;
    /**
     * @var array|null
     */
    public $after;

    /**
     * @return array|null
     */
    public function getExact(): ?array
    {
        return $this->exact;
    }

    /**
     * @param array $exact
     */
    public function setExact(array $exact): void
    {
        $this->exact = $exact;
    }

    /**
     * @return array|null
     */
    public function getBefore(): ?array
    {
        return $this->before;
    }

    /**
     * @param array $before
     */
    public function setBefore(array $before): void
    {
        $this->before = $before;
    }

    /**
     * @return array|null
     */
    public function getAfter(): ?array
    {
        return $this->after;
    }

    /**
     * @param array $after
     */
    public function setAfter(array $after): void
    {
        $this->after = $after;
    }

    /**
     * @param FileSearchResultEntity $entity
     * @return bool
     */
    public function rewrite(FileSearchResultEntity $entity): bool
    {
        $result = false;
        if ($this->getExact() === null && $entity->getExact() !== null) {
            $this->setExact($entity->getExact());
            $result = true;
        }
        if ($this->getBefore() === null && $entity->getBefore() !== null) {
            $this->setBefore($entity->getBefore());
            $result = true;
        }
        if ($this->getAfter() === null && $entity->getAfter() !== null) {
            $this->setAfter($entity->getAfter());
            $result = true;
        }
        return $result;
    }
}
