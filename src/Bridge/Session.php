<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Bridge;

use BitWasp\Trezor\Bridge\Exception\InactiveSessionException;
use BitWasp\Trezor\Bridge\Message\Device;
use BitWasp\Trezor\Device\Exception\CommandFailureException;
use BitWasp\Trezor\Device\Message;
use BitWasp\TrezorProto\Failure;

class Session
{
    /**
     * @var Client
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

    public function __construct(Client $client, Device $device, string $sessionId)
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

    public function sendMessage(Message $message): Message
    {
        $message = $this->client->call($this->getSessionId(), $message);
        $proto = $message->getProto();
        if ($proto instanceof Failure) {
            CommandFailureException::handleFailure($proto);
        }

        return $message;
    }
}
