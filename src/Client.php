<?php
/**
 * Created by PhpStorm.
 * User: pinguokeji
 * Date: 2020/12/3
 * Time: 9:38 AM
 */

namespace PGConfig;


use pgc\helpers\SecurityHelper;
use GuzzleHttp\Client AS GuzzleHttpClient;

class Client
{
    const API_URL_QA   = "https://api-qa.camera360.com/";
    const API_URL_PROD = "https://micro-api.camera360.com/";

    const ENV_QA   = "qa";
    const ENV_PROD = "prod";
    const ENV_DEV  = "dev";

    /**
     * @param string $clientId
     * @param string $sec
     * @param string $env
     *
     * @return Client
     * @throws \Exception
     */
    public static function NewInstance($clientId, $sec, $env = self::ENV_QA)
    {
        $url = self::API_URL_QA;
        switch ($env) {
            case self::ENV_QA:
                $url = self::API_URL_QA;
                break;
            case self::ENV_PROD:
                $url = self::API_URL_PROD;
                break;
            case self::ENV_DEV:
                throw new \Exception("no url for dev env, please use NewInstanceApiByUrl to create the instance");
            default:
                throw new \Exception("invalid env");
        }
        $ins = new self($clientId, $sec, $env, $url);
        return $ins;
    }

    /**
     * @param string $clientId
     * @param string $sec
     * @param string $url
     *
     * @return Client
     */
    public static function NewInstanceApiByUrl($clientId, $sec, $url = self::API_URL_QA)
    {
        $ins = new self($clientId, $sec, self::ENV_DEV, $url);
        return $ins;
    }

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $env;

    /**
     * @var string
     */
    protected $sec;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * Client constructor.
     *
     * @param string $clientId
     * @param string $sec
     * @param string $env
     * @param string $url
     */
    protected function __construct($clientId, $sec, $env, $url)
    {
        $this->clientId  = $clientId;
        $this->sec       = $sec;
        $this->env       = $env;
        $this->url       = $url;
        $this->namespace = "com.camera360.srv";
    }

    public function loadConfig()
    {
        $keys = $this->loadRemoteConfig();
        $data = [];
        foreach ($keys as $key => $value) {
            $temp = explode(".", $key);
            $this->setValue($data, $temp, $value);
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

    protected function loadRemoteConfig()
    {
        $sign    = $this->genAuth();
        $headers = [
            "Sign"      => $sign,
            "Client-Id" => $this->clientId,
        ];

        $url    = $this->url . "config/config/list";
        $client = new GuzzleHttpClient([
            'timeout' => 10,
        ]);
        $rsp    = $client->get($url, [
            "headers" => $headers,
        ]);
        $body   = $rsp->getBody();
        if ($body == "") {
            throw new \Exception("request to config center failure: " . $url);
        }
        $data = json_decode($body, true);
        if (!isset($data["status"]) || $data["status"] != 200) {
            throw new \Exception("request to config center failure: " . $body);
        }
        $keys = json_decode($data["data"], true);
        if (!is_array($keys)) {
            throw new \Exception("parse config center data failure: " . json_last_error_msg());
        }
        return $keys;
    }

    /**
     * @return string
     */
    protected function genAuth()
    {
        $sign = [
            "clientId"  => $this->clientId,
            "namespace" => $this->namespace,
            "timestamp" => time(),
        ];
        $str  = \GuzzleHttp\json_encode($sign);
        $auth = SecurityHelper::pinguoEncrypt($str, $this->sec);
        return $auth;
    }
}