<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Command;

use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Message;
use BitWasp\Trezor\Device\PinInput\CurrentPinInputInterface;
use BitWasp\TrezorProto\ButtonRequest;
use BitWasp\TrezorProto\ButtonRequestType;
use BitWasp\TrezorProto\MessageSignature;
use BitWasp\TrezorProto\MessageType;
use BitWasp\TrezorProto\PinMatrixRequest;
use BitWasp\TrezorProto\SignMessage;

class SignMessageService extends DeviceService
{
    public function call(Session $session, CurrentPinInputInterface $currentPinInput, SignMessage $message): MessageSignature
    {
        echo "send sign request\n";
        $message = $session->sendMessage(Message::signMessage($message));
        $proto = $message->getProto();

        if ($proto instanceof ButtonRequest) {
            echo "got button request\n";
            $message = $session->sendMessage($this->confirmWithButton($proto, ButtonRequestType::ButtonRequest_ProtectCall_VALUE));
            $proto = $message->getProto();
        }

        if ($proto instanceof PinMatrixRequest) {
            echo "got pin matrix\n";
            $message = $session->sendMessage($this->provideCurrentPin($proto, $currentPinInput));
        }

        echo "get signed message\n";
        $this->checkResponseType($message, MessageType::MessageType_MessageSignature_VALUE);

        return $message->getProto();
    }
}
