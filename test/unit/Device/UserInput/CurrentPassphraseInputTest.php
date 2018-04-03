<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device\UserInput;

use BitWasp\Test\Trezor\TestCase;
use BitWasp\Trezor\Device\UserInput\CurrentPassphraseInput;
use BitWasp\Trezor\Device\UserInput\UserInputRequest;

class CurrentPassphraseInputTest extends TestCase
{
    public function testGetCurrentPin()
    {
        $userInputMock = $this->getMockForAbstractClass(UserInputRequest::class);
        $userInputMock->expects($this->once())
            ->method("getInput")
            ->willReturn('my-bip39-passphrase');

        $currentPin = new CurrentPassphraseInput($userInputMock);
        $pin = $currentPin->getPassphrase();
        $this->assertEquals('my-bip39-passphrase', $pin);
    }
}
