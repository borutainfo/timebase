<?php
declare(strict_types=1);

namespace Boruta\Timebase\Filesystem\Helper;


/**
 * Class AppendDataToFileHelper
 * @package Boruta\Timebase\Filesystem\Helper
 */
abstract class AppendDataToFileHelper
{
    /**
     * @param string $file
     * @param string $data
     * @param int $position
     */
    public static function append(string $file, string $data, int $position): void
    {
        $fpFile = fopen($file, 'rwb+');
        $fpTemp = fopen('php://temp', 'rwb+');

        stream_copy_to_stream($fpFile, $fpTemp);
        rewind($fpFile);

        /** @noinspection MissingOrEmptyGroupStatementInspection */
        /** @noinspection PhpStatementHasEmptyBodyInspection */
        /** @noinspection LoopWhichDoesNotLoopInspection */
        while ($position-- >= 0 && fgets($fpFile) !== false) { }

        $position = (int)ftell($fpFile);

        fseek($fpFile, $position);
        fseek($fpTemp, $position);

        fwrite($fpFile, $data);

        stream_copy_to_stream($fpTemp, $fpFile);

        fclose($fpFile);
        fclose($fpTemp);
    }
}
