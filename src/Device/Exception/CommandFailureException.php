<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Exception;

use BitWasp\TrezorProto\Failure;
use Throwable;

class CommandFailureException extends DeviceException
{
    /**
     * @var Failure
     */
    private $failure;

    public function __construct(Failure $failure, Throwable $previous = null)
    {
        $this->failure = $failure;
        parent::__construct($failure->getMessage(), $failure->getCode()->value(), $previous);
    }

    public function getFailure(): Failure
    {
        return $this->failure;
    }

    public function getErrorName(): string
    {
        return $this->getFailure()->getCode()->name();
    }
}
