<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\UserInput;

interface UserInputRequestInterface
{
    public function getInput(string $message): string;
}
