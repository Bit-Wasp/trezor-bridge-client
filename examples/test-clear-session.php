<?php

declare(strict_types=1);

use BitWasp\Trezor\Device\Command\ClearSessionService;
use BitWasp\Trezor\Device\Command\InitializeService;
use BitWasp\Trezor\Device\RequestFactory;

require __DIR__ . "/../vendor/autoload.php";

$useNetwork = "BTC";
$trezor = \BitWasp\Trezor\Bridge\Client::fromUri("http://localhost:21325");

$devices = $trezor->listDevices();
if (empty($devices)) {
    throw new \Exception("Error! No devices connected!");
}

$firstDevice = $devices->devices()[0];

echo "acquire device!\n";
$session = $trezor->acquire($firstDevice);
$reqFactory = new RequestFactory();

$initializeCmd = new InitializeService();
$features = $initializeCmd->call($session, $reqFactory->initialize());

$clearSessionService = new ClearSessionService();

// the false flags here determine what the user should be challenged with
$clearSession = $reqFactory->clearSession();

$success = $clearSessionService->call($session, $clearSession);
var_dump($success);
$session->release();
