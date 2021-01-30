<?php
declare(strict_types=1);

namespace Boruta\Timebase\Filesystem\Helper;


use SplFileObject;

/**
 * Class LineTimestampHelper
 * @package Boruta\Timebase\Filesystem\Helper
 */
abstract class LineTimestampHelper
{
    /**
     * @param string $filePath
     * @return int
     */
    public static function getFirstLineTimestamp(string $filePath): int
    {
        if (!file_exists($filePath) || !filesize($filePath)) {
            return 0;
        }

        $file = new SplFileObject($filePath);
        $file->seek(0);
        $currentLineContent = $file->current();

        return (int)substr($currentLineContent, 0, strpos($currentLineContent, '/'));
    }

    /**
     * @param string $filePath
     * @return int
     */
    public static function getLastLineTimestamp(string $filePath): int
    {
        if (!file_exists($filePath) || !filesize($filePath)) {
            return 0;
        }

        $file = new SplFileObject($filePath);
        $file->seek(PHP_INT_MAX);
        $file->seek($file->key() - 1);
        $currentLineContent = $file->current();

        return (int)substr($currentLineContent, 0, strpos($currentLineContent, '/'));
    }
}
