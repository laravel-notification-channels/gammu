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
        $param = $payload->toArray();
        $this->gammuExceptions($payload, $notifiable);
        switch (config('services.gammu.method')) {
            case 'api':
                $this->gammuApi($param);
                break;
            case 'db':
                $this->gammuDb($param, $payload);
                break;
        }
    }

    /**
     * Send the given notification via Gammu Api.
     *
     * @param array $param
     */
    public function gammuApi(array $param)
    {
        $this->http->post(config('services.gammu.url'), [
            'json' => [
                'key' => config('services.gammu.auth'),
                'to' => $param['DestinationNumber'],
                'message' => $param['TextDecoded'],
            ],
        ]);
    }

    /**
     * Send the given notification via Gammu DB.
     *
     * @param array $param
     * @param GammuMessage $payload
     */
    public function gammuDb(array $param, GammuMessage $payload)
    {
        $outbox = $this->outbox->create($param);

        $multiparts = $payload->getMultipartChunks();
        if (! empty($multiparts) && ! empty($outbox->ID)) {
            foreach ($multiparts as $chunk) {
                $chunk['ID'] = $outbox->ID;
                $this->multipart->create($chunk);
            }
        }
    }

    public function gammuExceptions(GammuMessage $payload, $notifiable)
    {
        $method = config('services.gammu.method');

        if (! $method) {
            throw CouldNotSendNotification::methodNotProvided();
        }

        if ($method != 'db' && $method != 'api') {
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

        if (! config('services.gammu.url') && $method == 'api') {
            throw CouldNotSendNotification::apiUrlNotProvided();
        }

        if (! config('services.gammu.auth') && $method == 'api') {
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
