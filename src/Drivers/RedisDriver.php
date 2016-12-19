<?php

namespace NotificationChannels\Gammu\Drivers;

use Illuminate\Contracts\Config\Repository;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;
use NotificationChannels\Gammu\Exceptions\CouldNotSendNotification;

class RedisDriver extends DriverAbstract
{
    protected $callback;

    protected $config;

    protected $data = [];

    protected $chunks = [];

    public $destination;

    public $content;

    public $channel;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function send($phoneNumber, $content, $channel = null, $sender = null, $callback = null)
    {
        $this->setCallback($callback);
        $this->setDestination($phoneNumber);
        $this->setContent($content);
        $this->setChannel($channel);
        
        Redis::publish($this->channel, json_encode([
            'to' => $this->destination,
            'message' => $this->content,
            'callback' => $this->callback
        ]));
    }

    public function setChannel($channel)
    {
        $this->channel = !$channel ? 'gammu-channel' : $channel;
        return $this;
    }

    public function setCallback($callback)
    {
        $this->callback = $callback;

        return $this;
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
}
