<?php

namespace BitWasp\Trezor\Device\Command;

use BitWasp\Trezor\Device\Message;
use BitWasp\TrezorProto\MessageType;
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
}
