<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Bridge\Codec\CallMessage;

use BitWasp\Trezor\Bridge\Util\StreamUtil;
use Psr\Http\Message\StreamInterface;

class HexCodec
{
    /**
     * @var StreamUtil
     */
    private $stream;

    public function __construct()
    {
        $this->stream = new StreamUtil();
    }

    public function parsePayload(StreamInterface $stream): array
    {
        if ($stream->getSize() < 12) {
            throw new \Exception("Malformed data (size too small)");
        }

        $stream = $this->stream->hex2bin($stream);

        list ($type) = array_values(unpack('n', $stream->read(2)));
        $stream->seek(2);

        list ($size) = array_values(unpack('N', $stream->read(4)));
        $stream->seek(6);

        if ($size > ($stream->getSize() - 6)) {
            throw new \Exception("Malformed data (sent more than size)");
        }

        return [$type, $this->stream->createStream($stream->read($size))];
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
