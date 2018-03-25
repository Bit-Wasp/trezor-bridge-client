<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Bridge\Message;

/**
 * Properties via magic
 * @property int $path
 * @property string|null $session
 * @property int|null $vendor
 * @property int|null $product
 */
class Device
{
    /**
     * Device object to by wrapped
     *
     * @var \stdClass
     */
    private $msg;

    public function __construct(\stdClass $device)
    {
        $this->msg = $device;
    }

    public function __get($name)
    {
        return $this->msg->{$name};
    }

    public function getPath(): string
    {
        return $this->msg->path;
    }

    /**
     * @return string|null
     */
    public function getSession()
    {
        return $this->msg->session;
    }

    /**
     * @return int|null
     */
    public function getVendor()
    {
        return $this->msg->vendor;
    }

    /**
     * @return int|null
     */
    public function getProduct()
    {
        return $this->msg->product;
    }

    public function getObject(): \stdClass
    {
        return $this->msg;
    }
}
