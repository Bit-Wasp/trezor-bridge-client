<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Bridge\Message;

abstract class DeviceListResponse
{
    /**
     * @var Device[]
     */
    private $devices = [];

    /**
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

    /**
     * @return Device[]
     */
    public function devices(): array
    {
        return $this->devices;
    }
}
