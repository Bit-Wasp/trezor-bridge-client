<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Command;

use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Exception\UnexpectedResultException;
use BitWasp\Trezor\Device\Message;
use BitWasp\Trezor\Device\UserInput\CurrentPinInputInterface;
use BitWasp\TrezorProto;

class GetAddressService extends DeviceService
{
    public function call(
        Session $session,
        CurrentPinInputInterface $currentPinInput,
        TrezorProto\GetAddress $getAddress
    ): TrezorProto\Address {

        $proto = $session->sendMessage(Message::getAddress($getAddress));
        if ($proto instanceof TrezorProto\PinMatrixRequest) {
            $proto = $session->sendMessage($this->provideCurrentPin($proto, $currentPinInput));
        }

        if ($getAddress->getShowDisplay()) {
            while ($proto instanceof TrezorProto\ButtonRequest) {
                $proto = $session->sendMessage($this->confirmWithButton($proto, TrezorProto\ButtonRequestType::ButtonRequest_Other_VALUE));
            }
        }

        if (!($proto instanceof TrezorProto\Address)) {
            throw new UnexpectedResultException("Unexpected response, expecting Address, got " . get_class($proto));
        }

        return $proto;
    }
}
