<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Command;

use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Exception\IncorrectNonceException;
use BitWasp\Trezor\Device\Message;
use BitWasp\Trezor\Device\PinInput\CurrentPassphraseInputInterface;
use BitWasp\Trezor\Device\PinInput\CurrentPinInputInterface;
use BitWasp\TrezorProto\ButtonRequest;
use BitWasp\TrezorProto\ButtonRequestType;
use BitWasp\TrezorProto\MessageType;
use BitWasp\TrezorProto\PassphraseRequest;
use BitWasp\TrezorProto\Ping;
use BitWasp\TrezorProto\PinMatrixRequest;
use BitWasp\TrezorProto\Success;

class PingService extends DeviceService
{
    public function call(
        Session $session,
        Ping $ping,
        CurrentPinInputInterface $pinInput = null,
        CurrentPassphraseInputInterface $passphraseInput = null
    ): Success
    {
        $message = $session->sendMessage(Message::ping($ping));
        $proto = $message->getProto();

        if ($proto instanceof ButtonRequest) {
            // allow user to accept with the button
            $message = $session->sendMessage($this->confirmWithButton($proto, ButtonRequestType::ButtonRequest_ProtectCall_VALUE));
            $proto = $message->getProto();
        }

        if ($ping->hasPinProtection()) {
            // allow user to accept with their pin
            if ($proto instanceof PinMatrixRequest) {
                if (!$pinInput) {
                    throw new \InvalidArgumentException("Missing pin input");
                }

                $message = $session->sendMessage($this->provideCurrentPin($proto, $pinInput));
                $proto = $message->getProto();
            }
        }

        if ($ping->hasPassphraseProtection()) {
            // allow user to accept with their passphrase
            if ($proto instanceof PassphraseRequest) {
                if (!$passphraseInput) {
                    throw new \InvalidArgumentException("Missing passphrase input");
                }

                $message = $session->sendMessage($this->provideCurrentPassphrase($passphraseInput));
                $proto = $message->getProto();
            }
        }

        $this->checkResponseType($message, MessageType::MessageType_Success_VALUE);
        /** @var Success $proto */
        if (!hash_equals($proto->getMessage(), $proto->getMessage())) {
            throw new IncorrectNonceException("Nonce returned by device was incorrect");
        }

        return $message->getProto();
    }
}
