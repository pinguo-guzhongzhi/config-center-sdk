<?php
/**
 * Created by PhpStorm.
 * User: pinguokeji
 * Date: 2020/12/4
 * Time: 4:31 PM
 */

class ConfigFormatter
{

    /**
     * @var string
     */
    protected $jsonFileName;

    /**
     * @var array
     *
     */
    protected $data;

    /**
     * ConfigFormatter constructor.
     *
     * @param $jsonFileName
     *
     * @throws Exception
     */
    public function __construct($jsonFileName)
    {
        if (!file_exists($jsonFileName)) {
            throw new Exception("invalid json file: " . $jsonFileName);
        }
        $this->jsonFileName = $jsonFileName;
        $content            = file_get_contents($this->jsonFileName);
        if ($content == "") {
            $content = "{}";
        }
        $this->data = json_decode($content, true);
    }

    /**
     * @return array
     * @throws
     */
    public function toArray()
    {
        $data = [];
        foreach ($this->data as $key => $value) {
            $key  = trim($key);
            $temp = explode(".", $key);
            try {
                $this->setValue($data, $temp, $value);
            } catch (Exception $ex) {
                throw $ex;
            }
        }
        unset($data["security"], $data["micro"]);
        return $data;
    }

    protected function setValue(&$data, $keyPath, $value)
    {
        $key = $keyPath[0];
        unset($keyPath[0]);
        if (count($keyPath) == 0) {
            $data[$key] = $value;
            return $data;
        } else {
            if (!isset($data[$key])) {
                $data[$key] = [];
            }
            $this->setValue($data[$key], array_values($keyPath), $value);
        }
    }
}