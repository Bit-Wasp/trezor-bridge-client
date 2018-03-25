<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Bridge\Message;

class ListDevicesResponse
{
    /**
     * @var Device[]
     */
    private $devices = [];

    /**
     * ListDevicesResponse constructor.
     * @param Device[] $devices
     */
    public function __construct(array $devices)
    {
        foreach ($devices as $device) {
            if (!($device instanceof Device)) {
                throw new \InvalidArgumentException();
            }
        }

        $this->devices = $devices;
    }

    public function devices(): array
    {
        return $this->devices;
    }
}
