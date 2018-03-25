<?php

declare(strict_types=1);

namespace BitWasp\Trezor;

use BitWasp\Trezor\Exception\InactiveSessionException;
use BitWasp\Trezor\Message\Device;
use Protobuf\Message;

class Session
{
    /**
     * @var TrezorBridgeClient
     */
    private $client;

    /**
     * @var Device
     */
    private $device;

    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var bool
     */
    private $active = true;

    public function __construct(TrezorBridgeClient $client, Device $device, string $sessionId)
    {
        $this->client = $client;
        $this->device = $device;
        $this->sessionId = $sessionId;
    }

    private function checkSessionIsActive()
    {
        if (!$this->active) {
            throw new InactiveSessionException("Attempted command on inactive session");
        }
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function release()
    {
        $this->checkSessionIsActive();
        $result = $this->client->release($this->sessionId);
        $this->active = false;
    }

    public function getSessionId(): string
    {
        $this->checkSessionIsActive();
        return $this->sessionId;
    }

    public function getDevice(): Device
    {
        return $this->device;
    }

    public function sendMessage(int $messageType, Message $protobuf)
    {
        return $this->client->call(
            $this->getSessionId(),
            $messageType,
            $protobuf
        );
    }
}
