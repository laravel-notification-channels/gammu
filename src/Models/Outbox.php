<?php

namespace NotificationChannels\Gammu\Models;

class Outbox extends ModelAbstract
{
    protected $table = 'outbox';

    protected $primaryKey = 'ID';

    protected $fillable = [
        'SenderID', 'CreatorID', 'DestinationNumber', 'TextDecoded', 'MultiPart',
        'UDH', 'Retries',
    ];

    protected $dates = [
        'SendingDateTime', 'SendingTimeOut',
    ];

    public $timestamps = true;

    const CREATED_AT = 'InsertIntoDB';

    const UPDATED_AT = 'UpdatedInDB';
}
