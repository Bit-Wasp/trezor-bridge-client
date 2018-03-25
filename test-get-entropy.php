<?php

declare(strict_types=1);

use BitWasp\Trezor\Device\Command\GetAddressService;
use BitWasp\Trezor\Device\Command\GetEntropyService;
use BitWasp\Trezor\Device\Command\GetPublicKeyService;
use BitWasp\Trezor\Device\Command\InitializeService;
use BitWasp\Trezor\Device\PinInput\CurrentPinInput;
use BitWasp\Trezor\Device\RequestFactory;
use BitWasp\TrezorProto\CoinType;
use BitWasp\TrezorProto\Initialize;
use BitWasp\TrezorProto\InputScriptType;

require "vendor/autoload.php";

$useNetwork = "BTC";

$trezor = \BitWasp\Trezor\Bridge\Client::fromUri("http://localhost:21325");

echo "list devices\n";
$devices = $trezor->listDevices();
if (empty($devices)) {
    throw new \Exception("Error! No devices connected!");
}

echo "first device\n";
$firstDevice = $devices->devices()[0];

print_r($firstDevice);

echo "acquire device!\n";
$session = $trezor->acquire($firstDevice);
$sessionId = $session->getSessionId();
$reqFactory = new RequestFactory();

echo "sessionId: {$sessionId}\n";
echo "devicePath: {$session->getDevice()->getPath()}\n";

echo "initialize device\n";
$initializeCmd = new InitializeService();
$features = $initializeCmd->call($session, $reqFactory->initialize());

$btcNetwork = null;
foreach ($features->getCoinsList() as $coin) {
    /** @var CoinType $coin */
    if ($coin->getCoinShortcut() === $useNetwork) {
        $btcNetwork = $coin;
    }
}

if (!$btcNetwork) {
    throw new \RuntimeException("Failed to find requested network ({$useNetwork})");
}

echo "get entropy\n";
$entropyService = new GetEntropyService();
$getEntropy = $reqFactory->getEntropy(32);
$entropy = $entropyService->call($session, $getEntropy);

var_dump($entropy);

$session->release();
