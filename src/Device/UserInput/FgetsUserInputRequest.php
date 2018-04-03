<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\UserInput;

use BitWasp\Trezor\Device\Exception\UserInputException;

class FgetsUserInputRequest extends UserInputRequest
{
    public function getInput(): string
    {
        $input = fgets(STDIN);
        if (is_string($input)) {
            return $input;
        }

        throw new UserInputException("Failed to read from command line");
    }
}
