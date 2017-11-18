<?php
use Mapos\Log\LogTrait;

/**
 * Class Device
 */
class Device
{
    use LogTrait;





    /**
     * @var PhpSerial
     */
    private $device;

    /**
     * @var int seconds between all device checkings
     */
    private $waitFindDevice = 5;





    /**
     * @param int $rate optional rate
     * @return PhpSerial found device
     */
    private function findDevice()
    {
        if ($this->device) {
            return true;
        }

        $serial = new PhpSerial();
        //We are looking for a device on to ports..
        for ($x = 0; $x <= $this->maxDeviceNumber; $x++) {
            //Set the device
            $serial->deviceClose();
            $devicePath = $this->devicePath . $x;
            $serial->deviceSet($devicePath);
            $serial->confBaudRate($this->baudRate);
            //We check if device is available
            if (@$serial->deviceOpen()) {
                $this->log->info('Device found: ' . $devicePath);
                $this->device = $serial;
                return true;
            } else {
                $this->log->warning('Device not found: ' . $devicePath);
            }
        }
        $this->log->warning('Device not found. Waiting for ' . $this->waitFindDevice . ' sec and try again..');
        sleep(5);

        $this->findDevice();
    }


    /**
     * @return integer
     */
    public function getBaudRate()
    {
        return $this->baudRate;
    }

    /**
     * @param integer $baudRate
     */
    public function setBaudRate($baudRate)
    {
        $this->baudRate = (int)$baudRate;
    }
}
