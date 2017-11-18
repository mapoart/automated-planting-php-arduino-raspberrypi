<?php
namespace Mapos\Device;

use Mapos\Log\LogTrait;

trait DeviceTrait
{
    use LogTrait;

    private $maxDeviceNumber = 9;

    private $devicesPath;

    private $device;

    /**
     * @param mixed $device
     */
    public function setDevice($device)
    {
        $this->device = $device;
    }

    /**
     * @return mixed
     */
    public function getDevicesPath()
    {
        return $this->devicesPath;
    }

    /**
     * @param mixed $devicesPath
     */
    public function setDevicesPath($devicesPath)
    {
        $this->devicesPath = $devicesPath;
    }

    private function checkPath()
    {
        if (!$this->devicesPath) {
            $errorMessage = "Set devices path eg. /dev/video OR /dev/ttyACM";
            $this->warning($errorMessage);
            throw new DeviceException($errorMessage);
        }
    }

    /**
     * @param int $id
     * @return bool|string
     * @throws DeviceException
     */
    public function getDevice($id = 0)
    {
        $this->checkPath();
        $devicePath = $this->getDevicePath($id);
        if (!file_exists($devicePath)) {
            return false;
        }
        return $devicePath;
    }

    private function getDevicePath($x)
    {
        return $this->devicesPath . $x;
    }

    public function findDevice()
    {
        $this->checkPath();
        if (!$this->device) {
            //We find first device
            for ($x = 0; $x <= $this->maxDeviceNumber; $x++) {
                $devicePath = $this->getDevicePath($x);
                if (file_exists($devicePath)) {
                    $this->device = $devicePath;
                    return true;
                }
            }

            return false;
        } else {
            if (!file_exists($this->device)) {
                $this->device = null;
                return $this->findDevice();
            }
        }

        return false;
    }


}
