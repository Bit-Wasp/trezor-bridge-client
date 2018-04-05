<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor;

use BitWasp\Trezor\Device\UserInput\CurrentPinInput;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string $path
     * @param int|null $vendor
     * @param int|null $product
     * @param string|null $session
     * @return \stdClass
     */
    public function createDevice(
        string $path,
        int $vendor = null,
        int $product = null,
        string $session = null
    ): \stdClass {
        $device = new \stdClass();
        $device->path = $path;
        $device->vendor = $vendor;
        $device->product = $product;
        $device->session = $session;
        return $device;
    }

    /**
     * @param string $pin
     * @param int $numEntries
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    public function getMockSinglePinInput(string $pin, int $numEntries = 1)
    {
        $pinInputBuilder = $this
            ->getMockBuilder(CurrentPinInput::class)
            ->disableOriginalConstructor()
        ;

        $pinInput = $pinInputBuilder->getMock();
        $pinInput->expects($this->exactly($numEntries))
            ->method('getPin')
            ->willReturn($pin);

        return $pinInput;
    }
}
