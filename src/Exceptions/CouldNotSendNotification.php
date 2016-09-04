<?php

namespace NotificationChannels\Gammu\Exceptions;

class CouldNotSendNotification extends \Exception
{
    /**
     * Thrown when there is no phone Sender ID provided.
     *
     * @return static
     */
    public static function senderNotProvided()
    {
        return new static('Sender ID was not provided.');
    }

    /**
     * Thrown when there is no destination phone number provided.
     *
     * @return static
     */
    public static function destinationNotProvided()
    {
        return new static('Destination phone number was not provided.');
    }

    /**
     * Thrown when there is content provided.
     *
     * @return static
     */
    public static function contentNotProvided()
    {
        return new static('Content was not provided.');
    }

    /**
     * Thrown when there is no Gammu Api auth key provided.
     *
     * @return static
     */
    public static function authKeyNotProvided()
    {
        return new static('Auth key for Gammu Api was not provided.');
    }

    /**
     * Thrown when no Gammu Api URL is provided.
     *
     * @return static
     */
    public static function apiUrlNotProvided()
    {
        return new static('Gammu Api URL was not provided.');
    }

    /**
     * Thrown when method is not provided.
     *
     * @return static
     */
    public static function methodNotProvided()
    {
        return new static('Method for sending Gammu messages was not provided. Available methods ("api", "db").');
    }

    /**
     * Thrown when invalid method is provided.
     *
     * @return static
     */
    public static function invalidMethodProvided()
    {
        return new static('Invalid method for sending Gammu messages was provided. Available methods ("api", "db").');
    }
}
