<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Bridge\Codec\CallMessage;

use BitWasp\Test\Trezor\Bridge\Message\TestCase;
use BitWasp\Trezor\Bridge\Codec\CallMessage\HexCodec;
use BitWasp\Trezor\Bridge\Exception\InvalidMessageException;

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
        $stream = \GuzzleHttp\Psr7\stream_for(str_repeat("0", $invalidLength));
        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage("Malformed data (size too small)");
        $hexCodec->parsePayload($stream);
    }

    public function testRejectsExcessiveData()
    {
        $hexCodec = new HexCodec();
        $stream = \GuzzleHttp\Psr7\stream_for("00000000000001");
        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage("Malformed data (too much data)");
        $hexCodec->parsePayload($stream);
    }

    public function testRequiresValidHex()
    {
        $hexCodec = new HexCodec();
        $stream = \GuzzleHttp\Psr7\stream_for("yadayadayadayada");
        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage("Invalid hex as input");
        $hexCodec->parsePayload($stream);
    }

    public function testRejectsWithTooLitleData()
    {
        $hexCodec = new HexCodec();
        $stream = \GuzzleHttp\Psr7\stream_for("000001000000");
        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage("Malformed data (not enough data)");
        $hexCodec->parsePayload($stream);
    }
}
