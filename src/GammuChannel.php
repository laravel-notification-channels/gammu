<?php

namespace NotificationChannels\Gammu;

use NotificationChannels\Gammu\Exceptions\CouldNotSendNotification;
use NotificationChannels\Gammu\Models\Outbox;
use NotificationChannels\Gammu\Models\OutboxMultipart;
use NotificationChannels\Gammu\Models\Phone;
use Illuminate\Notifications\Notification;
use GuzzleHttp\Client as HttpClient;

class GammuChannel
{
    /**
     * @var HttpClient
     */
    protected $http;

    /**
     * @var Outbox
     */
    protected $outbox;

    /**
     * @var OutboxMultipart
     */
    protected $multipart;

    /**
     * @var Phone
     */
    protected $phone;

    /**
     * Channel constructor.
     *
     * @param HttpClient $http
     * @param Outbox $outbox
     * @param OutboxMultipart $multipart
     * @param Phone $phone
     */
    public function __construct(HttpClient $http, Outbox $outbox, OutboxMultipart $multipart, Phone $phone)
    {
        $this->http = $http;
        $this->outbox = $outbox;
        $this->multipart = $multipart;
        $this->phone = $phone;
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
        $this->gammuExceptions($payload, $notifiable);
        switch (config('services.gammu.method')) {
            case 'api':
                $this->gammuApi($payload);
                break;
            case 'db':
                $this->gammuDb($payload);
                break;
        }
    }

    /**
     * Send the given notification via Gammu Api.
     *
     * @param mixed $payload
     */
    public function gammuApi($payload)
    {
        $param = $payload->toArray();
        $this->http->post(config('services.gammu.url') . '/send/sms', [
            'json' => [
                'key' => config('services.gammu.auth'),
                'to' => $param['DestinationNumber'],
                'message' => $param['TextDecoded']
            ]
        ]);
    }

    /**
     * Send the given notification via Gammu DB.
     *
     * @param mixed $payload
     */
    public function gammuDb($payload)
    {
        $params = $payload->toArray();

        $outbox = $this->outbox->create($params);

        $multiparts = $payload->getMultipartChunks();
        if (! empty($multiparts) && ! empty($outbox->ID)) {
            foreach ($multiparts as $chunk) {
                $chunk['ID'] = $outbox->ID;
                $this->multipart->create($chunk);
            }
        }
    }

    public function gammuExceptions($payload, $notifiable)
    {
        $method = config('services.gammu.method');

        if(! $method) {
            throw CouldNotSendNotification::methodNotProvided();
        }

        if($method != 'db' && $method != 'api') {
            throw CouldNotSendNotification::invalidMethodProvided();
        }

        if ($payload->senderNotGiven() && $method == 'db') {
            if (! $sender = config('services.gammu.sender')) {
                if (! $sender = $this->phone->first()->ID) {
                    throw CouldNotSendNotification::senderNotProvided();
                }
            }

            $payload->sender($sender);
        }

        if(! config('services.gammu.url') && $method == 'api') {
            throw CouldNotSendNotification::apiUrlNotProvided();
        }

        if(! config('services.gammu.auth') && $method == 'api') {
            throw CouldNotSendNotification::authKeyNotProvided();
        }

        if ($payload->destinationNotGiven()) {
            if (! $destination = $notifiable->routeNotificationFor('gammu')) {
                throw CouldNotSendNotification::destinationNotProvided();
            }
            $payload->to($destination);
        }
    }
}
