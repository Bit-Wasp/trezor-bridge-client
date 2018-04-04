<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Command;

use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Exception\UnexpectedResultException;
use BitWasp\Trezor\Device\Message;
use BitWasp\Trezor\Device\UserInput\CurrentPinInputInterface;
use BitWasp\TrezorProto\Address;
use BitWasp\TrezorProto\ButtonRequest;
use BitWasp\TrezorProto\ButtonRequestType;
use BitWasp\TrezorProto\GetAddress;
use BitWasp\TrezorProto\PinMatrixRequest;

class GetAddressService extends DeviceService
{
    public function call(
        Session $session,
        CurrentPinInputInterface $currentPinInput,
        GetAddress $getAddress
    ): Address {

        $proto = $session->sendMessage(Message::getAddress($getAddress));
        if ($proto instanceof PinMatrixRequest) {
            $proto = $session->sendMessage($this->provideCurrentPin($proto, $currentPinInput));
        }

        if ($getAddress->getShowDisplay()) {
            while ($proto instanceof ButtonRequest) {
                $proto = $session->sendMessage($this->confirmWithButton($proto, ButtonRequestType::ButtonRequest_Address_VALUE));
            }
        }

        if (!($proto instanceof Address)) {
            throw new UnexpectedResultException("Unexpected response, expecting Address, got " . get_class($proto));
        }

        return $proto;
    }
}
