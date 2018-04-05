<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\UserInput;

use BitWasp\Trezor\Device\Exception\UserInputDisabledException;

class DisabledUserInputRequest
{
    public function getInput(string $message): string
    {
        throw new UserInputDisabledException("User input is disabled!");
    }
}
