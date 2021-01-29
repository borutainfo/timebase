<?php
declare(strict_types=1);

namespace Boruta\Timebase\Common\Exception;


use Throwable;

/**
 * Class DataReadingException
 * @package Boruta\Timebase\Common\Exception
 */
class DataReadingException extends TimebaseException
{
    public const MESSAGE = 'Timebase exception: Unable to read data.';

    /**
     * DataReadingException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = self::MESSAGE, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
