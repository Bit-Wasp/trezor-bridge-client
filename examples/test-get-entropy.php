<?php

declare(strict_types=1);

use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Http\HttpClient;
use BitWasp\Trezor\Device\Command\GetEntropyService;
use BitWasp\Trezor\Device\Command\InitializeService;
use BitWasp\Trezor\Device\RequestFactory;

require __DIR__ . "/../vendor/autoload.php";

$useNetwork = "BTC";

$httpClient = HttpClient::forUri("http://localhost:21325");
$trezor = new Client($httpClient);

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

echo "get entropy\n";
$entropyService = new GetEntropyService();
$getEntropy = $reqFactory->getEntropy(32);
$entropy = $entropyService->call($session, $getEntropy);

var_dump($entropy);

$session->release();
