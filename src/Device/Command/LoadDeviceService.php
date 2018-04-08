<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Command;

use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Button\ButtonAck;
use BitWasp\Trezor\Device\Exception\UnexpectedResultException;
use BitWasp\Trezor\Device\Message;
use BitWasp\TrezorProto;

class LoadDeviceService extends DeviceService
{
    private $ack;

    public function __construct(ButtonAck $buttonAck)
    {
        $this->ack = $buttonAck;
    }

    public function call(
        Session $session,
        TrezorProto\LoadDevice $loadDevice
    ): TrezorProto\Success {

        fwrite(STDERR, "loadService.sending load device\n");
        $proto = $session->sendMessage(Message::loadDevice($loadDevice));
        fwrite(STDERR, "loadService.response for load device\n");

        if ($proto instanceof TrezorProto\ButtonRequest) {
            fwrite(STDERR, "loadService.send button ack\n");
            $proto = $this->ack->acknowledge($session, $proto, TrezorProto\ButtonRequestType::ButtonRequest_ProtectCall());
            fwrite(STDERR, "loadService.response for ack device\n");
        }

        if (!($proto instanceof TrezorProto\Success)) {
            throw new UnexpectedResultException("Unexpected response, expecting Success, got " . get_class($proto));
        }
        fwrite(STDERR, "done\n");
        return $proto;
    }
}
