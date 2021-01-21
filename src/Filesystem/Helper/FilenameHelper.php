<?php
declare(strict_types=1);

namespace Boruta\Timebase\Filesystem\Helper;


/**
 * Class FilenameHelper
 * @package Boruta\Timebase\Filesystem\Helper
 */
abstract class FilenameHelper
{
    /**
     * @param array $files
     * @return array
     */
    public static function clearFilenames(array $files): array
    {
        array_walk($files, static function (&$file) {
            $file = basename($file);
        });
        return $files;
    }
}