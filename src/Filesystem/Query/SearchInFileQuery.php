<?php
declare(strict_types=1);

namespace Boruta\Timebase\Filesystem\Query;


use Boruta\Timebase\Filesystem\Entity\FileSearchResultEntity;
use JsonException;
use SplFileObject;

/**
 * Class SearchInFileQuery
 * @package Boruta\Timebase\Filesystem\Query
 */
class SearchInFileQuery
{
    /**
     * @param string $filePath
     * @param int $timestamp
     * @return FileSearchResultEntity
     * @throws JsonException
     */
    public function execute(string $filePath, int $timestamp): FileSearchResultEntity
    {
        $entity = new FileSearchResultEntity();

        $file = new SplFileObject($filePath);
        $file->seek(PHP_INT_MAX);
        $linesTotal = $file->key();

        if ($linesTotal <= 0) {
            return $entity;
        }

        $low = 0;
        $high = $linesTotal - 1;

        $currentLineNumber = 0;
        $currentTimestamp = 0;

        while ($low <= $high) {
            $currentLineNumber = (int)floor(($low + $high) / 2);

            $file->seek($currentLineNumber);
            $currentLineContent = $file->current();
            $currentTimestamp = (int)substr($currentLineContent, 0, strpos($currentLineContent, '/'));

            if ($currentTimestamp === $timestamp) {
                $entity->setExact($this->getAllRecordsWithEqualTimestamp($file, $currentLineNumber, $linesTotal));
                return $entity;
            }

            if ($timestamp < $currentTimestamp) {
                $high = $currentLineNumber - 1;
            } else {
                $low = $currentLineNumber + 1;
            }
        }

        if ($timestamp < $currentTimestamp && $currentLineNumber === 0) {
            $entity->setAfter($this->getAllRecordsWithEqualTimestamp($file, 0, $linesTotal));
            return $entity;
        }
        if ($timestamp > $currentTimestamp && $currentLineNumber === ($linesTotal - 1)) {
            $entity->setBefore($this->getAllRecordsWithEqualTimestamp($file, $linesTotal - 1, $linesTotal));
            return $entity;
        }

        if ($timestamp < $currentTimestamp) {
            $entity->setBefore($this->getAllRecordsWithEqualTimestamp($file, $currentLineNumber - 1, $linesTotal));
            $entity->setAfter($this->getAllRecordsWithEqualTimestamp($file, $currentLineNumber, $linesTotal));
        } else {
            $entity->setBefore($this->getAllRecordsWithEqualTimestamp($file, $currentLineNumber, $linesTotal));
            $entity->setAfter($this->getAllRecordsWithEqualTimestamp($file, $currentLineNumber + 1, $linesTotal));
        }

        return $entity;
    }

    /**
     * @param SplFileObject $file
     * @param $line
     * @param $total
     * @return array
     * @throws JsonException
     */
    private function getAllRecordsWithEqualTimestamp(SplFileObject $file, $line, $total): array
    {
        $result = [];
        $file->seek($line);
        $mainLineContent = $file->current();
        $mainLineTimestamp = (int)substr($mainLineContent, 0, strpos($mainLineContent, '/'));
        $result[$mainLineTimestamp][$line] = $this->decodeLine($mainLineContent);

        for ($i = $line - 1; $i >= 0; $i--) {
            $file->seek($i);
            $currentLineContent = $file->current();
            $currentLineTimestamp = (int)substr($currentLineContent, 0, strpos($currentLineContent, '/'));
            if ($currentLineTimestamp !== $mainLineTimestamp) {
                break;
            }
            $result[$mainLineTimestamp][$i] = $this->decodeLine($currentLineContent);
        }
        for ($i = $line + 1; $i < $total; $i++) {
            $file->seek($i);
            $currentLineContent = $file->current();
            $currentLineTimestamp = (int)substr($currentLineContent, 0, strpos($currentLineContent, '/'));
            if ($currentLineTimestamp !== $mainLineTimestamp) {
                break;
            }
            $result[$mainLineTimestamp][$i] = $this->decodeLine($currentLineContent);
        }

        ksort($result[$mainLineTimestamp]);

        return $result;
    }

    /**
     * @param string $content
     * @return mixed
     * @throws JsonException
     */
    private function decodeLine(string $content)
    {
        return json_decode(base64_decode(substr($content, strpos($content, '/') + 1)), true, 512, JSON_THROW_ON_ERROR);
    }
}
