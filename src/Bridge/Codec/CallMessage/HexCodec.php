<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Bridge\Codec\CallMessage;

use BitWasp\Trezor\Bridge\Exception\InvalidMessageException;
use Psr\Http\Message\StreamInterface;

class HexCodec
{
    /**
     * Implement integer comparison
     * Returns: 0 if  $a === $b
     *         -1 if  $a  <  $b
     *         +1 if  $a  >  $b
     *
     * @param int $a
     * @param int $b
     * @return int
     */
    private function intcmp(int $a, int $b): int
    {
        return ($a < $b) ? -1 : (($a > $b) ? 1 : 0);
    }

    public function parsePayload(StreamInterface $stream): array
    {
        if ($stream->getSize() < 12) {
            throw new InvalidMessageException("Malformed data (size too small)");
        }

        $hex = $stream->getContents();
        if (!ctype_xdigit($hex)) {
            throw new InvalidMessageException("Invalid hex as input");
        }

        $stream = \Protobuf\Stream::fromString(pack("H*", $hex));

        list ($type) = array_values(unpack('n', $stream->read(2)));
        $stream->seek(2);

        list ($size) = array_values(unpack('N', $stream->read(4)));
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
