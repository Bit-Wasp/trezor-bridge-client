<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Command;

use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Message;
use BitWasp\Trezor\Device\PinInput\CurrentPinInputInterface;
use BitWasp\TrezorProto\Address;
use BitWasp\TrezorProto\ButtonRequest;
use BitWasp\TrezorProto\ButtonRequestType;
use BitWasp\TrezorProto\GetAddress;
use BitWasp\TrezorProto\MessageType;
use BitWasp\TrezorProto\PinMatrixRequest;

class GetAddressService extends DeviceService
{
    public function call(Session $session, CurrentPinInputInterface $currentPinInput, GetAddress $getAddress): Address
    {
        $message = $session->sendMessage(Message::getAddress($getAddress));
        $proto = $message->getProto();

        if ($proto instanceof PinMatrixRequest) {
            $message = $session->sendMessage($this->provideCurrentPin($proto, $currentPinInput));
            $proto = $message->getProto();
        }

        if ($getAddress->getShowDisplay()) {
            while($proto instanceof ButtonRequest) {
                $message = $session->sendMessage($this->confirmWithButton($proto, ButtonRequestType::ButtonRequest_Address_VALUE));
                $proto = $message->getProto();
            }
        }

        $this->checkResponseType($message, MessageType::MessageType_Address_VALUE);

        return $message->getProto();
    }
}
