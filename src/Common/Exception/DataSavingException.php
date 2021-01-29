<?php
declare(strict_types=1);

namespace Boruta\Timebase\Common\Exception;


use Throwable;

/**
 * Class DataSavingException
 * @package Boruta\Timebase\Common\Exception
 */
class DataSavingException extends TimebaseException
{
    public const MESSAGE = 'Timebase exception: Unable to save data.';

    /**
     * DataSavingException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = self::MESSAGE, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
