<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Exception\Failure;

use BitWasp\Trezor\Device\Exception\CommandFailureException;

class UnknownError extends CommandFailureException
{
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
