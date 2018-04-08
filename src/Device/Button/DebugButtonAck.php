<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device\Button;

use BitWasp\Trezor\Bridge\Session;
use BitWasp\Trezor\Device\DebugMessage;
use BitWasp\Trezor\Device\Message;
use BitWasp\TrezorProto\ButtonRequest;
use BitWasp\TrezorProto\ButtonRequestType;
use BitWasp\TrezorProto\DebugLinkDecision;
use BitWasp\TrezorProto\DebugLinkGetState;
use BitWasp\TrezorProto\DebugLinkStop;
use BitWasp\TrezorProto\Success;

class DebugButtonAck extends ButtonAck
{
    private $debug;

    public function __construct(Session $debugSession)
    {
        $this->debug = $debugSession;
    }

    public function acknowledge(Session $session, ButtonRequest $request, ButtonRequestType $expectedType)
    {
        $theirType = $request->getCode();
        if ($theirType->value() !== $expectedType->value()) {
            throw new \RuntimeException("Unexpected button request (expected: {$expectedType->name()}, got {$theirType->name()})");
        }

        fwrite(STDERR, microtime() . " - debugButtonAck.sending button ack (async)\n");
        $t1 = microtime(true);
        $ack = new \BitWasp\TrezorProto\ButtonAck();

        $decision = new DebugLinkDecision();
        $decision->setYesNo(true);

        fwrite(STDERR, microtime() . " - debugButtonAck.sending DECISION (async)\n");
        $t1 = microtime(true);

        $success = $session->sendMessageAsync(Message::buttonAck($ack));
        $debug = $this->debug->sendMessageAsync(DebugMessage::decision($decision), [
            'Connection' => 'close',
        ]);

        fwrite(STDERR, microtime() . " - debugButtonAck.DECISION async took ".(microtime(true)-$t1).PHP_EOL);

        fwrite(STDERR, "create promise");
        $val = null;
        $success->then(function (Success $success) use (&$val) {
            fwrite(STDERR, "success resolved");
            $val = $success;
        });
        fwrite(STDERR, "wait for success");
        $success->wait(true);
        fwrite(STDERR, "DONE waiting");

        return $val;
    }
}
