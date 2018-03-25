<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Bridge\Codec\CallMessage;

use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;

class HexCodec
{
    private function stringToStream(string $stringToConvert)
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

    public function convertHexPayloadToBinary(StreamInterface $hexStream): StreamInterface
    {
        if ($hexStream->getSize() < 12) {
            throw new \Exception("Malformed data (size too small)");
        }

        return new Stream($this->stringToStream(pack("H*", $hexStream->getContents())));
    }

    public function parsePayload(StreamInterface $stream): array
    {
        list ($type) = array_values(unpack('n', $stream->read(2)));
        $stream->seek(2);

        list ($size) = array_values(unpack('N', $stream->read(4)));
        $stream->seek(6);

        if ($size > ($stream->getSize() - 6)) {
            throw new \Exception("Malformed data (sent more than size)");
        }

        return [$type, $this->stringToStream($stream->read($size))];
    }

    public function encode(int $messageType, \Protobuf\Message $protobuf): string
    {
        $stream = $protobuf->toStream();
        return unpack(
            'H*',
            sprintf(
                "%s%s",
                pack('nN', $messageType, $stream->getSize()),
                $stream->getContents()
            )
        )[1];
    }
}
