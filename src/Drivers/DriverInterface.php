<?php

namespace NotificationChannels\Gammu\Drivers;

interface DriverInterface
{
    public function send($phoneNumber, $content, $sender = null, $callback = null);

    public function setDestination($phoneNumber);

    public function getDestination();

    public function setContent($content);

    public function getContent();
}
