<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor;

use BitWasp\Trezor\Device\UserInput\CurrentPinInput;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public function createDevice(string $path, int $vendor = null, int $product = null, string $session = null)
    {
        $device = new \stdClass();
        $device->path = $path;
        $device->vendor = $vendor;
        $device->product = $product;
        $device->session = $session;
        return $device;
    }

    /**
     * @param string $pin
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    public function getMockSinglePinInput(string $pin)
    {
        $pinInputBuilder = $this
            ->getMockBuilder(CurrentPinInput::class)
            ->disableOriginalConstructor()
        ;

        $pinInput = $pinInputBuilder->getMock();
        $pinInput->expects($this->once())
            ->method('getPin')
            ->willReturn($pin);

        return $pinInput;
    }
}
