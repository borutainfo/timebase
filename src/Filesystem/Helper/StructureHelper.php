<?php
declare(strict_types=1);

namespace Boruta\Timebase\Filesystem\Helper;


use RuntimeException;

/**
 * Class StructureHelper
 * @package Boruta\Timebase\Filesystem\Helper
 */
abstract class StructureHelper
{
    /**
     * @param string $dir
     */
    public static function createDirectoryStructure(string $dir): void
    {
        if (!file_exists($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
    }
}
