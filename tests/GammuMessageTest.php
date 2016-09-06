<?php

namespace NotificationChannels\Gammu\Test;

use NotificationChannels\Gammu\GammuMessage;

class GammuMessageTest extends TestBase
{
    protected $message;

    public function setUp()
    {
        parent::setUp();

        $this->message = new GammuMessage();
    }

    public function test_can_accept_content()
    {
        $content = $this->faker->sentence;

        $message = new GammuMessage($content);

        $this->assertEquals($content, $message->content);
    }

    public function test_can_create_method()
    {
        $content = $this->faker->sentence;

        $message = GammuMessage::create($content);

        $this->assertEquals($content, $message->content);
    }

    public function test_set_destination()
    {
        $phoneNumber = $this->faker->e164PhoneNumber;

        $this->message->to($phoneNumber);
        $this->assertEquals($phoneNumber, $this->message->destination);
    }

    public function test_set_content()
    {
        $content = $this->faker->sentence;

        $this->message->content($content);
        $this->assertEquals($content, $this->message->content);
    }

    public function test_set_sender()
    {
        $sender = $this->faker->company;

        $this->message->sender($sender);
        $this->assertEquals($sender, $this->message->sender);
    }
}
