<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Command;

use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Message;
use BitWasp\Trezor\Device\PinInput\CurrentPinInputInterface;
use BitWasp\TrezorProto\GetPublicKey;
use BitWasp\TrezorProto\PinMatrixRequest;
use BitWasp\TrezorProto\PublicKey;

class GetPublicKeyService extends DeviceService
{
    public function call(
        Session $session,
        CurrentPinInputInterface $currentPinInput,
        GetPublicKey $getPublicKey
    ): PublicKey {
        $proto = $session->sendMessage(Message::getPublicKey($getPublicKey));
        if ($proto instanceof PinMatrixRequest) {
            $proto = $session->sendMessage($this->provideCurrentPin($proto, $currentPinInput));
        }

        if (!($proto instanceof PublicKey)) {
            throw new \RuntimeException("Unexpected response, expecting PublicKey, got " . get_class($proto));
        }

        return $proto;
    }
}
