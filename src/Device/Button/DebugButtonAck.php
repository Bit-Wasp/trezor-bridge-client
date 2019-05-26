<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Button;

use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\DebugMessage;
use BitWasp\Trezor\Device\Message;
use BitWasp\TrezorProto\ButtonRequest;
use BitWasp\TrezorProto\ButtonRequestType;
use BitWasp\TrezorProto\DebugLinkDecision;

class DebugButtonAck extends ButtonAck
{
    /**
     * @var Session
     */
    private $debug;

    /**
     * @var bool
     */
    private $button;

    public function __construct(
        Session $debugSession,
        bool $button
    ) {
        $this->debug = $debugSession;
        $this->button = $button;
    }

    public function acknowledge(
        Session $session,
        ButtonRequest $request,
        ButtonRequestType $expectedType
    ): \Protobuf\Message {
        $theirType = $request->getCode();
        if ($theirType->value() !== $expectedType->value()) {
            throw new \RuntimeException("Unexpected button request (expected: {$expectedType->name()}, got {$theirType->name()})");
        }

        $ack = new \BitWasp\TrezorProto\ButtonAck();

        $decision = new DebugLinkDecision();
        $decision->setYesNo($this->button);

        $success = $session->sendMessageAsync(Message::buttonAck($ack));
        $this->debug->postMessage(DebugMessage::decision($decision));
        return $success->wait(true);
    }
}
