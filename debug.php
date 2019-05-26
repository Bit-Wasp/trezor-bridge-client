<?php
declare(strict_types=1);

require "vendor/autoload.php";

use BitWasp\Trezor\Bridge\Client;
use BitWasp\Trezor\Bridge\Http\HttpClient;
use BitWasp\Trezor\Device\Button\DebugButtonAck;
use BitWasp\Trezor\Device\Command\LoadDeviceService;
use BitWasp\Trezor\Device\RequestFactory;

$depth = 0;
$fingerprint = 0;
$numChild = 0;
$chainCode = \Protobuf\Stream::fromString(hex2bin('a86d0945bd342199a130b65255df75199fe09e539d60053003cc1c0e999982a5'));
$privateKey = \Protobuf\Stream::fromString(hex2bin('874c62f2c98f7c94f1a691492825a71e8e9b9251f03c208f37d1ec9c9cda2b24'));
$language = "EN";

$reqFactory = new RequestFactory();
$hdNode = $reqFactory->privateHdNode($depth, $fingerprint, $numChild, $chainCode, $privateKey);
$loadDevice = $reqFactory->loadDeviceWithHdNode($hdNode, $language);

$httpClient = HttpClient::forUri("http://localhost:21325");
$client = new Client($httpClient);
$devices = $client->listDevices()->devices();
if (empty($devices)) {
    throw new \RuntimeException("No devices returned");
}

$session = $client->acquire($devices[0]);
$dbgSession = $client->acquire($devices[1]);

$dbgBtnAck = new DebugButtonAck($dbgSession);
$loadService = new LoadDeviceService($dbgBtnAck);
$loaded = $loadService->call($session, $loadDevice);
var_dump($loaded);
