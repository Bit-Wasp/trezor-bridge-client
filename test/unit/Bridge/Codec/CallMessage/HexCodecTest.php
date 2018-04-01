<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Bridge\Codec\CallMessage;

use BitWasp\Test\Trezor\Bridge\Message\TestCase;
use BitWasp\Trezor\Bridge\Codec\CallMessage\HexCodec;
use BitWasp\Trezor\Bridge\Exception\InvalidMessageException;
use BitWasp\Trezor\Bridge\Util\StreamUtil;
use GuzzleHttp\Psr7\Stream;

class HexCodecTest extends TestCase
{
    public function getInvalidLengths(): array
    {
        return [
            [0],
            [1],
            [2],
            [3],
            [4],
            [5],
        ];
    }

    /**
     * @dataProvider getInvalidLengths
     */
    public function testRequires(int $invalidLength)
    {
        $hexCodec = new HexCodec();
        $streamUtil = new StreamUtil();
        $stream = $streamUtil->createStream(str_repeat("0", $invalidLength));
        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage("Malformed data (size too small)");
        $hexCodec->parsePayload(new Stream($stream));
    }

    public function testRejectsExcessiveData()
    {
        $hexCodec = new HexCodec();
        $streamUtil = new StreamUtil();
        $stream = $streamUtil->createStream("00000000000001");
        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage("Malformed data (too much data)");
        $hexCodec->parsePayload(new Stream($stream));
    }

    public function testRejectsWithTooLitleData()
    {
        $hexCodec = new HexCodec();
        $streamUtil = new StreamUtil();
        $stream = $streamUtil->createStream("000001000000");
        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage("Malformed data (not enough data)");
        $hexCodec->parsePayload(new Stream($stream));
    }
}
