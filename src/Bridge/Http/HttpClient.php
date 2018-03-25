<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Bridge\Http;

use BitWasp\Trezor\Bridge\Codec\CallMessage;
use BitWasp\Trezor\Bridge\Message\Device;
use BitWasp\Trezor\Device\Message;
use BitWasp\TrezorProto\MessageType;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    /**
     * @var GuzzleClient
     */
    private $client;

    /**
     * Base headers to use when the request is
     * simple JSON
     *
     * @var array
     */
    private $jsonHeaders = [
        'Accept' => 'application/json',
    ];

    /**
     * Encoder for serializing the call payload & response
     *
     * @var CallMessage\HexCodec
     */
    private $callCodec;

    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
        $this->callCodec = new CallMessage\HexCodec();
    }

    public static function forUri(string $uri): self
    {
        return new self(new GuzzleClient([
            'base_uri' => $uri,
            'headers' => [
                'Origin' => 'http://localhost:5000',
            ],
        ]));
    }

    public function bridgeVersion(): ResponseInterface
    {
        return $this->client->post('/', [
            'headers' => $this->jsonHeaders,
        ]);
    }

    public function listDevices(): ResponseInterface
    {
        return $this->client->post('/enumerate', [
            'headers' => $this->jsonHeaders,
        ]);
    }

    public function listen(Device ...$devices): ResponseInterface
    {
        return $this->client->post('/listen', [
            'headers' => $this->jsonHeaders,
            'json' => array_map(function (Device $device): \stdClass {
                return $device->getObject();
            }, $devices),
        ]);
    }

    public function acquire(Device $device): ResponseInterface
    {
        if ($device->getSession()) {
            $prevSession = $device->getSession();
        } else {
            $prevSession = "null";
        }

        return $this->client->post("/acquire/{$device->getPath()}/{$prevSession}", [
            'headers' => $this->jsonHeaders,
        ]);
    }

    public function release(string $sessionId): ResponseInterface
    {
        return $this->client->post("/release/{$sessionId}", [
            'headers' => $this->jsonHeaders,
        ]);
    }

    public function call(string $sessionId, Message $message): Message
    {
        static $prefixLen;
        if (null === $prefixLen) {
            $prefixLen = strlen("MessageType_");
        }

        $response = $this->client->post("/call/{$sessionId}", [
            'body' => $this->callCodec->encode($message->getType(), $message->getProto()),
        ]);

        list ($type, $result) = $this->callCodec->parsePayload(
            $this->callCodec->convertHexPayloadToBinary($response->getBody())
        );

        $messageType = MessageType::valueOf($type);
        $protoType = substr($messageType->name(), $prefixLen);
        $reader = ["\\BitWasp\\TrezorProto\\{$protoType}", 'fromStream'];
        assert(class_exists($reader[0]));

        $protobuf = call_user_func($reader, $result);
        return new Message($messageType, $protobuf);
    }
}
