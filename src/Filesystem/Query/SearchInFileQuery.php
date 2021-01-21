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

        $step = (int)ceil(($linesTotal + 1) / 2);
        $currentLineNumber = $step;

        $visited = [];
        do {
            if (in_array($currentLineNumber, $visited, true)) {
                break;
            }
            $visited[] = $currentLineNumber;
            $file->seek($currentLineNumber - 1);
            $currentLineContent = $file->current();
            $currentTimestamp = (int)substr($currentLineContent, 0, strpos($currentLineContent, ':'));

            if ($currentTimestamp > $timestamp) {
                $currentLineNumber -= ($step = $step > 1 ? (int)floor($step / 2) : 1);
            } elseif ($currentTimestamp < $timestamp) {
                $currentLineNumber += ($step = $step > 3 ? (int)ceil($step / 2) : 1);
            } else {
                break;
            }
        } while ($currentLineNumber > 0 && $currentLineNumber <= $linesTotal);

        if ($currentTimestamp === $timestamp) {
            // find same as $currentLineNumber and save to $exact
            $entity->setExact($this->getAllRecordsWithEqualTimestamp($file, $currentLineNumber, $linesTotal));
            return $entity;
        }

        if ($currentLineNumber <= 0 || $currentLineNumber >= $linesTotal) {
            if ($currentTimestamp > $timestamp) {
                // find same as line no 1 and save to $before
                $entity->setAfter($this->getAllRecordsWithEqualTimestamp($file, 1, $linesTotal));
                return $entity;
            }
            if ($currentTimestamp < $timestamp) {
                // find same as line no $linesTotal and save to $before
                $entity->setBefore($this->getAllRecordsWithEqualTimestamp($file, $linesTotal, $linesTotal));
                return $entity;
            }
        }

        $line1 = array_pop($visited);
        $file->seek($line1 - 1);
        $line1Content = $file->current();
        $line1Timestamp = (int)substr($line1Content, 0, strpos($line1Content, ':'));

        $line2 = array_pop($visited);
        $file->seek($line2 - 1);
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
        $file->seek($line - 1);
        $mainLineContent = $file->current();
        $mainLineTimestamp = (int)substr($mainLineContent, 0, strpos($mainLineContent, ':'));
        $result[$mainLineTimestamp][$line] = $this->decodeLine($mainLineContent);

        for ($i = $line - 1; $i > 0; $i--) {
            $file->seek($i - 1);
            $currentLineContent = $file->current();
            $currentLineTimestamp = (int)substr($currentLineContent, 0, strpos($currentLineContent, ':'));
            if ($currentLineTimestamp !== $mainLineTimestamp) {
                break;
            }
            $result[$mainLineTimestamp][$i] = $this->decodeLine($currentLineContent);
        }
        for ($i = $line + 1; $i <= $total; $i++) {
            $file->seek($i - 1);
            $currentLineContent = $file->current();
            $currentLineTimestamp = (int)substr($currentLineContent, 0, strpos($currentLineContent, ':'));
            if ($currentLineTimestamp !== $mainLineTimestamp) {
                break;
            }
            $result[$mainLineTimestamp][$i] = $this->decodeLine($currentLineContent);
        }

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
