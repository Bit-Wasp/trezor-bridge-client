<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device\UserInput;

use BitWasp\Test\Trezor\TestCase;
use BitWasp\Trezor\Device\UserInput\CurrentPinInput;
use BitWasp\Trezor\Device\UserInput\UserInputRequest;

class CurrentPinInputTest extends TestCase
{
    public function testGetCurrentPin()
    {
        $userInputMock = $this->getMockForAbstractClass(UserInputRequest::class);
        $userInputMock->expects($this->once())
            ->method("getInput")
            ->willReturn('1234');

        $currentPin = new CurrentPinInput($userInputMock);
        $pin = $currentPin->getPin();
        $this->assertEquals('1234', $pin);
    }
}
