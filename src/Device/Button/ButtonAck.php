<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Button;

use BitWasp\Trezor\Bridge\Session;
use BitWasp\TrezorProto\ButtonRequest;
use BitWasp\TrezorProto\ButtonRequestType;

abstract class ButtonAck
{
    abstract public function acknowledge(Session $session, ButtonRequest $request, ButtonRequestType $allowType);
}
