<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Bridge;

use BitWasp\Trezor\Bridge\Exception\InactiveSessionException;
use BitWasp\Trezor\Bridge\Message\Device;
use BitWasp\Trezor\Device\Exception\FailureException;
use BitWasp\Trezor\Device\Message;
use BitWasp\Trezor\Device\MessageBase;
use BitWasp\TrezorProto\Failure;
use GuzzleHttp\Promise\PromiseInterface;
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
        $this->client->release($this->sessionId);
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
     * @param MessageBase $message
     * @return ProtoMessage
     * @throws FailureException
     * @throws InactiveSessionException
     */
    public function sendMessageAsync(MessageBase $request, array $headers = []): PromiseInterface
    {
        $this->assertSessionIsActive();
        fwrite(STDERR, "(sending {$request->getType()})\n");
        return $this->client->callAsync($this->getSessionId(), $request, $headers)
            ->then(function (Message $message) use ($request) {
                fwrite(STDERR, "(for {$request->getType()}) got message back {$message->getType()})\n");
                $proto = $message->getProto();
                if ($proto instanceof Failure) {
                    fwrite(STDERR, "(failure {$request->getType()}) got message back {$message->getType()})\n");
                    FailureException::handleFailure($proto);
                }

                return $proto;
            });
    }
    /**
     * @param MessageBase $message
     * @return ProtoMessage
     * @throws FailureException
     * @throws InactiveSessionException
     */
    public function sendMessage(MessageBase $message): ProtoMessage
    {
        $this->assertSessionIsActive();
        return $this->sendMessageAsync($message)
            ->wait(true);
    }
}
