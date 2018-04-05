<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Command;

use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Exception\UnexpectedResultException;
use BitWasp\Trezor\Device\Message;
use BitWasp\TrezorProto\ButtonRequest;
use BitWasp\TrezorProto\ButtonRequestType;
use BitWasp\TrezorProto\Entropy;
use BitWasp\TrezorProto\GetEntropy;

class GetEntropyService extends DeviceService
{
    public function call(
        Session $session,
        GetEntropy $getEntropy
    ): Entropy {

        $proto = $session->sendMessage(Message::getEntropy($getEntropy));
        if ($proto instanceof ButtonRequest) {
            $proto = $session->sendMessage($this->confirmWithButton($proto, ButtonRequestType::ButtonRequest_ProtectCall()));
        }

        if (!($proto instanceof Entropy)) {
            throw new UnexpectedResultException("Unexpected message returned, expecting Entropy");
        }

        return $proto;
    }
}
