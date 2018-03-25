<?php

namespace BitWasp\Trezor\Device\Command;

use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Message;
use BitWasp\TrezorProto\ButtonRequest;
use BitWasp\TrezorProto\ButtonRequestType;
use BitWasp\TrezorProto\Entropy;
use BitWasp\TrezorProto\GetEntropy;

class GetEntropyService extends DeviceService
{
    public function call(Session $session, GetEntropy $getEntropy): Entropy
    {
        $message = $session->sendMessage(Message::getEntropy($getEntropy));
        $proto = $message->getProto();

        if ($proto instanceof ButtonRequest) {
            $message = $session->sendMessage($this->confirmWithButton($proto, ButtonRequestType::ButtonRequest_ProtectCall_VALUE));
            $proto = $message->getProto();
        }

        if (!($proto instanceof Entropy)) {
            throw new \RuntimeException("Unexpected message returned, expecting Entropy");
        }

        return $proto;
    }
}
