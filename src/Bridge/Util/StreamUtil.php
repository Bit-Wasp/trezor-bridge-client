<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Bridge\Util;

use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;

class StreamUtil
{
    /**
     * @param string $stringToConvert
     * @return resource
     */
    public function createStream(string $stringToConvert)
    {
        $stream = fopen('php://memory', 'w+');
        if (!is_resource($stream)) {
            throw new \RuntimeException("Failed to create stream");
        }

        $wrote = fwrite($stream, $stringToConvert);
        if ($wrote !== strlen($stringToConvert)) {
            throw new \RuntimeException("Failed to write to stream");
        }

        rewind($stream);
        return $stream;
    }

    /**
     * @param StreamInterface $hexStream
     * @return StreamInterface
     */
    public function hex2bin(StreamInterface $hexStream): StreamInterface
    {
        $hex = $hexStream->getContents();
        if (!ctype_xdigit($hex)) {
            throw new \RuntimeException("Invalid hex as input");
        }

        return new Stream($this->createStream(pack("H*", $hex)));
    }
}
