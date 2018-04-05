<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\UserInput;

abstract class UserInputRequest
{
    abstract public function getInput(string $message): string;
}
