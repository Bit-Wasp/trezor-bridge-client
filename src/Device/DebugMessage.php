<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device;

use BitWasp\TrezorProto\DebugLinkDecision;
use BitWasp\TrezorProto\DebugLinkGetState;
use BitWasp\TrezorProto\DebugLinkMemoryRead;
use BitWasp\TrezorProto\DebugLinkStop;
use BitWasp\TrezorProto\MessageType;

class DebugMessage extends MessageBase
{
    public static function getState(DebugLinkGetState $getState): self
    {
        return new self(
            MessageType::MessageType_DebugLinkGetState(),
            $getState
        );
    }
    public static function stop(DebugLinkStop $stop)
    {
        return new self(
            MessageType::MessageType_DebugLinkStop(),
            $stop
        );
    }
    public static function decision(DebugLinkDecision $decision)
    {
        return new self(
            MessageType::MessageType_DebugLinkDecision(),
            $decision
        );
    }

    public static function memoryRead(DebugLinkMemoryRead $memoryRead): self
    {
        return new self(
            MessageType::MessageType_DebugLinkMemoryRead(),
            $memoryRead
        );
    }
}
