<?php

namespace NotificationChannels\Gammu\Test;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Gammu\GammuChannel;
use NotificationChannels\Gammu\GammuMessage;
use NotificationChannels\Gammu\Drivers\DbDriver;
use NotificationChannels\Gammu\Drivers\ApiDriver;
use NotificationChannels\Gammu\Models\Phone;

class GammuChannelTest extends TestBase
{
    protected $notification;

    protected $dbDriver;

    protected $apiDriver;

    public function setUp()
    {
        parent::setUp();

        $this->phone = $this->app->make(Phone::class);
        $this->dbDriver = $this->mock(DbDriver::class);
        $this->apiDriver = $this->mock(ApiDriver::class);

        $this->channel = new GammuChannel(
            $this->app->config, $this->dbDriver, $this->apiDriver
        );

        $this->notification = new TestNotification();
        $this->notifiable = new TestNotifiable();
    }

    public function test_can_send_a_notification_using_native()
    {
        $this->app->config->set('services.gammu.method', 'db');
        $this->phone->create([
            'ID' => 'telkomsel',
            'IMEI' => $this->faker->numerify('#############'),
            'Client' => $this->faker->userAgent,
            'Send' => 'yes',
        ]);

        $message = $this->notification->toGammu($this->notifiable);

        $this->dbDriver->shouldReceive('send')->once();
        $this->channel->send($this->notifiable, $this->notification);
    }

    public function test_can_send_a_notification_using_api()
    {
        $this->app->config->set('services.gammu.method', 'api');

        $message = $this->notification->toGammu($this->notifiable);

        $this->apiDriver->shouldReceive('send')->once();
        $this->channel->send($this->notifiable, $this->notification);
    }
}

class TestNotifiable
{
    use Notifiable;
}

class TestNotification extends Notification
{
    public function toGammu($notifiable)
    {
        return new GammuMessage();
    }
}
