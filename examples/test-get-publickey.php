<?php

declare(strict_types=1);

use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Http\HttpClient;
use BitWasp\Trezor\Device\Command\GetPublicKeyService;
use BitWasp\Trezor\Device\Command\InitializeService;
use BitWasp\Trezor\Device\UserInput\CurrentPinInput;
use BitWasp\Trezor\Device\RequestFactory;
use BitWasp\Trezor\Device\UserInput\FgetsUserInputRequest;
use BitWasp\Trezor\Device\Util;

require __DIR__ . "/../vendor/autoload.php";

$useNetwork = "BTC";
$httpClient = HttpClient::forUri("http://localhost:21325");
$trezor = new Client($httpClient);

$hardened = pow(2, 31)-1;
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

$initializeCmd = new InitializeService();
$features = $initializeCmd->call($session, $reqFactory->initialize());

if (!($btcNetwork = Util::networkByCoinShortcut($useNetwork, $features))) {
    throw new \RuntimeException("Failed to find requested network ({$useNetwork})");
}

$currentPinInput = new CurrentPinInput(new FgetsUserInputRequest());
$publicKeyService = new GetPublicKeyService();
$getPublicKey = $reqFactory->getPublicKey($btcNetwork->getCoinName(), [1]);
$publicKey = $publicKeyService->call($session, $currentPinInput, $getPublicKey);

echo "xpub: ".$publicKey->getXpub().PHP_EOL;
echo "depth: ".$publicKey->getNode()->getDepth().PHP_EOL;
echo "childnum: ".$publicKey->getNode()->getChildNum().PHP_EOL;
echo "fingerprint: ".$publicKey->getNode()->getFingerprint().PHP_EOL;
echo "chaincode: ".bin2hex($publicKey->getNode()->getChainCode()->getContents()).PHP_EOL;
echo "public: ".bin2hex($publicKey->getNode()->getPublicKey()->getContents()).PHP_EOL;
if ($publicKey->getNode()->hasPrivateKey()) {
    echo "private: ".bin2hex($publicKey->getNode()->getPrivateKey()->getContents()).PHP_EOL;
}


$session->release();
