<?php

namespace NotificationChannels\Gammu\Models;

class Outbox extends ModelAbstract
{
    protected $table = 'outbox';

    protected $primaryKey = 'ID';

    protected $fillable = [
        'SenderNumber', 'UDH', 'SMSCNumber', 'TextDecoded', 'RecipientID',
        'Processed',
    ];

    protected $dates = [
        'SendingDateTime', 'SendingTimeOut',
    ];

    public $timestamps = true;

    const CREATED_AT = 'InsertIntoDB';

    const UPDATED_AT = 'UpdatedInDB';
}
