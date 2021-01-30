<?php
declare(strict_types=1);

namespace Boruta\Timebase\Common\Presenter;


use Boruta\Timebase\Common\Constant\SearchStrategyConstant;
use Boruta\Timebase\Common\Exception\DataReadingException;
use Boruta\Timebase\Filesystem\Entity\FileSearchResultEntity;

/**
 * Class SearchResultPresenter
 */
class SearchResultPresenter
{
    /**
     * @param FileSearchResultEntity $entity
     * @param string $strategy
     * @param int $timestamp
     * @param bool $all
     * @return array|null
     * @throws DataReadingException
     */
    public static function present(
        FileSearchResultEntity $entity,
        string $strategy,
        int $timestamp,
        bool $all = false
    ): ?array {
        if (!empty($entity->getExact())) {
            $strategyResult = $entity->getExact();
        } else {
            switch ($strategy) {
                case SearchStrategyConstant::EXACT:
                    $strategyResult = $entity->getExact();
                    break;
                case SearchStrategyConstant::EARLIER:
                    $strategyResult = $entity->getBefore();
                    break;
                case SearchStrategyConstant::LATER:
                    $strategyResult = $entity->getAfter();
                    break;
                case SearchStrategyConstant::NEAREST:
                    $beforeTimestamp = empty($entity->getBefore()) ? null : (int)array_key_first($entity->getBefore());
                    $afterTimestamp = empty($entity->getAfter()) ? null : (int)array_key_first($entity->getAfter());
                    if ($beforeTimestamp === null && $afterTimestamp === null) {
                        $strategyResult = [];
                    } elseif ($beforeTimestamp !== null && $afterTimestamp === null) {
                        $strategyResult = $entity->getBefore();
                    } elseif ($beforeTimestamp === null && $afterTimestamp !== null) {
                        $strategyResult = $entity->getAfter();
                    } elseif (abs($beforeTimestamp - $timestamp) <= abs($afterTimestamp - $timestamp)) {
                        $strategyResult = $entity->getBefore();
                    } else {
                        $strategyResult = $entity->getAfter();
                    }
                    break;
                default:
                    throw new DataReadingException(DataReadingException::MESSAGE . ' Details: Unknown strategy.');
            }
        }

        if (empty($strategyResult)) {
            return null;
        }

        $strategyResultTimestamp = (int)array_key_first($strategyResult);
        $strategyResultAll = array_values($strategyResult[$strategyResultTimestamp]);

        $finalResult = [
            'timestamp' => $strategyResultTimestamp,
            'value' => $strategyResultAll[0]
        ];
        if ($all) {
            $finalResult['all'] = $strategyResultAll;
        }

        return $finalResult;
    }
}
