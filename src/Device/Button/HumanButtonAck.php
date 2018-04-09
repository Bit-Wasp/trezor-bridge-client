<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Button;

use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\Message;
use BitWasp\TrezorProto\ButtonRequest;
use BitWasp\TrezorProto\ButtonRequestType;

class HumanButtonAck extends ButtonAck
{
    public function acknowledge(Session $session, ButtonRequest $request, ButtonRequestType $expectedType)
    {
        $theirType = $request->getCode();
        if ($theirType->value() !== $expectedType->value()) {
            throw new \RuntimeException("Unexpected button request (expected: {$expectedType->name()}, got {$theirType->name()})");
        }

        return $session->sendMessage(Message::buttonAck(new \BitWasp\TrezorProto\ButtonAck()));
    }
}
