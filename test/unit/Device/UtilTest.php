<?php

declare(strict_types=1);

namespace BitWasp\Test\Trezor\Device;

use BitWasp\Test\Trezor\TestCase;
use BitWasp\Trezor\Device\Util;
use BitWasp\TrezorProto\CoinType;
use BitWasp\TrezorProto\Features;

class UtilTest extends TestCase
{
    public function testFindNetworkByCoinName()
    {
        $coinNames = ['Bitcoin', 'Litecoin', 'Dogecoin', 'Zcash'];
        $coinTypes = [];
        foreach ($coinNames as $coinName) {
            $coinType = new CoinType();
            $coinType->setCoinName($coinName);
            $coinTypes[] = $coinType;
        }

        $features = new Features();
        $features->setCoinsList(new \Protobuf\MessageCollection($coinTypes));

        $findBitcoin = Util::networkByCoinName('Litecoin', $features);
        $this->assertEquals('Litecoin', $findBitcoin->getCoinName());

        $findBitcoin = Util::networkByCoinName('Dogecoin', $features);
        $this->assertEquals('Dogecoin', $findBitcoin->getCoinName());
    }

    public function testFailFindNetworkByCoinName()
    {
        $findCoin = "Bcash";
        $coinNames = ['Bitcoin', 'Litecoin', 'Dogecoin', 'Zcash'];
        $coinTypes = [];
        foreach ($coinNames as $coinName) {
            $coinType = new CoinType();
            $coinType->setCoinName($coinName);
            $coinTypes[] = $coinType;
        }

        $features = new Features();
        $features->setCoinsList(new \Protobuf\MessageCollection($coinTypes));

        $this->assertNull(Util::networkByCoinName($findCoin, $features));
    }

    public function testFindNetworkByCoinShortcut()
    {
        $coinNames = ['BTC', 'LTC', 'DOGE', 'ZCH'];
        $coinTypes = [];
        foreach ($coinNames as $coinName) {
            $coinType = new CoinType();
            $coinType->setCoinShortcut($coinName);
            $coinTypes[] = $coinType;
        }

        $features = new Features();
        $features->setCoinsList(new \Protobuf\MessageCollection($coinTypes));

        $findBitcoin = Util::networkByCoinShortcut('LTC', $features);
        $this->assertEquals('LTC', $findBitcoin->getCoinShortcut());

        $findBitcoin = Util::networkByCoinShortcut('DOGE', $features);
        $this->assertEquals('DOGE', $findBitcoin->getCoinShortcut());
    }

    public function testFailFindNetworkByCoinShortcut()
    {
        $findCoin = "BCH";
        $coinNames = ['BTC', 'LTC', 'DOGE', 'ZCH'];
        $coinTypes = [];
        foreach ($coinNames as $coinName) {
            $coinType = new CoinType();
            $coinType->setCoinShortcut($coinName);
            $coinTypes[] = $coinType;
        }

        $features = new Features();
        $features->setCoinsList(new \Protobuf\MessageCollection($coinTypes));

        $this->assertNull(Util::networkByCoinShortcut($findCoin, $features));
    }
}
