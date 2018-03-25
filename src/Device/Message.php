<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device;

use BitWasp\TrezorProto\ButtonAck;
use BitWasp\TrezorProto\GetAddress;
use BitWasp\TrezorProto\GetEntropy;
use BitWasp\TrezorProto\GetPublicKey;
use BitWasp\TrezorProto\Initialize;
use BitWasp\TrezorProto\MessageType;
use BitWasp\TrezorProto\PinMatrixAck;
use BitWasp\TrezorProto\SignMessage;
use BitWasp\TrezorProto\VerifyMessage;

class Message
{
    /**
     * @var int
     */
    private $type;

    /**
     * @var \Protobuf\Message
     */
    private $proto;

    public function __construct(int $messageType, \Protobuf\Message $protobuf)
    {
        $this->type = $messageType;
        $this->proto = $protobuf;
    }

    public function isType(int $messageType): bool
    {
        return $this->type === $messageType;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getProto(): \Protobuf\Message
    {
        return $this->proto;
    }

    public static function getPublicKey(GetPublicKey $getPublicKey): self
    {
        return new self(
            MessageType::MessageType_GetPublicKey_VALUE,
            $getPublicKey
        );
    }

    public static function getAddress(GetAddress $getAddress): self
    {
        return new self(
            MessageType::MessageType_GetAddress_VALUE,
            $getAddress
        );
    }

    public static function getEntropy(GetEntropy $getEntropy): self
    {
        return new self(
            MessageType::MessageType_GetEntropy_VALUE,
            $getEntropy
        );
    }

    public static function pinMatrixAck(PinMatrixAck $pinAck): self
    {
        return new self(
            MessageType::MessageType_PinMatrixAck_VALUE,
            $pinAck
        );
    }

    public static function initialize(Initialize $initialize): self
    {
        return new self(
            MessageType::MessageType_Initialize_VALUE,
            $initialize
        );
    }

    public static function buttonAck(ButtonAck $ack): self
    {
        return new self(
            MessageType::MessageType_ButtonAck_VALUE,
            $ack
        );
    }

    public static function signMessage(SignMessage $signMessage): self
    {
        return new self(
            MessageType::MessageType_SignMessage_VALUE,
            $signMessage
        );
    }

    public static function verifyMessage(VerifyMessage $verifyMsg): self
    {
        return new self(
            MessageType::MessageType_VerifyMessage_VALUE,
            $verifyMsg
        );
    }
}
