<?php

namespace BitWasp\Trezor\Device\PinInput;

interface CurrentPassphraseInputInterface
{
    public function getPassphrase(): string;
}
