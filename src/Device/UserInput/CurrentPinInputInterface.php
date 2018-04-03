<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\UserInput;

interface CurrentPinInputInterface
{
    public function getPin(): string;
}
