<?php
declare(strict_types=1);

namespace Boruta\Timebase\Filesystem\Query;


use Boruta\Timebase\Filesystem\Entity\FileSearchResultEntity;
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
        $visited = [];

        while ($low <= $high) {
            $currentLineNumber = (int)floor(($low + $high) / 2);
            $visited[] = $currentLineNumber;

            $file->seek($currentLineNumber);
            $currentLineContent = $file->current();
            $currentTimestamp = (int)substr($currentLineContent, 0, strpos($currentLineContent, ':'));

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

        print_r($visited);exit;

        $line1 = array_pop($visited);
        $file->seek($line1);
        $line1Content = $file->current();
        $line1Timestamp = (int)substr($line1Content, 0, strpos($line1Content, ':'));

        $line2 = array_pop($visited);
        $file->seek($line2);
        $line2Content = $file->current();
        $line2Timestamp = (int)substr($line2Content, 0, strpos($line2Content, ':'));

        if ($line1Timestamp < $line2Timestamp) {
            $entity->setBefore($this->getAllRecordsWithEqualTimestamp($file, $line1, $linesTotal));
            $entity->setAfter($this->getAllRecordsWithEqualTimestamp($file, $line2, $linesTotal));
        } else {
            $entity->setBefore($this->getAllRecordsWithEqualTimestamp($file, $line2, $linesTotal));
            $entity->setAfter($this->getAllRecordsWithEqualTimestamp($file, $line1, $linesTotal));
        }

        return $entity;
    }

    /**
     * @param SplFileObject $file
     * @param $line
     * @param $total
     * @return array
     */
    private function getAllRecordsWithEqualTimestamp(SplFileObject $file, $line, $total): array
    {
        $result = [];
        $file->seek($line);
        $mainLineContent = $file->current();
        $mainLineTimestamp = (int)substr($mainLineContent, 0, strpos($mainLineContent, ':'));
        $result[$mainLineTimestamp][$line] = $this->decodeLine($mainLineContent);

        for ($i = $line - 1; $i >= 0; $i--) {
            $file->seek($i);
            $currentLineContent = $file->current();
            $currentLineTimestamp = (int)substr($currentLineContent, 0, strpos($currentLineContent, ':'));
            if ($currentLineTimestamp !== $mainLineTimestamp) {
                break;
            }
            $result[$mainLineTimestamp][$i] = $this->decodeLine($currentLineContent);
        }
        for ($i = $line + 1; $i < $total; $i++) {
            $file->seek($i);
            $currentLineContent = $file->current();
            $currentLineTimestamp = (int)substr($currentLineContent, 0, strpos($currentLineContent, ':'));
            if ($currentLineTimestamp !== $mainLineTimestamp) {
                break;
            }
            $result[$mainLineTimestamp][$i] = $this->decodeLine($currentLineContent);
        }

        $result[$mainLineTimestamp] = array_values($result[$mainLineTimestamp]);

        return $result;
    }

    /**
     * @param string $content
     * @return mixed
     */
    private function decodeLine(string $content)
    {
        return json_decode(base64_decode(substr($content, strpos($content, ':') + 1)), true);
    }
}
