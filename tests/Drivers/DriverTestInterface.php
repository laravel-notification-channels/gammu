<?php

namespace NotificationChannels\Gammu\Test\Drivers;

interface DriverTestInterface
{
    public function test_set_destination();

    public function test_empty_destination();

    public function test_set_empty_destination();

    public function test_set_content();

    public function test_empty_content();

    public function test_set_empty_content();

    public function test_send();
}
