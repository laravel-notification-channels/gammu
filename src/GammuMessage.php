<?php

namespace NotificationChannels\Gammu;

class GammuMessage
{
    public $destination;

    public $content;

    public $sender;

    public $callback;

    public $channel;

    /**
     * @param string $content
     *
     * @return static
     */
    public static function create($content = '')
    {
        return new static($content);
    }

    /**
     * Create a new message instance.
     *
     * @param string $content
     */
    public function __construct($content = '')
    {
        $this->content($content);
    }

    /**
     * Destination phone number.
     *
     * @param $phoneNumber
     *
     * @return $this
     */
    public function to($phoneNumber)
    {
        $this->destination = $phoneNumber;

        return $this;
    }

    /**
     * SMS message.
     *
     * @param $content
     *
     * @return $this
     */
    public function content($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Gammu Api Callback.
     *
     * @param $content
     *
     * @return $this
     */
    public function callback($content)
    {
        $this->callback = $content;

        return $this;
    }

    /**
     * Gammu Api Redis Channel.
     *
     * @param $channel
     *
     * @return $this
     */
    public function channel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Sender Phone ID.
     *
     * @param $phoneId
     *
     * @return $this
     */
    public function sender($phoneId = null)
    {
        $this->sender = $phoneId;

        return $this;
    }

    /**
     * Determine if Sender Phone ID is not given.
     *
     * @return bool
     */
    public function senderNotGiven()
    {
        return empty($this->sender);
    }

    /**
     * Determine if Destination Phone Number is not given.
     *
     * @return bool
     */
    public function destinationNotGiven()
    {
        return empty($this->destination);
    }

    /**
     * Determine if Content is not given.
     *
     * @return bool
     */
    public function contentNotGiven()
    {
        return empty($this->content);
    }

    /**
     * Returns params payload.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'destination' => $this->destination,
            'content' => $this->content,
            'sender' => $this->sender,
            'callback' => $this->callback,
        ];
    }
}
