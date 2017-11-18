<?php
namespace Mapos\Planting;

use Mapos\Device\DeviceSerial;
use Mapos\Log\LogTrait;
use PDO;

class Planting
{
    use LogTrait;
    private $dbh;
    private $temp;
    private $humidity;

    public function fromLine($line)
    {
        $lineExploded = explode(',', $line);
        if (count($lineExploded) == 2) {
            list($temp, $humidity) = $lineExploded;
            $this->setHumidity($humidity);
            $this->setTemp($temp);
            return $this->persist();
        } else {
            $this->warning("Can't store data, something wrong.." . var_export($line));
        }
    }

    public function __construct()
    {
        //SOLID broken, for now is ok
        $this->dbh = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USERNAME, DB_PASSWORD);
    }

    public function persist()
    {
        try {
            $stmt = $this->dbh->prepare("INSERT INTO results (temp, humidity, date) VALUES (:temp, :humidity, now())");
            $stmt->bindParam(':temp', $this->temp);
            $stmt->bindParam(':humidity', $this->humidity);

            $stmt->execute();
        } catch (PDOException $ex) {
            echo "An Error occured!"; //user friendly message
//some_logging_function($ex->getMessage());
        }
    }

    public function getPreset($id)
    {
        return $this->getData("SELECT * FROM presets WHERE id = " . $id . " LIMIT 1");
    }

    public function getPresets($select = '*')
    {
        return $this->getData("SELECT " . $select . " FROM presets order by id");
    }

    public function readStatus()
    {
        $result = @$this->getData("SELECT * FROM results ORDER BY id DESC LIMIT 1")[0];
        if (!$result) {
            $result['temp'] = '-';
            $result['humidity'] = '-';
        }
        return array($result['temp'], $result['humidity']);
    }

    public function displayStage($stage_id)
    {
        switch ($stage_id) {
            case 1:
                return 'Grow';
            case 2:
                return 'Flowering';
            case 3:
                return 'Sick';
        }
    }

    public function getStatus()
    {
        $result = $this->getData("SELECT * FROM status LIMIT 1")[0];
        return $result;
    }

    public function getData($query)
    {
        try {
            $r = $this->dbh->query($query);
            $result = $r->fetchAll();
        } catch (PDOException $ex) {
            echo "An Error occured!";
            return false;
        }

        return $result;
    }

    /**
     * @var
     */
    private $serialDevice;

    /**
     * Send time set command
     */
    public function setTime()
    {
        $this->info("Setting time..");
        return $this->serialDevice->send("T" . microtime(true) . "\n", 4);
    }

    /**
     *Preparation
     */
    public function boot()
    {
        $this->info("Booting..");
        $timeSet = true;
        while ($timeSet) {
            $read = $this->serialDevice->read();
            if (strpos($read, "Date Time request") !== false) {
                $this->setTime();
                $timeSet = false;
            }
            sleep(1);
        }


        $this->setData();
    }

    public function getActivePreset()
    {
        $preset = $this->getStatus()['preset'];
        return $this->getPreset($preset)[0];
    }

    /**
     * Send data
     */
    public function setData()
    {
        //We get the setups from database
        $preset = $this->getActivePreset();

        $dayTime = $preset['growing_day_start'];
        $nighTime = $preset['growing_night_start'];
        $temp = array($preset['day_temp_min'], $preset['day_temp_max'], $preset['night_temp_min'], $preset['night_temp_max']);
        $humidity = array($preset['day_humidity_min'], $preset['day_humidity_max'], $preset['night_humidity_min'], $preset['night_humidity_max']);
        $light = array($preset['growing_red'], $preset['growing_blue'], $preset['growing_uv'], $preset['growing_ir']);
        $stage = 1;

        $this->info("Setting data..");
        return $this->serialDevice->send("SET" . $stage . "," . $dayTime . "," . $nighTime . "," . join(",", $temp) . "," . join(",", $humidity) . "," . join(",", $light) . "\n", 4);
    }

    public function collectData()
    {
        //update device???
        $read = $this->serialDevice->read();

        if ($read !== false) {
            if (strpos($read, "Date Time request") === false) {
                if ($read != '') {
                    //We get data from line
                    //$this->info("Storing results to database");
                    $this->fromLine($read);
                }
            } else {
                $this->setTime();
                $this->setData();
            }
        } else {
            $this->info("Device disconnected?");
        }
    }

    public function updateDevice()
    {
        $read = $this->serialDevice->read();

        if ($read !== false) {

            if (strpos($read, "Date Time request") !== false) {
                $this->setTime();
                $timeSet = false;
                sleep(5);
            }

            if ($this->getStatus()['updateDevice']) {
                $this->setData();
            }
        } else {
            $this->info("Device disconnected?");
        }
    }

    public function getSerialDevice()
    {
        return $this->serialDevice;
    }


    public function setSerialDevice(DeviceSerial $serialDevice)
    {
        $this->serialDevice = $serialDevice;
    }

    /**
     * @param float $humidity
     */
    public function setHumidity($humidity)
    {
        $this->humidity = (float)$humidity;
    }

    /**
     * @param float $temp
     */
    public function setTemp($temp)
    {
        $this->temp = (float)$temp;
    }

}
