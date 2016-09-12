<?php

namespace NotificationChannels\Gammu\Models;

class OutboxMultipart extends ModelAbstract
{
    protected $table = 'outbox_multipart';

    protected $primaryKey = 'ID';

    protected $fillable = [
        'ID', 'SequencePosition', 'TextDecoded', 'UDH',
    ];

    public $incrementing = false;

    public $timestamps = false;
}
