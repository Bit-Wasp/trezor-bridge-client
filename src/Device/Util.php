<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device;

use BitWasp\TrezorProto\CoinType;
use BitWasp\TrezorProto\Features;

class Util
{
    /**
     * @param string $coinName
     * @param Features $features
     * @return CoinType|null
     */
    public static function networkByCoinName(string $coinName, Features $features)
    {
        foreach ($features->getCoinsList() as $coinType) {
            /** @var CoinType $coinType */
            if ($coinType->getCoinName() === $coinName) {
                return $coinType;
            }
        }

        return null;
    }

    /**
     * @param string $shortcut
     * @param Features $coinTypes
     * @return CoinType|null
     */
    public static function networkByCoinShortcut(string $shortcut, Features $coinTypes)
    {
        foreach ($coinTypes->getCoinsList() as $coinType) {
            /** @var CoinType $coinType */
            if ($coinType->getCoinShortcut() === $shortcut) {
                return $coinType;
            }
        }

        return null;
    }
}
