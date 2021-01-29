<?php
declare(strict_types=1);

namespace Boruta\Timebase\Filesystem\Helper;


/**
 * Class StoragePathHelper
 * @package Boruta\Timebase\Filesystem\Helper
 */
abstract class PathHelper
{
    /**
     * @param array $pathArray
     * @return string
     */
    public static function arrayToString(array $pathArray): string
    {
        $result = '';
        foreach ($pathArray as $path) {
            $result .= '/' . trim($path, '/');
        }
        return $result;
    }

    /**
     * @param array $pathArray
     * @return string
     */
    public static function arrayToStringSanitized(array $pathArray): string
    {
        array_walk($pathArray, static function (&$value) {
            $value = strtolower(preg_replace("/\W/", '', $value));
        });
        return implode('/', $pathArray);
    }
}
