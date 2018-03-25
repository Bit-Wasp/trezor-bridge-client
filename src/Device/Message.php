<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device;

use BitWasp\TrezorProto\GetPublicKey;
use BitWasp\TrezorProto\Initialize;
use BitWasp\TrezorProto\MessageType;
use BitWasp\TrezorProto\PinMatrixAck;

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
}
