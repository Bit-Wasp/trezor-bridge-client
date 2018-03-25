<?php

namespace BitWasp\Trezor\Device\Command;

use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Message;
use BitWasp\TrezorProto\Features;
use BitWasp\TrezorProto\Initialize;
use BitWasp\TrezorProto\MessageType;

class InitializeService extends DeviceService
{
    public function call(Session $session, Initialize $initialize): Features
    {
        $message = $session->sendMessage(Message::initialize($initialize));

        $this->checkResponseType($message, MessageType::MessageType_Features_VALUE);

        return $message->getProto();
    }
}
