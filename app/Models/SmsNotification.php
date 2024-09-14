<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsNotification extends Model
{
    use HasFactory;

    protected $table = "sms_notification_records";
    protected $guarded = ['id'];

    protected $fillable = [
        'recipient',
        'sender_id',
        'type',
        'message',
        'status',
        'response',
    ];

    protected $casts = [
        'response'  => 'string',
    ];
}
