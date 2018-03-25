<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Command;

use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Message;
use BitWasp\TrezorProto\ButtonRequest;
use BitWasp\TrezorProto\ButtonRequestType;
use BitWasp\TrezorProto\MessageType;
use BitWasp\TrezorProto\Success;
use BitWasp\TrezorProto\VerifyMessage;

class VerifyMessageService extends DeviceService
{
    public function call(Session $session, VerifyMessage $message): Success
    {
        $message = $session->sendMessage(Message::verifyMessage($message));
        $proto = $message->getProto();

        if ($proto instanceof ButtonRequest) {
            // allow user to verify address
            $message = $session->sendMessage($this->confirmWithButton($proto, ButtonRequestType::ButtonRequest_Other_VALUE));
            $proto = $message->getProto();
        }

        if ($proto instanceof ButtonRequest) {
            // allow user to verify message
            $message = $session->sendMessage($this->confirmWithButton($proto, ButtonRequestType::ButtonRequest_Other_VALUE));
        }

        $this->checkResponseType($message, MessageType::MessageType_Success_VALUE);

        return $message->getProto();
    }
}
