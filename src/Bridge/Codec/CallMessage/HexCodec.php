<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Bridge\Codec\CallMessage;

use BitWasp\Trezor\Bridge\Exception\InvalidMessageException;
use Psr\Http\Message\StreamInterface;

class HexCodec
{
    private function intcmp(int $a, int $b): int
    {
        return ($a < $b) ? -1 : (($a > $b) ? 1 : 0);
    }

    private function hex2bin(StreamInterface $stream): \Protobuf\Stream
    {
        $hex = $stream->getContents();
        if (!ctype_xdigit($hex)) {
            throw new InvalidMessageException("Invalid hex as input");
        }

        return \Protobuf\Stream::fromString(pack("H*", $hex));
    }

    public function parsePayload(StreamInterface $stream): array
    {
        if ($stream->getSize() < 12) {
            throw new InvalidMessageException("Malformed data (size too small)");
        }

        $stream = $this->hex2bin($stream);

        // relies on php returning the variables in order defined in unpack string
        list ($type, $size) = array_values(unpack('n1type/N1size', $stream->read(6)));
        $stream->seek(6);

        $lCmp = $this->intcmp($stream->getSize() - 6, $size);
        if ($lCmp < 0) {
            throw new InvalidMessageException("Malformed data (not enough data)");
        } else if ($lCmp > 0) {
            throw new InvalidMessageException("Malformed data (too much data)");
        }

        return [$type, \Protobuf\Stream::wrap($stream->read($size))];
    }

    public function encode(int $messageType, \Protobuf\Message $protobuf): string
    {
        $stream = $protobuf->toStream();
        return unpack('H*', pack('nN', $messageType, $stream->getSize()) . $stream->getContents())[1];
    }
}
