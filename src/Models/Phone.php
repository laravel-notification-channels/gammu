<?php

namespace NotificationChannels\Gammu\Models;

class Phone extends ModelAbstract
{
    protected $table = 'phones';

    protected $fillable = ['ID', 'IMEI', 'Client', 'Send', 'Receive'];

    protected $primaryKey = 'IMEI';

    protected $dates = [
        'TimeOut',
    ];

    public $incrementing = false;

    public $timestamps = true;

    const CREATED_AT = 'InsertIntoDB';

    const UPDATED_AT = 'UpdatedInDB';
}
