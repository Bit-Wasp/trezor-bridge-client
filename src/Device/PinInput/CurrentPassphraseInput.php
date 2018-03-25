<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\PinInput;

class CurrentPassphraseInput implements CurrentPassphraseInputInterface
{
    public function getPassphrase(): string
    {
        echo "It's your safe and trusted pin entry!\n";
        echo "Enter your passphrase to proceed: ";
        return trim(fgets(STDIN));
    }
}
