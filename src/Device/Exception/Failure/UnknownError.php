<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Exception\Failure;

use BitWasp\Trezor\Device\Exception\FailureException;

class UnknownError extends FailureException
{
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
