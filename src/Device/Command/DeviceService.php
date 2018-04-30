<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Command;

use BitWasp\Trezor\Device\Message;
use BitWasp\Trezor\Device\UserInput\CurrentPassphraseInputInterface;
use BitWasp\Trezor\Device\UserInput\CurrentPinInputInterface;
use BitWasp\TrezorProto\ButtonAck;
use BitWasp\TrezorProto\ButtonRequest;
use BitWasp\TrezorProto\ButtonRequestType;
use BitWasp\TrezorProto\PassphraseAck;
use BitWasp\TrezorProto\PinMatrixAck;
use BitWasp\TrezorProto\PinMatrixRequest;
use BitWasp\TrezorProto\PinMatrixRequestType;

abstract class DeviceService
{
    protected function confirmWithButton(ButtonRequest $request, ButtonRequestType $buttonType): Message
    {
        $theirType = $request->getCode();
        if ($theirType->value() !== $buttonType->value()) {
            throw new \RuntimeException("Unexpected button request (expected: {$buttonType->name()}, got {$theirType->name()})");
        }

        return Message::buttonAck(new ButtonAck());
    }

    protected function provideCurrentPin(PinMatrixRequest $proto, CurrentPinInputInterface $currentPinInput): Message
    {
        if ($proto->getType()->value() !== PinMatrixRequestType::PinMatrixRequestType_Current()->value()) {
            throw new \RuntimeException("Unexpected pin matrix type (was {$proto->getType()->name()}, not expected type ".PinMatrixRequestType::PinMatrixRequestType_Current()->name().")");
        }

        $pinMatrixAck = new PinMatrixAck();
        $pinMatrixAck->setPin($currentPinInput->getPin());

        return Message::pinMatrixAck($pinMatrixAck);
    }

    protected function provideCurrentPassphrase(CurrentPassphraseInputInterface $passphraseInput): Message
    {
        $passphraseAck = new PassphraseAck();
        $passphraseAck->setPassphrase($passphraseInput->getPassphrase());

        return Message::passphraseAck($passphraseAck);
    }
}
