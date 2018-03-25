<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Bridge;

use BitWasp\Trezor\Bridge\Exception\InactiveSessionException;
use BitWasp\Trezor\Bridge\Message\Device;
use BitWasp\Trezor\Device\Exception\FailureException;
use BitWasp\Trezor\Device\Message;
use BitWasp\TrezorProto\Failure;
use Protobuf\Message as ProtoMessage;

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

    /**
     * Session constructor.
     * @param Client $client
     * @param Device $device
     * @param string $sessionId
     */
    public function __construct(Client $client, Device $device, string $sessionId)
    {
        $this->client = $client;
        $this->device = $device;
        $this->sessionId = $sessionId;
    }

    /**
     * @throws InactiveSessionException
     */
    private function assertSessionIsActive()
    {
        if (!$this->active) {
            throw new InactiveSessionException("Attempted command on inactive session");
        }
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @throws InactiveSessionException
     */
    public function release()
    {
        $this->assertSessionIsActive();
        $result = $this->client->release($this->sessionId);
        $this->active = false;
    }

    /**
     * @return string
     * @throws InactiveSessionException
     */
    public function getSessionId(): string
    {
        $this->assertSessionIsActive();
        return $this->sessionId;
    }

    /**
     * @return Device
     */
    public function getDevice(): Device
    {
        return $this->device;
    }

    /**
     * @param Message $message
     * @return ProtoMessage
     * @throws FailureException
     * @throws InactiveSessionException
     */
    public function sendMessage(Message $message): ProtoMessage
    {
        $message = $this->client->call($this->getSessionId(), $message);
        $proto = $message->getProto();
        if ($proto instanceof Failure) {
            FailureException::handleFailure($proto);
        }

        return $proto;
    }
}
