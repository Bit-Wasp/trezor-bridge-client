<?php

namespace BitWasp\Trezor\Device\Command;

use BitWasp\Trezor\Device\Message;
use BitWasp\Trezor\Device\PinInput\CurrentPassphraseInputInterface;
use BitWasp\Trezor\Device\PinInput\CurrentPinInputInterface;
use BitWasp\TrezorProto\ButtonAck;
use BitWasp\TrezorProto\ButtonRequest;
use BitWasp\TrezorProto\MessageType;
use BitWasp\TrezorProto\PassphraseAck;
use BitWasp\TrezorProto\PinMatrixAck;
use BitWasp\TrezorProto\PinMatrixRequest;
use BitWasp\TrezorProto\PinMatrixRequestType;

abstract class DeviceService
{
    protected function checkResponseType(Message $message, int $expectedType)
    {
        if (!$message->isType($expectedType)) {
            $ourType = MessageType::valueOf($expectedType);
            try {
                $theirType = MessageType::valueOf($message->getType());
            } catch (\Exception $e) {
                $theirType = 'Unknown';
            }

            throw new \RuntimeException("Unexpected response. Expected {$ourType}, but got {$theirType}");
        }
    }

    protected function checkPinRequestType(PinMatrixRequest $pinRequest, int $expectedType)
    {
        if ($pinRequest->getType()->value() !== $expectedType) {
            $ourType = PinMatrixRequestType::valueOf($expectedType);
            throw new \Exception("Unexpected pin matrix type (was {$pinRequest->getType()->name()}, not expected type {$ourType->name()}");
        }
    }

    protected function confirmWithButton(ButtonRequest $request, int $expectType): Message
    {
        $theirType = $request->getCode();
        if ($theirType->value() !== $expectType) {
            $ourType = MessageType::valueOf($expectType)->name();
            throw new \RuntimeException("Unexpected button request (expected: {$ourType}, got {$theirType->name()}");
        }

        return Message::buttonAck(new ButtonAck());
    }

    protected function provideCurrentPin(PinMatrixRequest $proto, CurrentPinInputInterface $currentPinInput): Message
    {
        $this->checkPinRequestType($proto, PinMatrixRequestType::PinMatrixRequestType_Current_VALUE);

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
