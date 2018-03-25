<?php

namespace BitWasp\Trezor\Device;

use BitWasp\TrezorProto\GetPublicKey;
use BitWasp\TrezorProto\Initialize;

class RequestFactory
{
    public function initialize(): Initialize
    {
        return new Initialize();
    }

    public function getPublicKey(string $coinName, array $path, string $curveName = null): GetPublicKey
    {
        $getPublicKey = new GetPublicKey();
        $getPublicKey->setCoinName($coinName);
        foreach ($path as $sequence) {
            $getPublicKey->addAddressN($sequence);
        }
        if ($curveName) {
            $getPublicKey->setEcdsaCurveName($curveName);
        }
        return $getPublicKey;
    }
}
