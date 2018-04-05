<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Command;

use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Exception\UnexpectedResultException;
use BitWasp\Trezor\Device\Message;
use BitWasp\TrezorProto\ButtonRequest;
use BitWasp\TrezorProto\ButtonRequestType;
use BitWasp\TrezorProto\Success;
use BitWasp\TrezorProto\VerifyMessage;

class VerifyMessageService extends DeviceService
{
    public function call(Session $session, VerifyMessage $message): Success
    {
        $proto = $session->sendMessage(Message::verifyMessage($message));
        if ($proto instanceof ButtonRequest) {
            // allow user to verify address
            $proto = $session->sendMessage($this->confirmWithButton($proto, ButtonRequestType::ButtonRequest_Other()));
        }

        if ($proto instanceof ButtonRequest) {
            // allow user to verify message
            $proto = $session->sendMessage($this->confirmWithButton($proto, ButtonRequestType::ButtonRequest_Other()));
        }

        if (!($proto instanceof Success)) {
            throw new UnexpectedResultException("Unexpected response, expecting Success, got " . get_class($proto));
        }

        return $proto;
    }
}
