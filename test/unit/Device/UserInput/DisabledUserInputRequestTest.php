<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device\UserInput;

use BitWasp\Test\Trezor\TestCase;
use BitWasp\Trezor\Device\Exception\UserInputDisabledException;
use BitWasp\Trezor\Device\UserInput\DisabledUserInputRequest;

class DisabledUserInputRequestTest extends TestCase
{
    public function testDisabledUserInput()
    {
        $disabled = new DisabledUserInputRequest();

        $this->expectException(UserInputDisabledException::class);
        $this->expectExceptionMessage("User input is disabled!");

        $disabled->getInput("");
    }
}
