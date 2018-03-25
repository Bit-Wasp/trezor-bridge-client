<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Command;

use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Message;
use BitWasp\TrezorProto\ClearSession;
use BitWasp\TrezorProto\Success;

class ClearSessionService extends DeviceService
{
    public function call(
        Session $session,
        ClearSession $clearSession
    ): Success {
        $proto = $session->sendMessage(Message::clearSession($clearSession));
        if (!($proto instanceof Success)) {
            throw new \RuntimeException("Unexpected response, expecting Success, got " . get_class($proto));
        }

        return $proto;
    }
}
