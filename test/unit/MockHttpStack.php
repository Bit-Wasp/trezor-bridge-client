<?php

namespace BitWasp\Test\Trezor;

use BitWasp\Trezor\Bridge\Http\HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MockHttpStack
{
    /**
     * @var array[] $requestLog
     */
    private $requestLog = [];

    /**
     * @var HttpClient
     */
    private $client;

    public function __construct(
        string $trezorUri,
        array $options = [],
        ResponseInterface... $responses
    ) {
        $handler = new MockHandler($responses);
        $stack = HandlerStack::create($handler);
        $stack->push(Middleware::history($this->requestLog));
        $this->client = HttpClient::forUri($trezorUri, array_merge($options, [
            'handler' => $stack,
        ]));
    }

    public function getRequestLogs(): array
    {
        return $this->requestLog;
    }

    public function getRequest(int $i): RequestInterface
    {
        return $this->getRequestLog($i)['request'];
    }

    public function getResponse(int $i): RequestInterface
    {
        return $this->getRequestLog($i)['request'];
    }

    public function getRequestLog(int $i): array
    {
        if (!array_key_exists($i, $this->requestLog)) {
            throw new \RuntimeException("Nonexistant request log {$i}");
        }
        return $this->requestLog[$i];
    }

    public function getClient(): HttpClient
    {
        return $this->client;
    }
}
