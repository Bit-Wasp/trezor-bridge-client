<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Command;

use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Message;
use BitWasp\TrezorProto\Features;
use BitWasp\TrezorProto\Initialize;

class InitializeService extends DeviceService
{
    public function call(
        Session $session,
        Initialize $initialize
    ): Features {
        $proto = $session->sendMessage(Message::initialize($initialize));
        if (!($proto instanceof Features)) {
            throw new \RuntimeException("Unexpected response, expecting Features, got " . get_class($proto));
        }

        return $proto;
    }
}
