<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Bridge;

use BitWasp\Trezor\Bridge\Exception\InvalidMessageException;
use BitWasp\Trezor\Bridge\Exception\SchemaValidationException;
use BitWasp\Trezor\Bridge\Message\Device;
use BitWasp\Trezor\Bridge\Message\ListDevicesResponse;
use BitWasp\Trezor\Bridge\Message\ListenResponse;
use BitWasp\Trezor\Bridge\Message\VersionResponse;
use BitWasp\Trezor\Bridge\Schema\ValidatorFactory;
use BitWasp\Trezor\Bridge\Http\HttpClient;
use BitWasp\Trezor\Device\Message;
use Psr\Http\Message\ResponseInterface;

class Client
{
    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @var ValidatorFactory
     */
    private $validation;

    /**
     * TrezorClient constructor.
     * @param HttpClient $client
     */
    public function __construct(HttpClient $client)
    {
        $this->client = $client;
        $this->validation = new ValidatorFactory();
    }

    /**
     * @param \Psr\Http\Message\StreamInterface $body
     * @return mixed
     */
    protected function parseResponse(\Psr\Http\Message\StreamInterface $body)
    {
        $data = json_decode($body->getContents());
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidMessageException("Invalid JSON received in response");
        }
        return $data;
    }

    /**
     * @param \stdClass $data
     * @param \stdClass $schema
     */
    protected function validateSchema($data, \stdClass $schema)
    {
        $validator = new \JsonSchema\Validator;
        $validator->coerce($data, $schema);
        if ($validator->isValid()) {
            return;
        }

        throw new SchemaValidationException($validator->getErrors());
    }

    /**
     * @param ResponseInterface $response - Response message
     * @param \stdClass $schema - JSON schema to validate against
     * @return mixed
     */
    protected function processResponse(ResponseInterface $response, \stdClass $schema)
    {
        $result = $this->parseResponse($response->getBody());
        $this->validateSchema($result, $schema);
        return $result;
    }

    /**
     * @return VersionResponse
     */
    public function bridgeVersion(): VersionResponse
    {
        $result = $this->processResponse(
            $this->client->bridgeVersion(),
            $this->validation->versionResponse()
        );

        return new VersionResponse($result);
    }

    /**
     * @return ListDevicesResponse
     */
    public function listDevices(): ListDevicesResponse
    {
        $result = $this->processResponse(
            $this->client->listDevices(),
            $this->validation->listDevicesResponse()
        );

        $devices = [];
        foreach ($result as $device) {
            $devices[] = new Device($device);
        }

        return new ListDevicesResponse($devices);
    }

    public function listen(Device ...$devices): ListenResponse
    {
        $result = $this->processResponse(
            $this->client->listen(...$devices),
            $this->validation->listDevicesResponse()
        );

        $devices = [];
        foreach ($result as $device) {
            $devices[] = new Device($device);
        }

        return new ListenResponse($devices);
    }

    /**
     * @param Device $device
     * @return Session
     */
    public function acquire(Device $device): Session
    {
        $result = $this->processResponse(
            $this->client->acquire($device),
            $this->validation->acquireResponse()
        );

        return new Session($this, $device, $result->session);
    }

    public function release(string $sessionId): bool
    {
        $this->processResponse(
            $this->client->release($sessionId),
            $this->validation->releaseResponse()
        );

        return true;
    }

    public function call(string $sessionId, Message $message): Message
    {
        return $this->client->call($sessionId, $message);
    }
}
