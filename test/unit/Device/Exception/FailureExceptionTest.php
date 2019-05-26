<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device\Exception;

use BitWasp\Test\Trezor\TestCase;
use BitWasp\Trezor\Device\Exception\Failure\UnknownError;
use BitWasp\Trezor\Device\Exception\FailureException;
use BitWasp\TrezorProto\Failure;

class FailureExceptionTest extends TestCase
{
    public function testUnknownError()
    {
        $stream = \Protobuf\Stream::fromString(hex2bin("0842120a6e6577206572726f7221"));
        $failure = new Failure($stream);

        $this->expectException(UnknownError::class);
        $this->expectExceptionCode(0);
        FailureException::handleFailure($failure);
    }
}
