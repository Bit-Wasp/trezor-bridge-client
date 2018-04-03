<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\UserInput;

interface CurrentPassphraseInputInterface
{
    public function getPassphrase(): string;
}
