<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Command;

use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Message;
use BitWasp\Trezor\Device\PinInput\CurrentPinInputInterface;
use BitWasp\TrezorProto\GetPublicKey;
use BitWasp\TrezorProto\MessageType;
use BitWasp\TrezorProto\PinMatrixAck;
use BitWasp\TrezorProto\PinMatrixRequest;
use BitWasp\TrezorProto\PinMatrixRequestType;
use BitWasp\TrezorProto\PublicKey;

class GetPublicKeyService extends DeviceService
{
    public function call(Session $session, CurrentPinInputInterface $currentPinInput, GetPublicKey $getPublicKey): PublicKey
    {
        $message = $session->sendMessage(Message::getPublicKey($getPublicKey));
        $proto = $message->getProto();

        if ($proto instanceof PinMatrixRequest) {
            $this->checkPinRequestType($proto, PinMatrixRequestType::PinMatrixRequestType_Current_VALUE);

            $pinMatrixAck = new PinMatrixAck();
            $pinMatrixAck->setPin($currentPinInput->getPin());

            $message = $session->sendMessage(Message::pinMatrixAck($pinMatrixAck));
        }

        $this->checkResponseType($message, MessageType::MessageType_PublicKey_VALUE);

        return $message->getProto();
    }
}
