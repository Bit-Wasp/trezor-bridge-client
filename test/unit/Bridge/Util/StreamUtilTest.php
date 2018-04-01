<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Bridge\Util;

use BitWasp\Test\Trezor\TestCase;
use BitWasp\Trezor\Bridge\Exception\InvalidMessageException;
use BitWasp\Trezor\Bridge\Util\StreamUtil;
use GuzzleHttp\Psr7\Stream;

class StreamUtilTest extends TestCase
{
    public function testHex2BinRequiresHex()
    {
        $streamUtil = new StreamUtil();
        $stream = new Stream($streamUtil->createStream("y"));

        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage("Invalid hex as input");

        $streamUtil->hex2bin($stream);
    }
}
