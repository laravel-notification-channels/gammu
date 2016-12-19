<?php

namespace NotificationChannels\Gammu;

use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Config\Repository;
use NotificationChannels\Gammu\Drivers\DbDriver;
use NotificationChannels\Gammu\Drivers\ApiDriver;
use NotificationChannels\Gammu\Drivers\RedisDriver;
use NotificationChannels\Gammu\Exceptions\CouldNotSendNotification;

class GammuChannel
{
    protected $config;

    protected $dbDriver;

    protected $apiDriver;

    protected $redisDriver;

    private $method;

    public function __construct(
        Repository $config, DbDriver $dbDriver, ApiDriver $apiDriver, RedisDriver $redisDriver
    ) {
        $this->config = $config;
        $this->dbDriver = $dbDriver;
        $this->apiDriver = $apiDriver;
        $this->redisDriver = $redisDriver;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param $notification
     */
    public function send($notifiable, Notification $notification)
    {
        $payload = $notification->toGammu($notifiable);

        $destination = $payload->destination;
        $content = $payload->content;
        $sender = $payload->sender;
        $callback = $payload->callback;
        $channel = $payload->channel;

        $this->getMethod();

        switch ($this->method) {
            case 'db':
                $this->dbDriver->send($destination, $content, $sender);
                break;
            case 'api':
                $this->apiDriver->send($destination, $content, $sender, $callback);
                break;
            case 'redis':
                $this->redisDriver->send($destination, $content, $channel, $sender, $callback);
                break;
            default:
                throw CouldNotSendNotification::invalidMethodProvided();
        }
    }

    private function getMethod()
    {
        $this->method = $this->config->get('services.gammu.method');

        if (empty($this->method)) {
            throw CouldNotSendNotification::methodNotProvided();
        }

        return $this;
    }
}
