<?php

namespace NotificationChannels\Gammu\Drivers;

use Illuminate\Contracts\Config\Repository;
use GuzzleHttp\Client;
use NotificationChannels\Gammu\Exceptions\CouldNotSendNotification;

class ApiDriver extends DriverAbstract
{
    protected $config;

    protected $client;

    protected $url;

    protected $key;

    protected $ua;

    protected $data = [];

    protected $chunks = [];

    public $destination;

    public $content;

    public function __construct(Repository $config, Client $client)
    {
        $this->config = $config;
        $this->client = $client;
    }

    public function send($phoneNumber, $content, $sender = null)
    {
        $this->setUrl();
        $this->setKey();
        $this->setUserAgent();
        $this->setDestination($phoneNumber);
        $this->setContent($content);

        $this->client->post($this->url, [
            'json' => [
                'key' => $this->key,
                'to' => $this->destination,
                'message' => $this->content,
            ],
            'headers' => [
                'user-agent' => $this->ua,
            ],
        ]);
    }

    public function setDestination($phoneNumber)
    {
        if (empty($phoneNumber)) {
            throw CouldNotSendNotification::destinationNotProvided();
        }

        $this->destination = trim($phoneNumber);

        return $this;
    }

    public function getDestination()
    {
        if (empty($this->destination)) {
            throw CouldNotSendNotification::destinationNotProvided();
        }

        return $this->destination;
    }

    public function setContent($content)
    {
        if (empty($content)) {
            throw CouldNotSendNotification::contentNotProvided();
        }

        $this->content = $content;

        return $this;
    }

    public function getContent()
    {
        if (empty($this->content)) {
            throw CouldNotSendNotification::contentNotProvided();
        }

        return $this->content;
    }

    public function setUrl()
    {
        $this->url = $this->config->get('services.gammu.url');

        if (empty($this->url)) {
            throw CouldNotSendNotification::apiUrlNotProvided();
        }

        return $this;
    }

    public function getUrl()
    {
        if (empty($this->url)) {
            throw CouldNotSendNotification::apiUrlNotProvided();
        }

        return $this->url;
    }

    public function setKey()
    {
        $this->key = $this->config->get('services.gammu.auth');

        if (empty($this->key)) {
            throw CouldNotSendNotification::authKeyNotProvided();
        }

        return $this;
    }

    public function getKey()
    {
        if (empty($this->key)) {
            throw CouldNotSendNotification::authKeyNotProvided();
        }

        return $this->key;
    }

    public function setUserAgent()
    {
        $userAgents = [
            $this->getSignature(),
            'GuzzleHttp/'.Client::VERSION,
            'PHP/'.PHP_VERSION,
        ];

        if (extension_loaded('curl') && function_exists('curl_version')) {
            array_push($userAgents, 'curl/'.\curl_version()['version']);
        }

        $this->ua = collect($userAgents)->implode(' ');

        return $this;
    }
}
