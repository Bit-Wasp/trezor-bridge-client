<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device;

use BitWasp\TrezorProto\ButtonAck;
use BitWasp\TrezorProto\ClearSession;
use BitWasp\TrezorProto\GetAddress;
use BitWasp\TrezorProto\GetEntropy;
use BitWasp\TrezorProto\GetPublicKey;
use BitWasp\TrezorProto\Initialize;
use BitWasp\TrezorProto\LoadDevice;
use BitWasp\TrezorProto\MessageType;
use BitWasp\TrezorProto\PassphraseAck;
use BitWasp\TrezorProto\Ping;
use BitWasp\TrezorProto\PinMatrixAck;
use BitWasp\TrezorProto\SignMessage;
use BitWasp\TrezorProto\VerifyMessage;

class Message extends MessageBase
{
    public static function getPublicKey(GetPublicKey $getPublicKey): self
    {
        return new self(
            MessageType::MessageType_GetPublicKey(),
            $getPublicKey
        );
    }

    public static function getAddress(GetAddress $getAddress): self
    {
        return new self(
            MessageType::MessageType_GetAddress(),
            $getAddress
        );
    }

    public static function getEntropy(GetEntropy $getEntropy): self
    {
        return new self(
            MessageType::MessageType_GetEntropy(),
            $getEntropy
        );
    }

    public static function pinMatrixAck(PinMatrixAck $pinAck): self
    {
        return new self(
            MessageType::MessageType_PinMatrixAck(),
            $pinAck
        );
    }

    public static function passphraseAck(PassphraseAck $passphraseAck): self
    {
        return new self(
            MessageType::MessageType_PassphraseAck(),
            $passphraseAck
        );
    }

    public static function initialize(Initialize $initialize): self
    {
        return new self(
            MessageType::MessageType_Initialize(),
            $initialize
        );
    }

    public static function buttonAck(ButtonAck $ack): self
    {
        return new self(
            MessageType::MessageType_ButtonAck(),
            $ack
        );
    }

    public static function signMessage(SignMessage $signMessage): self
    {
        return new self(
            MessageType::MessageType_SignMessage(),
            $signMessage
        );
    }

    public static function verifyMessage(VerifyMessage $verifyMsg): self
    {
        return new self(
            MessageType::MessageType_VerifyMessage(),
            $verifyMsg
        );
    }

    public static function ping(Ping $ping): self
    {
        return new self(
            MessageType::MessageType_Ping(),
            $ping
        );
    }

    public static function clearSession(ClearSession $clear): self
    {
        return new self(
            MessageType::MessageType_ClearSession(),
            $clear
        );
    }

    public static function loadDevice(LoadDevice $clear): self
    {
        return new self(
            MessageType::MessageType_LoadDevice(),
            $clear
        );
    }
}
