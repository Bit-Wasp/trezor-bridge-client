<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Device;

use BitWasp\TrezorProto\ClearSession;
use BitWasp\TrezorProto\GetAddress;
use BitWasp\TrezorProto\GetEntropy;
use BitWasp\TrezorProto\GetPublicKey;
use BitWasp\TrezorProto\Initialize;
use BitWasp\TrezorProto\InputScriptType;
use BitWasp\TrezorProto\Ping;
use BitWasp\TrezorProto\SignMessage;
use BitWasp\TrezorProto\VerifyMessage;

class RequestFactory
{
    public function initialize(): Initialize
    {
        return new Initialize();
    }

    public function getEntropy(int $bytes): GetEntropy
    {
        $getEntropy = new GetEntropy();
        $getEntropy->setSize($bytes);
        return $getEntropy;
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

    public function getAddress(string $coinName, array $path, InputScriptType $inScriptType, bool $showDisplay): GetAddress
    {
        $getAddress = new GetAddress();
        $getAddress->setCoinName($coinName);
        foreach ($path as $sequence) {
            $getAddress->addAddressN($sequence);
        }
        $getAddress->setShowDisplay($showDisplay);
        $getAddress->setScriptType($inScriptType);

        return $getAddress;
    }

    public function getKeyHashAddress(string $coinName, array $path, bool $showDisplay): GetAddress
    {
        return $this->getAddress($coinName, $path, InputScriptType::SPENDADDRESS(), $showDisplay);
    }

    public function getWitnessKeyHashAddress(string $coinName, array $path, bool $showDisplay): GetAddress
    {
        return $this->getAddress($coinName, $path, InputScriptType::SPENDWITNESS(), $showDisplay);
    }

    public function getP2shWitnessKeyHashAddress(string $coinName, array $path, bool $showDisplay): GetAddress
    {
        return $this->getAddress($coinName, $path, InputScriptType::SPENDP2SHWITNESS(), $showDisplay);
    }

    public function verifyMessage(string $coinName, string $address, string $signature, string $message): VerifyMessage
    {
        $verifyMsg = new VerifyMessage();
        $verifyMsg->setCoinName($coinName);
        $verifyMsg->setAddress($address);
        $verifyMsg->setSignature(\Protobuf\Stream::fromString($signature));
        $verifyMsg->setMessage(\Protobuf\Stream::fromString($message));
        return $verifyMsg;
    }

    public function rawSignMessage(string $coinName, array $path, InputScriptType $inScriptType, string $message): SignMessage
    {
        $signMessage = new SignMessage();
        $signMessage->setCoinName($coinName);
        foreach ($path as $sequence) {
            $signMessage->addAddressN($sequence);
        }
        $signMessage->setScriptType($inScriptType);
        $signMessage->setMessage(\Protobuf\Stream::fromString($message));
        return $signMessage;
    }

    public function signMessagePubKeyHash(string $coinName, array $path, string $message): SignMessage
    {
        return $this->rawSignMessage($coinName, $path, InputScriptType::SPENDADDRESS(), $message);
    }

    public function ping(string $nonce, bool $hasButtonProtect, bool $hasPinProtect, bool $hasPasswordProtect): Ping
    {
        $ping = new Ping();
        $ping->setMessage($nonce);
        $ping->setButtonProtection($hasButtonProtect);
        $ping->setPinProtection($hasPinProtect);
        $ping->setPassphraseProtection($hasPasswordProtect);
        return $ping;
    }

    public function clearSession(): ClearSession
    {
        return new ClearSession();
    }
}
