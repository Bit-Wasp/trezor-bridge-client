<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\PinInput;

class CurrentPinInput implements CurrentPinInputInterface
{
    public function getPin(): string
    {
        echo "It's your safe and trusted pin entry!\n";
        echo "Enter your pin to proceed: ";
        return trim(fgets(STDIN));
    }
}
