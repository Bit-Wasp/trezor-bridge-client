<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\PinInput;

class CurrentPinInput implements CurrentPinInputInterface
{
    public function getPin(): int
    {
        echo "It's your safe and trusted pin entry!\n";
        echo "Enter your pin to proceed: ";
        return (int) trim(fgets(STDIN));
    }
}
