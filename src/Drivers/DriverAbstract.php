<?php

namespace NotificationChannels\Gammu\Drivers;

use NotificationChannels\Gammu\Exceptions\CouldNotSendNotification;

abstract class DriverAbstract implements DriverInterface
{
    const PACKAGE = 'LNC-Gammu';

    const VERSION = '0.0.8';

    public $destination;

    public $content;

    public function send($phoneNumber, $content, $sender = null, $callback = null)
    {
    }

    public function getSignature()
    {
        return self::PACKAGE.'/'.self::VERSION;
    }

    public function setDestination($phoneNumber)
    {
        if (empty($phoneNumber)) {
            throw CouldNotSendNotification::destinationNotProvided();
        }

        return $this;
    }

    public function getDestination()
    {
    }

    public function setContent($content)
    {
        if (empty($content)) {
            throw CouldNotSendNotification::contentNotProvided();
        }

        return $this;
    }

    public function getContent()
    {
    }
}
