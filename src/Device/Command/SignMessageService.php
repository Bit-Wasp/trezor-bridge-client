<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Command;

use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Message;
use BitWasp\Trezor\Device\PinInput\CurrentPinInputInterface;
use BitWasp\TrezorProto\ButtonRequest;
use BitWasp\TrezorProto\ButtonRequestType;
use BitWasp\TrezorProto\MessageSignature;
use BitWasp\TrezorProto\PinMatrixRequest;
use BitWasp\TrezorProto\SignMessage;

class SignMessageService extends DeviceService
{
    public function call(
        Session $session,
        CurrentPinInputInterface $currentPinInput,
        SignMessage $message
    ): MessageSignature {
        $proto = $session->sendMessage(Message::signMessage($message));
        if ($proto instanceof ButtonRequest) {
            $proto = $session->sendMessage($this->confirmWithButton($proto, ButtonRequestType::ButtonRequest_ProtectCall_VALUE));
        }

        if ($proto instanceof PinMatrixRequest) {
            $proto = $session->sendMessage($this->provideCurrentPin($proto, $currentPinInput));
        }

        if (!($proto instanceof MessageSignature)) {
            throw new \RuntimeException("Unexpected response, expecting MessageSignature, got " . get_class($proto));
        }

        return $proto;
    }
}
