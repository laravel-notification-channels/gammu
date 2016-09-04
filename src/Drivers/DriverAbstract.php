<?php

namespace NotificationChannels\Gammu\Drivers;

abstract class DriverAbstract implements DriverInterface
{
    const PACKAGE = 'LNC-Gammu';

    const VERSION = '0.0.2';

    public function send($phoneNumber, $content, $sender = null)
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
        if (empty($this->data['DestinationNumber'])) {
            throw CouldNotSendNotification::destinationNotProvided();
        }
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
