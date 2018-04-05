<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\UserInput;

class CommandLineUserInputRequest extends UserInputRequest
{
    public function getInput(string $message): string
    {
        return readline($message);
    }
}
