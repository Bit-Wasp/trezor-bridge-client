<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\PinInput;

interface CurrentPinInputInterface
{
    public function getPin(): int;
}
