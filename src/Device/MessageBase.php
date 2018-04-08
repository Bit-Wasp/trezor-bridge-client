<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device;

use BitWasp\TrezorProto\MessageType;

abstract class MessageBase
{
    /**
     * @var MessageType
     */
    private $type;

    /**
     * @var \Protobuf\Message
     */
    private $proto;

    public function __construct(
        MessageType $messageType,
        \Protobuf\Message $protobuf
    ) {
        $this->type = $messageType;
        $this->proto = $protobuf;
    }

    public function getType(): int
    {
        return $this->type->value();
    }

    public function getProto(): \Protobuf\Message
    {
        return $this->proto;
    }
}
